<?php
/**
 * Desc: 后台账户服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/29
 */

namespace Service\Common;
use Service\Common\PasswordService;
class UserService extends \Service\Service{

    const ERROR_PASSWD_MAX      =   5;     //密码最大错误次数
    const ERROR_PASSWD_EXPIRED  =   3600;  //超过最大密码错误次数时，限制登录时间（秒）
    const USER_STATUS_OPEN      =   1;
    const USER_STATUS_FORBID    =   0;

    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }

    public function initModels(){
        $this->models = new \stdClass();
        $this->models->user                        = new \Model\User();
        $this->models->role_user                   = new \Model\RoleUser();
        $this->models->member                      = new \Model\Member();
    }

    public function getUserInfoById($id = 0){
        $id = intval($id);
        if($id){
            $where['id'] = $id;
            $member_info = $this->models->member->getOneByWhere($where);
            $this->ret = array(
                'status' => 1,
                'info'   => "查询域名成功",
                'data'   => $member_info
            );
        }else{
            $this->ret['info'] = '查询用户信息失败';
        }
        return $this->ret;
    }

    public function getLoginedUserId(){
        return isset($_SESSION[$this->global_config['USER_AUTH_KEY']]['id'])?$_SESSION[$this->global_config['USER_AUTH_KEY']]['id']:null;
    }

    public function setLoginedUserSeesion($data = array()){
        $user_id = $this->getLoginedUserId();
        if($user_id){
            isset($data['account']) && $_SESSION[$this->global_config['USER_AUTH_KEY']]['account'] = $data['account'];
            isset($data['password']) && $_SESSION[$this->global_config['USER_AUTH_KEY']]['password'] = $data['password'];
            isset($data['nickname']) && $_SESSION[$this->global_config['USER_AUTH_KEY']]['nickname'] = $data['nickname'];
            isset($data['mobile']) && $_SESSION[$this->global_config['USER_AUTH_KEY']]['mobile'] = $data['mobile'];
            isset($data['status']) && $_SESSION[$this->global_config['USER_AUTH_KEY']]['status'] = $data['status'];
            isset($data['remark']) && $_SESSION[$this->global_config['USER_AUTH_KEY']]['remark'] = $data['remark'];
            isset($data['avatar']) && $_SESSION[$this->global_config['USER_AUTH_KEY']]['avatar'] = $data['avatar'];
        }
    }

    public function getLoginedUserInfo(){
        return isset($_SESSION[$this->global_config['USER_AUTH_KEY']]) ?
            $_SESSION[$this->global_config['USER_AUTH_KEY']] : array();
    }



    public function getUserListByWhere($where = array(), $fields = array(), $user_id_key = false){
        $user_list = array();
        if(is_array($where) && !empty($where)){
            $list = $this->models->user->getListByWhere($where);
            if($user_id_key && !empty($list)){
                foreach($list as $key => $val){
                    $user_list[$val['id']] = $val;
                }
            }else{
                $user_list = $list;
            }
        }
        return $user_list;
    }
    public function getUserInfoByWhere($where = array()){
        $user_list = array();
        if(is_array($where) && !empty($where)){
            $user_list = $this->models->user->getOneByWhere($where);
        }
        return $user_list;
    }

    public function saveUserInfo($user_id, $data = array()){
        if($user_id){
            $res = $this->models->user->updateById($user_id, $data);
            if($res){
                $this->ret = array('status' => 1, 'info' => '更新账户成功');
            }else{
                $this->ret = array('status' => 0, 'info' => '更新账户失败');
            }
        }else{
            $this->ret = array('status' => 0, 'info' => '未指定账户');
        }
        return $this->ret;
    }

    public function encryptPassword($password = ''){
        return PasswordService::getEncryPassword($password);
    }

    public function checkUserFields($user_info = array()){
        if(isset($user_info['account']) && !validAccount($user_info['account'])){
            return array('status' => 0, 'info' => '用户名只能为数字、字母、下划线(-、_)');
        }
        if(isset($user_info['mobile']) && !validMobile($user_info['mobile'])){
            return array('status' => 0, 'info' => '手机号不合法');
        }
        return array('status' => 1, 'info' => '用户信息合法');
    }

    /**
     * 检测用户名是否存在
     * @param $account
     * @param array $where
     * @return bool
     */
    public function isExistedAccount($account, $where = array()){
        $where['account'] = trim($account);
        $count = $this->models->user->getListCountByWhere($where);
        $isExisted = $count ? true : false;
        return $isExisted;
    }

    /**
     * 检测用户名状态
     * @return bool
     */
    public function getLoggedUserStatus(){
        $account_status = self::USER_STATUS_FORBID;
        $user_id = $this->getLoginedUserId();
        if($user_id){
            $where = array('id' => $user_id);
            $account_info = $this->models->user->getOneByWhere($where);
            isset($account_info['status']) && $account_status = $account_info['status'];
        }

        return $account_status;
    }

    /**
     * 检测用户名状态
     * @param array $where
     * @return bool
     */
    public function getAccountStatus($where = array()){
        $account_status = self::USER_STATUS_FORBID;
        if(is_array($where) && !empty($where)){
            $account_info = $this->models->user->getOneByWhere($where);
            isset($account_info['status']) && $account_status = $account_info['status'];
        }

        return $account_status;
    }

    /**
     * 更新登录密码错误次数和时间
     * @param string $account
     * @return bool
     */
    public function updateErrorPasswd($account = ''){
        $update_res = false;
        if($account){
            $where = array('account' => $account);
            $account_info = $this->models->user->getOneByWhere($where);
            if(!empty($account_info) && $account_info['error_passwd_count'] < self::ERROR_PASSWD_MAX){
                $data = array(
                    'error_passwd_count' => $account_info['error_passwd_count'] + 1,
                    'error_passwd_last' => date('Y-m-d H:i:s', time())
                );
                $res = $this->models->user->updateByWhere($where, $data);
                $res && $update_res = true;
            }
        }
        return $update_res;
    }

    /**
     * 清除登录密码错误次数
     * @param string $account
     * @return bool
     */
    public function clearErrorPasswd($account = ''){
        $update_res = false;
        if($account){
            $where = array('account' => $account);
            $account_info = $this->models->user->getOneByWhere($where);
            if(!empty($account_info)){
                $data = array(
                    'error_passwd_count' => 0
                );
                $res = $this->models->user->updateByWhere($where, $data);
                $res && $update_res = true;
            }
        }
        return $update_res;
    }

    /**
     * 验证登录密码错误次数和时间，是否可以登录
     * @param string $account
     * @return bool
     */
    public function checkErrorPasswd($account = ''){
        $update_res = array('status' => 0, 'info' => '禁止登录');
        if(!empty($account)){
            $where = array('account' => $account);
            $account_info = $this->models->user->getOneByWhere($where);
            if(!empty($account_info)){
                if(strtotime($account_info['error_passwd_last']) + self::ERROR_PASSWD_EXPIRED > time()
                    && $account_info['error_passwd_count'] >= self::ERROR_PASSWD_MAX){
                    return array('status' => 0, 'info' => '错误密码大于最大次数，暂不能登录');
                }else{
                    return array('status' => 1, 'info' => '无错误密码限制');
                }
            }else{
                $update_res = array('status' => 0, 'info' => '未查询到用户信息');
            }
        }
        return $update_res;
    }

    /**
     * 更新登录用户最后一次成功登录时间
     */
    public function updateLastLogin(){
        $logged_id = $this->getLoginedUserId();
        if($logged_id){
            $data = array('last_login' => date('Y-m-d H:i:s', time()));
            $this->models->user->updateById($logged_id, $data);
        }
    }

    /**
     *获取全部后台用户信息
     *@param array $where
     *@return array
     *@author huangzhongxi | huangzhongxi@yundun.com
     */
    public function getUserAllList($where=array())
    {
        $list=$this->models->user->getListByWhere($where);
        return $list;
    }

    /**
     * 查询用户角色
     * @param array $where
     * @return array
     */
    public function getUserRoleByWhere($where = array()){
        $role_user = array();
        if($where){
            $role_user = $this->models->role_user->getOneByWhere($where);
        }
        return $role_user;
    }


    /**
     * @node_name 查询角色下所有用户信息
     *
     * @author huangzhongxi@yundun.com
     */
    public function  getRoleByAllUserInfo($role_id=0)
    {
        $user_list=[];

        if(!empty($role_id)){
            $where=['role_id'=>$role_id];

            $user_role=$this->models->role_user->getListByWhere($where);

            if(!empty($user_role)){
                $user_ids=[];
                foreach($user_role as $vo){
                    $user_ids[]=$vo['user_id'];
                }

                $user_where=['id'=>['in',$user_ids]];

                $user_list=$this->models->user->getListByWhere($user_where);
            }
        }

        return $user_list;
    }

}