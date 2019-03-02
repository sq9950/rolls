<?php
namespace Controller\Admin\Auth;

use Service\Auth\NodeGroupService;

/**
 * @node_name 权限节点分组
 * Desc: 权限节点分组
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016-7-18 14:35:59
 */
class NodeGroup extends \Controller\Admin\Common\Common {

    const SEPARATOR = '/';
    protected $defaultService;      //默认服务类

    public function __construct() {
        parent::__construct();
        $this->setHeaderFooter();
        $this->defaultService = new NodeGroupService();
    }

    /**
     * @node_name 入口方法
     */
    public function index() {
        $actions = $this->buildUrl([
                                       'getDataList',
                                       'addData',
                                       'batchAddData',
                                       'editData',
                                       'deleteData',
                                       'batchDeleteData',
                                       'changeStatus',
                                       'changeFieldValue',
                                   ]);

        $this->view->assign('actions', $actions);
        $params                = $this->req;
        $field_maps            = $this->defaultService->getFieldMaps();
        $params['fields_list'] = $field_maps['fields_list'];
        $show_add=$this->defaultService->getShowAddEdit("add");
        $this->view->assign('params', $params);
        $this->view->assign("show_add",$show_add);
        $class_name_arr = explode('\\', __CLASS__);
        array_shift($class_name_arr);
        $this->view->display(implode(self::SEPARATOR, $class_name_arr) . self::SEPARATOR . __FUNCTION__ . ".html");
    }

    /**
     * @node_name 查询数据列表
     */
    protected function getDataList() {
        $params = $this->parseJplistStatuses($this->req['statuses']);
        $result = $this->defaultService->getDataList($params);
        if($result['count']>0){
            $this->formatDataList($result['data']);
        }
        
        $this->ajaxReturn($result);
    }

    private function formatDataList(&$data = []){

        foreach($data['list_fields'] as $field_setting){
            if('select' == $field_setting['form_config']['type']
                && isset($data['list_fields'][$field_setting['form_config']['name']])
            ){
                $actions = $this->buildUrl([
                                               'changeFieldValue',
                                           ]);

                $data['list_fields'][$field_setting['form_config']['name']]['form_config']['form_action'] = $actions['changeFieldValue'];
            }
        }

    }

    /**
     * @node_name 新增
     */
    public function addData() {
        if (IS_GET) {
            $params                = [];
            $actions               = $this->buildUrl([

                                                     ]);
             //上传图片方法
            $actions['uploadProfileImg'] = \Url::get_function_url('publicer', 'uploader', 'uploadProfileImg', array(), true);

            $field_maps            = $this->defaultService->getFieldMaps();
            $params['fields_maps'] = $field_maps;
            $this->view->assign('params', $params);
            $this->view->assign('fields_add', json_encode($field_maps['fields_add']));
            $this->view->assign('actions', $actions);
            $class_name_arr = explode('\\', __CLASS__);
            array_shift($class_name_arr);
            $this->view->display(implode(self::SEPARATOR, $class_name_arr) . self::SEPARATOR . __FUNCTION__ . ".html");
        } else {
            $res = $this->defaultService->addData($this->post);
            $this->ajaxReturn($res);
        }
    }

    /**
     * @node_name 批量新增
     */
    public function batchAddData() {

    }

    /**
     * @node_name 批量删除
     */
    public function batchDeleteData() {
        $batch_ids=$this->req['batch_ids'];
        $batch_type=$this->req['batch_type'];
        if(empty($batch_ids)){
            $this->ajaxReturn(['status'=>0,"info"=>"记录不存在"]);
        }

        if($batch_type=="delete"){
            $where=['id'=>['in',$batch_ids]];
            $res=$this->defaultService->deleteDataByWhere($where);
        }
      
        $this->ajaxReturn($res);
    }

    /**
     * @node_name 编辑
     */
    public function editData() {
        if (IS_GET) {
            $where = [
                'id' => $this->get['id']
            ];
            $this->view->assign('params', $this->req);
            $data       = $this->defaultService->getDataInfo($where);
            $field_maps = $this->defaultService->getFieldMaps();
            foreach ($field_maps['fields_edit'] as $key => $val) {
                if (isset($val['form_config']['name']) &&
                    !empty($val['form_config']['name']) && isset($data[$val['form_config']['name']])
                ) {
                    if(is_serialized($data[$val['form_config']['name']])){
                        $field_maps['fields_edit'][$key]['form_config']['value'] = str_replace('"', '\"', $data[$val['form_config']['name']]);
                    }else{
                        $field_maps['fields_edit'][$key]['form_config']['value'] = $data[$val['form_config']['name']];
                    }
                }
            }
            $this->view->assign('fields_edit', json_encode($field_maps['fields_edit']));

            $actions = $this->buildUrl([

                                       ]);
             //上传图片方法
            $actions['uploadProfileImg'] = \Url::get_function_url('publicer', 'uploader', 'uploadProfileImg', array(), true);

            $this->view->assign('actions', $actions);
            $class_name_arr = explode('\\', __CLASS__);
            array_shift($class_name_arr);
            $this->view->display(implode(self::SEPARATOR, $class_name_arr) . self::SEPARATOR . __FUNCTION__ . ".html");

        } else {
            $res = $this->defaultService->editData($this->post);

            $this->ajaxReturn($res);
        }
    }

    /**
     * @node_name 删除
     */
    public function deleteData() {
        $id           = $this->req['id'];
        $list         = $this->defaultService->getDataList(['where' => ['id' => $id]]);
        $profile_info = [];
        if (isset($list['data']) && !empty($list['data'])) {
            $profile_info = array_pop($list['data']);
        }
        if (empty($profile_info)) {
            $this->ajaxReturn(['status' => 0, 'info' => '记录不存在']);
        }
        $res = $this->defaultService->deleteDataById($id);

        $this->ajaxReturn($res);
    }

    /**
     * @node_name 修改审核状态
     */
    public function changeStatus() {
        if (!isset($this->post['id'])) {
            $this->ajaxReturn(['status' => 0, 'info' => '请指定要修改的记录']);
        }

        $list         = $this->defaultService->getDataList(['where' => ['id' => $this->post['id']]]);
        $profile_info = [];
        if (isset($list['data']) && !empty($list['data'])) {
            $profile_info = array_pop($list['data']);
        }
        if (empty($profile_info)) {
            $this->ajaxReturn(['status' => 0, 'info' => '记录不存在']);
        }

        if (!isset($this->post['status'])) {
            $this->ajaxReturn(['status' => 0, 'info' => '未设置审核状态']);
        }
        $update_data = [
            'id'         => $this->post['id'],
            'status' => intval($this->post['status'])
        ];
        $res         = $this->defaultService->changeStatus($update_data);

        $this->ajaxReturn($res);
    }


    /**
     * @node_name 修改字段值
     */
    public function changeFieldValue() {
        if (!isset($this->post['id'])) {
            $this->ajaxReturn(['status' => 0, 'info' => '请指定要修改的记录']);
        }

        $list         = $this->defaultService->getDataList(['where' => ['id' => $this->post['id']]]);
        $profile_info = [];
        if (isset($list['data']) && !empty($list['data'])) {
            $profile_info = array_pop($list['data']);
        }
        if (empty($profile_info)) {
            $this->ajaxReturn(['status' => 0, 'info' => '记录不存在']);
        }

        $res         = $this->defaultService->changeFieldValue($this->post);

        $this->ajaxReturn($res);
    }
}
