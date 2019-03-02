<?php
/**
 * 用户控制类
 * Created by PhpStorm.
 * User: xuan
 * Date: 2015/6/5
 * Time: 9:37
 */

namespace Controller\Admin\Auth;
use Service\Cache\UserCacheService;
use Service\Common\PasswordService;
class User extends \Controller\Admin\Common\Common
{
    public $role_model;
    public $user_model;
    public $role_user_model;
    public $node_model;
    public $access_model;
    private $personalService;
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
        $this->personalService = new \Service\Personal\PersonalService();

    }

    public function index()
    {

        $actions['getUserList'] = \Url::get_function_url('auth', 'user', 'getUserList',array(),true);
        $actions['editStatus'] = \Url::get_function_url('auth', 'user', 'editStatus',array(),true);
        $actions['add'] = \Url::get_function_url('auth', 'user', 'add',array(),true);
        $actions['edit'] = \Url::get_function_url('auth', 'user', 'editUser',array(),true);
        $actions['delete'] = \Url::get_function_url('auth', 'user', 'deleteUser',array(),true);
        $actions['setRole'] = \Url::get_function_url('auth', 'user', 'setRole',array(),true);
        $actions['manager'] = \Url::get_function_url('auth', 'user', 'manager',array(),true);
        $actions['resetPasswd'] = \Url::get_function_url('auth', 'user', 'resetPasswd',array(),true);

        $role_list = $this->role_model->get_role_list(array('status' => 1));
        $this->view->assign('role_list', $role_list);
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Auth/user/index.html');
    }

    public function getUserList(){
        $roleService = new \Service\Auth\UserService();
        $params = $this->parseJplistStatuses($this->req['statuses']);
        $this->ret = $roleService->getUserList($params);
        $this->ajaxReturn($this->ret);
    }

    public function editStatus(){
        $user_id = $this->request->post('user_id');
        $status = $this->request->post('status');
        $data['status'] = $status;
        $ret = array('status' => 0, 'info' => '修改状态失败');
        if(!$user_id){
            $ret['info'] = '非法操作';
        }else{
            $res = $this->user_model->updateById($user_id, $data);
            $res && $ret = array('status' => 1, 'info' => '修改状态成功');
        }
        $message = $status ? '启用用户' : '禁用用户';
        $log_params['message'] = "{$message}：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    /**
     * 修改用户权限组
     */
    public function setRole(){
        $post = $this->request->post;
        $ret = array('status' => 0, 'info' => '修改权限组失败');
        if(!isset($post['user_id']) || empty($post['user_id'])){
            $ret['info'] = '请选择用户';
        }elseif(!isset($post['role_id']) ){
            $ret['info'] = '请选择权限组';
        }else{
            $where['user_id'] = $post['user_id'];
            $count = $this->role_user_model->getListCountByWhere($where);
            if(!$count){
                $data['user_id'] = $post['user_id'];
                $data['role_id'] = $post['role_id'];
                $res = $this->role_user_model->add($data);
            }else{
                $data['role_id'] = $post['role_id'];
                $res = $this->role_user_model->updateByWhere($where, $data);
            }
            if($res){
                $userCacheService = new UserCacheService();
                $userCacheService->clearUserCacheByGroupName($post['user_id']);
                $ret = array('status' => 1, 'info' => '修改成功');
            }
        }
        $log_params['message'] = "修改用户权限组：{$ret['info']}";
        $log_params['params'] = json_encode($post, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    public function add(){
        $post = $this->request->post;
        $ret = array('status' => 0, 'info' => '新增用户失败');
        if(!isset($post['user_name']) || empty($post['user_name'])){
            $ret['info'] = '请输入用户名';
        }elseif(!isset($post['role_id']) || empty($post['role_id'])){
            $ret['info'] = '请选择权限组';
        }else{
            $is_vaild = $this->is_vaild_username($post['user_name']);
            if(!$is_vaild['status']){
                $ret['info'] = $is_vaild['info'];
            }else{
                $where['account'] = $post['user_name'];
                $count = $this->user_model->getListCountByWhere($where);
                if($count){
                    $ret['info'] = '用户名已存在，请换一个吧~';
                }else{

                    $data['password'] = PasswordService::getEncryPassword(123456);
                    $data['source'] = $post['source'];
                    $data['account'] = $post['user_name'];
                    $data['nickname'] = $post['nickname'];
                    $data['status'] = 1;
                    $res = $this->user_model->add($data);
                    if($res){
                        $data = array();
                        $data['user_id'] = $res;
                        $data['role_id'] = $post['role_id'];
                        $this->role_user_model->add($data);
                        $ret = array('status' => 1, 'info' => '新增用户成功');
                    }
                }
            }
        }
        $res['status'] && $log_params['params'] = $post;
        $log_params['message'] = "新增账户：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
        
    }

    /**
     * 重置用户密码
     */
    public function resetPasswd(){
        $post = $this->request->post;
        $ret = array('status' => 0, 'info' => '重置密码失败');
        if(!isset($post['new_passwd']) || empty($post['new_passwd'])){
            $ret['info'] = '请输入新密码';
        }elseif(!isset($post['user_id']) || empty($post['user_id'])){
            $ret['info'] = '请选择用户';
        }elseif(!$this->_isLogin()){
            $ret['info'] = '请您先登录';
        }else{
            $valid_pass = $this->personalService->checkPassValid($post['new_passwd'], $post['user_id']);
            if(1 != $valid_pass['status']){
                $this->ajaxReturn($valid_pass);
            }

            $data['password'] = PasswordService::getEncryPassword($post['new_passwd']);
            $res = $this->user_model->updateById($post['user_id'], $data);
            $res && $ret = array('status' => 1, 'info' => '重置密码成功', 'data' => $res);

        }
        $log_params['message'] = "重置密码：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    public function editUser(){
        $userService = new \Service\Common\UserService();
        if(IS_POST){
            $user_id = intval($this->post['user_id']);
            if($user_id){
                $data['nickname'] = trim($this->post['nickname']);
                $this->ret = $userService->saveUserInfo($user_id, $data);
            }else{
                $this->ret = array('status' => 0, 'info' => '未指定用户');
            }
            $log_params['message'] = "编辑用户：{$this->ret['info']}";
            $log_params['params'] = json_encode($this->post, JSON_UNESCAPED_UNICODE);
            $this->saveLog($log_params);
        }else{
            $user_id = $this->get['user_id'];
            if($user_id){
                $user_info = $userService->getUserInfoByWhere(array('id' => $user_id));
                $this->ret = array('status' => 1, 'info' => '获取用户信息成功', 'data' => $user_info);
            }else{
                $this->ret = array('status' => 0, 'info' => '获取用户信息失败');
            }
        }

        $this->ajaxReturn($this->ret);
    }

    /**
     * 验证是否是合法的用户名
     * @param string $username
     * @return bool
     */
    private function is_vaild_username($username = ''){
        $is_vaild = array('status' => 0, 'info' => '用户名必须为3-16位的字母或者数字,下划线');
        $user = '/^[a-z0-9_-]{3,16}$/';
        preg_match($user, $username) && $is_vaild = array('status' => 1, 'info' => '验证通过');

        return $is_vaild;
    }
    /**
     * 验证是否是合法的密码
     * @param string $password
     * @return bool
     */
    private function is_vaild_password($password = ''){
        $is_vaild = array('status' => 0, 'info' => '密码必须为6-20位');
        $reg = '/^[\s\S]{6,20}$/';
        preg_match($reg, $password) && $is_vaild = array('status' => 1, 'info' => '验证通过');

        return $is_vaild;
    }

    /**
     * 查询用户所属的角色ID
     * @param array $user_ids
     * @return array
     */
    private function get_user_role_ids($user_ids = array()){
        $users = array();
        if(is_array($user_ids) && !empty($user_ids)){
            $where['user_id'] = array('in', $user_ids);
            $list = $this->role_user_model->getListByWhere($where);
            if(is_array($list) && !empty($list)){
                foreach($list as $val){
                    $users[$val['user_id']] = $val['role_id'];
                }
            }
        }
        return $users;
    }

    public function deleteUser(){
        $userService = new \Service\Auth\UserService();
        $res = $userService->deleteUser($this->post);
        $log_params['message'] = "删除用户：{$res['info']}";
        $log_params['params'] = json_encode($this->post, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($res);
    }

}