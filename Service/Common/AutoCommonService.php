<?php
/**
 * Desc: 自动化开发公共服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016-7-18 14:37:14
 */

namespace Service\Common;

abstract class AutoCommonService extends \Service\Service {

    protected $defaultModel = null;    //默认业务数据表模型
    protected $listFieldMap = [];       //列表字段和显示映射
    protected $addFieldMap  = [];       //新增页字段和显示映射
    protected $editFieldMap = [];       //编辑也字段和显示映射

    public function __construct() {
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
        if (empty($this->defaultModel)) {
            exit('请设置默认业务数据表模型变量：$this->defaultModel');
        }
    }

    abstract public function initAddFieldMap();

    abstract public function initEditFieldMap();

    abstract public function initListFieldMap();

    public function initModels() {

    }

    public function getDataList($params) {
        $result = array(
            'count' => 0,
            'data'  => [
                'list' => []
            ],
            'info'  => '查询失败'
        );

        extract($params);

        if (empty($this->listFieldMap)) {
            $result['info'] = '请设置列表字段映射';
            return $result;
        }else{
            // 查询显示字段
            $fields_data=array();
            foreach($this->listFieldMap as $vo){
                if($vo['is_show'] && $vo['field_name']!="操作"){
                    $fields_data[]=$vo['field_name'];
                }
            }

            if(empty($fields_data)){
                $field="";
            }else{
                $field=implode(",",$fields_data);
            }
        }

        empty($where) && $where = array();
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 10;
        empty($order) && $order = array();
        empty($field) && $field = '';
        empty($group) && $group = '';

        if(!empty($where['`updated_at`']) && !is_array($where['`updated_at`'])){
            $tmp = $where['`updated_at`'];
            $where['`updated_at`']    = [];
            $where['`updated_at`'][1] = $tmp;
        }
        //时间
        if (!empty($where['`created_at`'][1]) || !empty($where['`updated_at`'][1])) {
            $start = !empty($where['`created_at`'][1]) ? $where['`created_at`'][1] : '1990-11-11';
            $end   = !empty($where['`updated_at`'][1]) ? $where['`updated_at`'][1] : '2999-11-11';

            $where['`created_at`'] = array('between', array($start, $end));
            unset($where['`updated_at`']);

        }
        $count = $this->defaultModel->getListCountByWhere($where);
        if ($count) {
            $list   = $this->defaultModel->getListByWhere($where, $offset, $limit, $order, $field, $group);
            foreach($list as $key => $val){
                foreach($val as $k => $v){
                    ('0' != $v) && empty($v) && ($list[$key][$k] = '');
                }
            }
            $show_fields = array_diff(array_column($this->listFieldMap, 'field_name'), ['']);
            foreach($list as $key => $val){
                foreach($val as $k => $v){
                    if(!in_array($k, $show_fields)){
                        unset($list[$key][$k]);
                    }
                }
            }
            $result = [
                'count' => $count,
                'data'  => [
                    'list'        => $list,
                    'list_fields' => array_combine(array_column($this->listFieldMap, 'field_name'), $this->listFieldMap),
                    'show_fields' => $show_fields,
                    'edit_show'   => $this->getShowAddEdit("edit")
                ],
                'info'  => '查询成功'
            ];
        }

        return $result;
    }

    public function getModuleList($except = []) {
        $module_dir = __ROOT__ . '/Controller/Admin';
        $module_list = scandir($module_dir);
        is_array($except) && !empty($except) && ($module_list = array_values(array_diff($module_list, $except)));
        return $module_list;
    }

    public function getDataInfo($where = []) {
        $data = [];
        if (is_array($where) && !empty($where)) {
            $data = $this->defaultModel->getOneByWhere($where);
        }
        return $data;
    }

    /**
     * @node_name 查询列表字段显示表
     * @return array
     */
    public function getFieldMaps() {
        $field_maps = [
            'fields_list' => $this->listFieldMap,
            'fields_add'  => $this->addFieldMap,
            'fields_edit' => $this->editFieldMap
        ];
        return $field_maps;
    }

    /**
     * @node_name 新增数据
     * @param array $params
     * @return array
     */
    public function addData($params = []) {
        $ret = ['status' => 0, 'info' => '新增失败'];
        if (is_array($params) && !empty($params)) {
            $data = $this->filterAddData($params);
            $res  = $this->defaultModel->add($data);
            if ($res) {
                $ret = ['status' => 1, 'info' => '新增成功'];
            }
        }
        return $ret;
    }

    /**
     * @node_name 编辑数据
     * @param array $params
     * @return array
     */
    public function editData($params = []) {
        $res = ['status' => 0, 'info' => '编辑失败'];
        if (is_array($params) && !empty($params)) {
            $where = [
                'id' => $params['id']
            ];
            unset($params['id']);
            $data = $params;
            $res  = $this->defaultModel->updateByWhere($where, $data);
            if (false !== $res) {
                $res = ['status' => 1, 'info' => '编辑成功', 'data' => $data];
            } else {
                $res = ['status' => 0, 'info' => '编辑失败', 'data' => $data];
            }
        }
        return $res;
    }

