<?php
/**
 * 角色授权控制类
 * Created by PhpStorm.
 * User: xuan
 * Date: 2015/6/5
 * Time: 9:37
 */

namespace Controller\Admin\Auth;
class Role extends \Controller\Admin\Common\Common
{

    public $role_model;
    public $user_model;
    public $role_user_model;
    public $node_model;
    public $access_model;
    public $rbacCacheService;

    public function __construct()
    {
        parent::__construct();
        $this->setHeaderFooter();
        $this->role_model = new \Model\Role();
        $this->user_model = new \Model\User();
        $this->role_user_model = new \Model\RoleUser();
        $this->node_model = new \Model\Node();
        $this->access_model = new \Model\Access();
        include_once(__LIBRARY__ . '/Util/Url.class.php');
        $this->rbacCacheService = new \Service\Cache\RbacCacheService();
    }

    public function index()
    {

        $actions['getRoleList'] = \Url::get_function_url('auth', 'role', 'getRoleList',array(),true);
        $actions['Manager'] = \Url::get_function_url('auth', 'role', 'Manager',array(),true);
        $actions['saveRole'] = \Url::get_function_url('auth', 'role', 'saveRole',array(),true);
        $actions['editStatus'] = \Url::get_function_url('Auth', 'Role', 'editStatus');
        $actions['setNode'] = \Url::get_function_url('Auth', 'Role', 'setNode');
        $actions['delete'] = \Url::get_function_url('Auth', 'Role', 'delete');
        $actions['add'] = \Url::get_function_url('Auth', 'Role', 'add');
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Auth/role/index.html');
    }

    /**
     * 获取角色列表
     */
    public function getRoleList(){
        $roleService = new \Service\Auth\RoleService();
        $params = $this->parseJplistStatuses($this->req['statuses']);
        $this->ret = $roleService->getRoleList($params);
        $this->ajaxReturn($this->ret);
    }

    /**
     * 修改角色状态
     */
    public function editStatus(){
        $roleService = new \Service\Auth\RoleService();
        $this->ret = $roleService->editStatus($this->post);
        $message = $this->post['status'] ? '启用角色' : '禁用角色';
        $log_params['message'] = "{$message}：{$this->ret['info']}";
        $log_params['params'] = json_encode($this->post, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($this->ret);
    }

    public function Manager(){
        $actions['getNode'] = \Url::get_function_url('Auth', 'Role', 'getNode');
        $actions['saveNode'] = \Url::get_function_url('Auth', 'Role', 'saveNode');
        $this->view->assign('role_id', $this->request->get('role_id'));
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Auth/role/manager.html');
    }

    public function getNode(){
        $params = $this->request->get;
        $where['pid'] = isset($params['id']) ? intval($params['id']) : 0;
        $role_id = $params['role_id'];
        $list = $this->node_model->getListByWhere($where);
        if(is_array($list) && !empty($list)){

            $access_node_ids = $this->access_model->get_role_node_ids($role_id);
            foreach($list as $key => $node){
                $where = array();
                $where['pid'] = $node['id'];
                $count = $this->node_model->getListCountByWhere($where);
                $count && $list[$key]['isParent'] = true;
                in_array($node['id'], $access_node_ids) && $list[$key]['checked'] = true;
            }
        }

        $this->ajaxReturn($list);
    }

    public function saveNode(){
        $ret = array('status' => 1, 'info' => '保存成功');
        $change_checked_nodes = $this->request->post('change_checked_nodes');
        $role_id = $this->request->post('role_id');
        if(is_array($change_checked_nodes) && !empty($change_checked_nodes)){
            $res = true;
            foreach($change_checked_nodes as $node){
                $where = array();
                //设置当前节点
                $node_info = $this->node_model->getOneByWhere(" id = {$node['id']} ");
                $where['role_id'] = $role_id;
                $where['node_id'] = $node['id'];
                $count = $this->access_model->getListCountByWhere($where);

                if($count && 'false' == $node['checked']){
                    $res = $this->access_model->deleteByWhere($where);
                }else if(!$count && 'true' == $node['checked']){
                    $data = $where;
                    $data['level'] = $node_info['level'];
                    $res = $this->access_model->add($data);
                }
                //设置子节点
                $where = array();
                $where['pid'] = $node['id'];
                $list = $this->node_model->getListByWhere($where);
                if(is_array($list) && !empty($list)){
                    $sub_res = true;
                    foreach($list as $val){
                        $where = array();
                        $where['node_id'] = $val['id'];
                        $where['role_id'] = $role_id;
                        $count = $this->access_model->getListCountByWhere($where);
                        if($count && 'false' == $node['checked']){
                            $mid = $this->access_model->deleteByWhere($where);
                            if(!$mid){
                                $sub_res  = $mid;
                            }
                        }else if(!$count && 'true' == $node['checked']){
                            $data = $where;
                            $data['level'] = $val['level'];
                            $mid = $this->access_model->add($data);
                            if(!$mid){
                                $sub_res  = $mid;
                            }
                        }
                    }
                    !$sub_res && $ret = array('status' => 0, 'info' => '保存子节点失败');
                }
            }
            !$res && $ret = array('status' => 0, 'info' => '保存当前节点失败');
        }
        $log_params['params'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log_params['message'] = "保存节点：{$ret['info']}";
        $this->saveLog($log_params);
        if(1 == $ret['status']){
            //清除用户权限缓存
            $this->rbacCacheService->clearUserCacheByRoleId($role_id);
        }
        $this->ajaxReturn($ret);
    }


    public function add(){
        $post = $this->request->post;
        $ret = array('status' => 0, 'info' => '新增失败');
        if(!isset($post['role_name']) || empty($post['role_name'])){
            $ret['info'] = '新增角色失败';
        }else{
            $data['name'] = $post['role_name'];
            $data['status'] = 1;
            $res = $this->role_model->add($data);
            $res && $ret = array('status' => 1, 'info' => '新增角色成功');
        }
        $log_params['params'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log_params['message'] = "新增角色：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    public function delete(){
        $post = $this->request->post;
        $roleService = new \Service\Auth\RoleService();
        $this->ret = $roleService->deleteRole($post);
        $log_params['message'] = "删除角色：{$this->ret['info']}";
        $log_params['params'] = json_encode($post, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($this->ret);
    }

    public function saveRole(){
        if($this->post['role_name']){
            $roleService = new \Service\Auth\RoleService();
            $data['name'] = $this->post['role_name'];
            $this->ret = $roleService->saveRole($data);
        }else{
            $this->ret['info'] = '请输入角色名称';
        }
        $log_params['message'] = "新增角色：{$this->ret['info']}";
        $log_params['params'] = json_encode($this->post, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($this->ret);
    }

}