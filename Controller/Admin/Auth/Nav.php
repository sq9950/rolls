<?php
/**
 * Desc: 导航菜单管理
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/8/21 16:39
 */
namespace Controller\Admin\Auth;
class Nav extends \Controller\Admin\Common\Common{

    public $node_model;
    public $nodeService;
    public $nav_prefix;
    public function __construct(){
        parent::__construct();
        $this->setHeaderFooter();
        $this->node_model = new \Model\Node();
        $this->nodeService = new \Service\Auth\NodeService();
        include_once(__LIBRARY__.'/Util/Url.class.php');
        $this->nav_prefix = 'Controller'.DIRECTORY_SEPARATOR.'Admin';
    }

    public function index(){

        $actions['getNavList'] = \Url::get_function_url('auth', 'nav', 'getNavList',array(),true);
        $actions['navEdit'] = \Url::get_function_url('auth', 'nav', 'navEdit',array(),true);
        $actions['navAdd'] = \Url::get_function_url('auth', 'nav', 'navAdd',array(),true);
        $actions['navDelete'] = \Url::get_function_url('auth', 'nav', 'navDelete',array(),true);
        $actions['setNavStatus'] = \Url::get_function_url('auth', 'nav', 'setNavStatus',array(),true);
        $actions['getNavBasic'] = \Url::get_function_url('auth', 'nav', 'getNavBasic',array(),true);
        $this->view->assign('actions' ,$actions);
        $this->view->display('Admin/Auth/nav/index.html');
    }

    public function getNavList(){
        $params = $this->parseJplistStatuses($this->req['statuses']);
        $nodeService = new \Service\Auth\NavService();
        $result = $nodeService->getNavList($params);
        include_once(__LIBRARY__.'/Util/Url.class.php');
        foreach((array)$result['data'] as $key => $node){
            $result['data'][$key]['status_bool'] = $node['status'] ? true : false;
            $result['data'][$key]['action']['navEdit'] = \Url::get_function_url('auth', 'nav', 'navEdit',array('id' => $node['id']),true);
            $result['data'][$key]['action']['navDelete'] = \Url::get_function_url('auth', 'nav', 'navDelete',array('id' => $node['id']),true);
        }

        $this->ajaxReturn($result);
    }

    /**
     * 查询基本信息
     */
    public function getNavBasic(){
        $node_info = $this->nodeService->getNodeBasic($this->req['id']);
        if(!empty($node_info)){
            $action_params = array(
                'navEdit'		=> 'navEdit',
                'setNavStatus'		=> 'setNavStatus',
            );
            $node_info['actions'] = $this->buildUrl($action_params);
            $this->ret = array('status' => 1, 'info' => '查询成功', 'data' => $node_info);
        }else{
            $this->ret = array('status' => 0, 'info' => '查询失败', 'data' => []);
        }
        $this->ajaxReturn($this->ret);
    }

    public function edit(){

    }