    /**
     * @node_name 修改状态
     * @param array $params
     * @return array
     */
    public function changeStatus($params = []) {
        $res = ['status' => 0, 'info' => '修改状态失败'];
        if (is_array($params) && !empty($params)) {
            if (!isset($params['id']) || empty(trim($params['id']))) {
                return ['status' => 0, 'info' => '请指定要审核的记录'];
            }
            if (!isset($params['status'])) {
                return ['status' => 0, 'info' => '未设置状态'];
            }

            $where = [
                'id' => $params['id']
            ];
            $data  = ['status' => $params['status']];
            $res   = $this->defaultModel->updateByWhere($where, $data);
            if ($res) {
                $res = ['status' => 1, 'info' => '修改状态成功', 'data' => $params];
            } else {
                $res = ['status' => 0, 'info' => '修改状态失败', 'data' => $params];
            }
        }
        return $res;
    }

    /**
     * @node_name 修改状态
     * @param array $params
     * @return array
     */
    public function changeType($params = []) {
        $res = ['status' => 0, 'info' => '修改类型失败'];
        if (is_array($params) && !empty($params)) {
            if (!isset($params['id']) || empty(trim($params['id']))) {
                return ['status' => 0, 'info' => '请指定要审核的记录'];
            }
            if (!isset($params['type'])) {
                return ['status' => 0, 'info' => '未设置类型'];
            }

            $where = [
                'id' => $params['id']
            ];
            $data  = ['type' => $params['type']];
            $res   = $this->defaultModel->updateByWhere($where, $data);
            if ($res) {
                $res = ['status' => 1, 'info' => '修改类型成功', 'data' => $params];
            } else {
                $res = ['status' => 0, 'info' => '修改类型失败', 'data' => $params];
            }
        }
        return $res;
    }

    /**
     * @node_name 修改状态
     * @param array $params
     * @return array
     */
    public function changeFieldValue($params = []) {
        $res = ['status' => 0, 'info' => '修改失败'];
        if (is_array($params) && !empty($params)) {
            if (!isset($params['id']) || empty(trim($params['id']))) {
                return ['status' => 0, 'info' => '请指定要修改的记录'];
            }
            if (!isset($params['field_name'])) {
                return ['status' => 0, 'info' => '未设置修改类型'];
            }
            if (!isset($params['field_value'])) {
                return ['status' => 0, 'info' => '未设置修改值'];
            }

            $where = [
                'id' => $params['id']
            ];
            $data  = [ $params['field_name'] => $params['field_value']];
            $res   = $this->defaultModel->updateByWhere($where, $data);
            if ($res) {
                $res = ['status' => 1, 'info' => '修改成功', 'data' => $params];
            } else {
                $res = ['status' => 0, 'info' => '修改失败', 'data' => $params];
            }
        }
        return $res;
    }

    /**
     * @node_name 删除
     * @param $id
     * @return mixed
     */
    public function deleteDataById($id) {
        if ($id) {
            $res = $this->defaultModel->deleteByWhere(['id' => $id]);
            if ($res) {
                return ['status' => 1, 'info' => '删除成功'];
            }
            return ['status' => 0, 'info' => '删除失败'];
        } else {
            return ['status' => 0, 'info' => '请指定ID'];
        }
    }

    /**
     * @node_name 删除(慎用！！)
     * @param array $where
     * @return array
     */
    public function deleteDataByWhere($where = []) {
        if (is_array($where) && !empty($where)) {
            $res = $this->defaultModel->deleteByWhere($where);
            if ($res) {
                return ['status' => 1, 'info' => '删除成功'];
            }
            return ['status' => 0, 'info' => '删除失败'];
        } else {
            return ['status' => 0, 'info' => '请指定ID'];
        }
    }

    /**
     * @node_name 过滤新增数据
     * @param array $params
     * @return array
     */
    protected function filterAddData($params = []) {
        $data = [];
        if (is_array($params) && !empty($params)) {
            $data = $params;
        }
        return $data;
    }

    /**
     * @node_name 过滤编辑数据
     * @param array $params
     * @return array
     */
    protected function filterEditData($params = []) {
        $data = [];
        if (is_array($params) && !empty($params)) {
            $data = $params;
        }
        return $data;
    }

    public function batchAdd(){
        $batch_data = [
            [
                'name' => '111',
                'description' => 'desc',
                'created_time' => date('Y-m-d H:id:s'),
                'status' => 1
            ],
            [
                'name' => '222',
                'description' => 'desc',
                'created_time' => date('Y-m-d H:id:s'),
                'status' => 1
            ],
            [
                'name' => '333',
                'description' => 'desc',
                'created_time' => date('Y-m-d H:id:s'),
                'status' => 1
            ]
        ];
        return $this->defaultModel->batchAdd($batch_data);
    }


    //判断是否有新增功能和编辑功能
    public function getShowAddEdit($type="add")
    {
        $show_arr=array();
        switch($type){
            case "add":
                $field_add=$this->addFieldMap;
                foreach($field_add as $vo){
                    if($vo['field_name']=="id" || $vo['field_name']=="" ) continue;
                    if($vo['is_show']===false){
                        $show_arr[]=0;
                    }else{
                        $show_arr[]=1;
                    }
                }
            break;
            case "edit":
                $field_edit=$this->editFieldMap;
                foreach($field_edit as $vo){
                    if($vo['field_name']=="id" || $vo['field_name']=="") continue;
                     if($vo['is_show']===false){
                        $show_arr[]=0;
                    }else{
                        $show_arr[]=1;
                    }
                }
            default:break;
        }
        
        if(in_array(1,$show_arr)){
            $is_show=1;
        }else{
            $is_show=0;
        }

        return $is_show;
    }
}