    /**
     * 保存节点基础信息
     */
    public function navSave(){
        $post_info = $this->request->post;
        $ret = array('status' => 0, 'info' => '保存失败');
        if(!isset($post_info['node_id']) || empty($post_info['node_id'])){
            $ret['info'] = '非法操作！';
        }elseif(!isset($post_info['node_title']) || empty($post_info['node_title'])){
            $ret['info'] = '标题不能为空';
        }else if(!isset($post_info['node_name']) || empty($post_info['node_name'])){
            $ret['info'] = '菜单名称不能为空';
        }else{
            $node_id = intval($post_info['node_id']);
            $data['name'] = $post_info['node_name'];
            $data['nav_name'] = $post_info['node_nav_name'];
            $data['title'] = $post_info['node_title'];
            $data['status'] = isset($post_info['node_status']) ? 1 : 0;
            $data['display'] = isset($post_info['display']) ? $post_info['display'] : 0;
            if(!empty($data['nav_name'])){
                $where = array(
                    'id'        => array('not in', $node_id),
                    'nav_name' => $data['nav_name']
                );
                $count = $this->node_model->getListCountByWhere($where);
            }else{
                $count = 0;
            }
            if($count){
                $ret['info'] = '菜单别名已存在';
            }else{
                $res = $this->node_model->update_node_by_id($node_id, $data);
                $res && $ret = array('status' => 1, 'info' => '保存成功', 'data' => $res);
            }
        }
        $log_params['message'] = "菜单导航编辑：{$ret['info']}";
        $log_params['params'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }


    /**
     * 新增方法节点
     */
    public function navAdd(){
        if(IS_POST){
            $post_info = $this->request->post;
            $ret = array('status' => 0, 'info' => '添加新导航失败');
            if(!isset($post_info['node_title']) || empty($post_info['node_title'])){
                $ret['info'] = '标题不能为空';
            }else if(!isset($post_info['node_name']) || empty($post_info['node_name'])){
                $ret['info'] = '操作名不能为空';
            }else{
                $data['name'] = $post_info['node_name'];
                !empty($post_info['node_nav_name']) && $data['nav_name'] = $post_info['node_nav_name'];
                $data['title'] = $post_info['node_title'];
                $where = array('pid' => $data['pid'], 'name' => $data['name']);
                $count = $this->node_model->getListCountByWhere($where);
                if($count){
                    $ret = array('status' => 0, 'info' => '导航名已经存在');
                }else{
                    $where = array();
                    $data['status'] = 1;
                    $data['level'] = 1;
                    $data['nav_name'] = '';
                    $data['display'] = isset($post_info['display']) ? intval($post_info['display']) : 1;
                    if($post_info['node_nav_name']){
                        $where['nav_name'] = $data['nav_name'];
                        $count = $this->node_model->getListCountByWhere($where);
                    }else{
                        $count = 0;
                    }
                    if($count){
                        $this->ret['info'] = '菜单别名已存在';
                    }else{
                        $res = $this->node_model->add_node($data);
                        if($res){
                            $ret['data'] = array(
                                'node_id'   => $res,
                                'node_name' => $data['name'],
                                'node_nav_name' => $data['nav_name'],
                                'node_title'=> $data['title'],
                                'node_edit_url' => \Url::get_function_url('auth', 'nav', 'navEdit',array(),true),
                                'node_delete_url' => \Url::get_function_url('auth', 'nav', 'navDelete',array(),true),
                            );

                            $ret['status'] = 1;
                            $ret['info'] = '添加新导航成功';
                        }
                    }
                }
            }
            $log_params['params'] = json_encode($data, JSON_UNESCAPED_UNICODE);
            $log_params['message'] = "新增菜单导航：{$ret['info']}";
            $this->saveLog($log_params);
            $this->ajaxReturn($ret);
        }else{
            $module_nodes = $this->node_model->get_module_nodes();
            $this->view->assign('navAdd',\Url::get_function_url('auth', 'nav', 'navAdd',array(),true));
            $this->view->assign('module_nodes', $module_nodes);
            $this->view->display('Admin/Auth/nav/add.html');
        }

    }

    /**
     * 保存节点基础信息
     */
    public function navEdit(){
        if(IS_POST){
            $post_info = $this->request->post;
            $ret = array('status' => 0, 'info' => '保存失败');
            if(!isset($post_info['node_id']) || empty($post_info['node_id'])){
                $ret['info'] = '非法操作！';
            }elseif(!isset($post_info['node_title']) || empty($post_info['node_title'])){
                $ret['info'] = '标题不能为空';
            }else if(!isset($post_info['node_name']) || empty($post_info['node_name'])){
                $ret['info'] = '菜单名不能为空';
            }else{
                $node_id = intval($post_info['node_id']);
                $data['name'] = $post_info['node_name'];
                $data['nav_name'] = $post_info['node_nav_name'];
                $data['title'] = $post_info['node_title'];
                $node_info = $this->node_model->get_node_info($node_id);
                if(!is_array($node_info) || empty($node_info)){
                    $ret['info'] = '当前节点不存在！';
                }else{
                    $where['id']    =  array('not in', $node_id);
                    $where['name']  =  $data['name'];
                    $where['level'] =  $node_info['level'];
                    $where['pid']   =  $node_info['pid'];

                    $count = $this->node_model->getListCountByWhere($where);
                    if($count){
                        $ret['info'] = '操作名在当前层级下已经存在';
                    }else{
                        if(!empty($data['nav_name'])){
                            $where = array(
                                'id'        => array('not in', $node_id),
                                'nav_name' => $data['nav_name']
                            );
                            $count = $this->node_model->getListCountByWhere($where);
                        }else{
                            $count = 0;
                        }
                        if($count){
                            $ret['info'] = '菜单别名已存在';
                        }else{
                            $res = $this->node_model->update_node_by_id($node_id, $data);
                            $res && $ret = array('status' => 1, 'info' => '修改成功', 'data' => $res);
                        }
                    }
                }
            }
            $log_params['message'] = "菜单编辑：{$ret['info']}";
            $log_params['params'] = json_encode($post_info, JSON_UNESCAPED_UNICODE);
            $this->saveLog($log_params);
            $this->ajaxReturn($ret);
        }else{
            $node_id = $this->request->get('id');
            if($node_id){
                $node_info = $this->node_model->get_node_info($node_id);
                if(is_array($node_info) && !empty($node_info)){
                    $where['level'] = abs($node_info['level'] - 1);
                    $parent_nodes = $this->node_model->getListByWhere($where);
                    $node_info['parent_nodes'] = $parent_nodes;
                }
                $action_list = $this->node_model->get_action_node_list($node_id);
            }

            $action_params = array(
                'navSave' 		=> 'navSave',
                'navAdd' 		=> 'navAdd',
                'navEdit'		=> 'navEdit',
                'navDelete'	=> 'navDelete',
                'getNavNodeList'	=> 'getNavNodeList',
                'getNavBasic'	=> 'getNavBasic',
                'controlBatchAdd'	=> 'controlBatchAdd'
            );
            $actions = $this->buildUrl($action_params);
            $params = array('id' => $node_id);
            $this->view->assign('actions', $actions);
            $this->view->assign('params', $params);
            $this->view->assign('action_list', $action_list);

            $this->view->display('Admin/Auth/nav/edit.html');
        }

    }

    /**
     * 删除控制器节点
     */
    public function navDelete(){
        $node_id = intval($this->request->post('node_id'));
        $ret = array('status' => 0, 'info' => '删除失败');
        if(!$node_id){
            $ret['info'] = '非法操作！';
        }else{
            $count = $this->node_model->getListCountByWhere(" pid = {$node_id} ");
            if($count){
                $ret['info'] = '当前菜单节点下有子节点，请先删除子节点';
            }else{
                $res = $this->node_model->delete_node($node_id);
                $res && $ret = array('status' => 1, 'info' => '删除成功', 'data' => array('node_id' => $node_id));
            }
        }
        $log_params['message'] = "菜单导航删除：{$ret['info']}";
        $log_params['params'] = json_encode(array($node_id), JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    public function setNavStatus(){
        $navService = new \Service\Auth\NavService();
        $this->ret = $navService->setNavStatus($this->post);
        $message = $this->post['status'] ? '启用菜单' : '禁用菜单';
        $log_params['message'] = "{$message}：{$this->ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($this->ret);
    }

    public function getNavNodeList(){
        if(isset($this->req['pid']) && !empty($this->req['pid'])){
            $where = array('id' => $this->req['pid']);
            $nav_info = $this->nodeService->getNodeInfoByWhere($where);
            $nav_dir = __ROOT__. DIRECTORY_SEPARATOR . $this->nav_prefix . DIRECTORY_SEPARATOR. ucfirst($nav_info['name']);
            $where = array(
                'pid' => $this->req['pid']
            );
            $existed_nodes = $this->nodeService->getExistedNodeList($where);
            $new_nodes = $this->nodeService->getNewNodeList($nav_dir, $this->nav_prefix);
            $action_params = array(
                'controlAdd'		=> 'controlAdd',
                'controlSave'		=> 'controlSave',
                'controlDelete'		=> 'controlDelete',
            );
            $data['actions'] = $this->buildUrl($action_params);
            if(!empty($existed_nodes) || !empty($new_nodes)){
                $existed_names = array_column((array)$existed_nodes, 'name');
                $all_new_names = array_column((array)$new_nodes, 'name');
                $new_names = array_diff($all_new_names, $existed_names);
                $noexist_names = array_diff($existed_names, $all_new_names);
                foreach((array)$new_nodes as $key => $val){
                    if(!in_array($val['name'],$new_names)){
                        unset($new_nodes[$key]);
                    }
                }

                foreach((array)$existed_nodes as $key => $val){
                    $existed_nodes[$key]['is_existed'] = in_array($val['name'], $noexist_names) ? false : true;
                }
                $data['existed_nodes'] = $existed_nodes;
                $data['new_nodes'] = array_values($new_nodes);
                $this->ret = array('status' => 1, 'info' => '查询成功', 'data' => $data);
            }else{
                $this->ret = array('status' => 0, 'info' => '未查询到节点', 'data' => $data);
            }
        }

        $this->ajaxReturn($this->ret);
    }


    /**
     * 新增方法节点
     */
    public function controlAdd(){
        if(IS_POST){
            $post_info = $this->request->post;
            $ret = array('status' => 0, 'info' => '添加控制器节点失败');
            if(!isset($post_info['node_title']) || empty($post_info['node_title'])){
                $ret['info'] = '标题不能为空';
            }else if(!isset($post_info['node_name']) || empty($post_info['node_name'])){
                $ret['info'] = '控制器名不能为空';
            }else if(!isset($post_info['node_pid']) || empty($post_info['node_pid'])){
                $ret['info'] = '未指定所属菜单';
            }else{
                $data['name'] = $post_info['node_name'];
                !empty($post_info['node_nav_name']) && $data['nav_name'] = $post_info['node_nav_name'];
                $data['title'] = $post_info['node_title'];
                $where = array('pid' => $data['node_pid'], 'name' => $data['name']);
                $count = $this->node_model->getListCountByWhere($where);
                if($count){
                    $ret = array('status' => 0, 'info' => '控制器名已经存在');
                }else{
                    $where = array();
                    $data['status'] = 1;
                    $data['level'] = 2;
                    $data['nav_name'] = '';
                    $data['pid'] = $post_info['node_pid'];
                    $data['display'] = isset($post_info['display']) ? intval($post_info['display']) : 1;
                    if($post_info['node_nav_name']){
                        $where['nav_name'] = $data['nav_name'];
                        $count = $this->node_model->getListCountByWhere($where);
                    }else{
                        $count = 0;
                    }
                    if($count){
                        $this->ret['info'] = '控制器别名已存在';
                    }else{
                        $res = $this->node_model->add_node($data);
                        if($res){

                            $ret['status'] = 1;
                            $ret['info'] = '添加新方法成功';
                        }
                    }
                }
            }
            $log_params['params'] = json_encode($data, JSON_UNESCAPED_UNICODE);
            $log_params['message'] = "新增控制器节点：{$ret['info']}";
            $this->saveLog($log_params);
            $this->ajaxReturn($ret);
        }

    }

    /**
     * 批量新增方法节点
     */
    public function controlBatchAdd(){
        if(IS_POST){

            $ret = $this->nodeService->batchAdd($this->post['batch_data'], 2);
            $log_params['params'] = json_encode($this->post['batch_data'], JSON_UNESCAPED_UNICODE);
            $log_params['message'] = "批量新增控制器节点：{$ret['info']}";
            $this->saveLog($log_params);
            $this->ajaxReturn($ret);
        }

    }

    /**
     * 保存节点基础信息
     */
    public function controlSave(){
        $post_info = $this->post;
        $ret = array('status' => 0, 'info' => '保存失败');
        if(!isset($post_info['node_id']) || empty($post_info['node_id'])){
            $ret['info'] = '非法操作！';
        }elseif(!isset($post_info['node_title']) || empty($post_info['node_title'])){
            $ret['info'] = '标题不能为空';
        }else if(!isset($post_info['node_name']) || empty($post_info['node_name'])){
            $ret['info'] = '控制器名称不能为空';
        }else{
            $node_id = intval($post_info['node_id']);
            $data['name'] = $post_info['node_name'];
            $data['nav_name'] = $post_info['node_nav_name'];
            $data['title'] = $post_info['node_title'];
            $data['status'] = isset($post_info['node_status']) ? 1 : 0;
            $data['display'] = isset($post_info['display']) ? $post_info['display'] : 0;
            if(!empty($data['nav_name'])){
                $where = array(
                    'id'        => array('not in', $node_id),
                    'nav_name' => $data['nav_name']
                );
                $count = $this->node_model->getListCountByWhere($where);
            }else{
                $count = 0;
            }
            if($count){
                $ret['info'] = '控制器别名已存在';
            }else{
                $res = $this->node_model->update_node_by_id($node_id, $data);
                $res && $ret = array('status' => 1, 'info' => '保存成功', 'data' => $res);
            }
        }
        $log_params['message'] = "控制器节点编辑：{$ret['info']}";
        $log_params['params'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    /**
     * 删除控制器节点
     */
    public function controlDelete(){
        $node_id = intval($this->post['node_id']);
        $ret = array('status' => 0, 'info' => '删除失败');
        if(!$node_id){
            $ret['info'] = '非法操作！';
        }else{
            $count = $this->node_model->getListCountByWhere(" pid = {$node_id} ");
            if($count){
                $ret['info'] = '当前控制器节点下有方法节点，请先删除方法节点';
            }else{
                $res = $this->node_model->delete_node($node_id);
                $res && $ret = array('status' => 1, 'info' => '删除成功', 'data' => array('node_id' => $node_id));
            }
        }
        $log_params['message'] = "控制器节点删除：{$ret['info']}";
        $log_params['params'] = json_encode(array($node_id), JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }
}
