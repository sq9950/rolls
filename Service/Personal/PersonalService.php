<?php
/**
 * Desc: 个人中心服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015-12-25 16:05:29
 */

namespace Service\Personal;
class PersonalService extends \Service\Service{

    public $ret;
    public $userService;
    public $status_switch = array(
        'on'    =>  1,
        'off'   =>  0
    );
    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
        $this->userService = new \Service\Common\UserService();
    }


    public function initModels(){
        $this->models = new \stdClass();
        $this->models->user =   new \Model\User();
    }


    /**
     * 保存用户密码
     * @param array $params
     * @return array
     */
    public function editPass($params = array()){
        if(!isset($params['old_password'])){
            return array('status' => 0, 'info' => '旧密码不能为空！');
        }
        if(!isset($params['new_password'])){
            return array('status' => 0, 'info' => '新密码不能为空！');
        }
        if($params['new_password'] == $params['old_password']){
            return array('status' => 0, 'info' => '新密码不能和旧密码相同！');
        }
        $logged_user_id = $this->userService->getLoginedUserId();
        $valid_passwd = $this->checkPassValid($params['new_password'], $logged_user_id);
        if(1 != $valid_passwd['status']){
            return $valid_passwd;
        }
        $encryted_password = $this->userService->encryptPassword($params['old_password']);
        $where  = array('id' => $logged_user_id, 'password' => $encryted_password);
        $count = $this->models->user->getListCountByWhere($where);
        if(!$count){
            return array('status' => 0, 'info' => '旧密码错误！');
        }
        $data['password'] = $this->userService->encryptPassword($params['new_password']);;
        $res = $this->models->user->updateById($logged_user_id, $data);
        if($res){
            $this->ret = array('status' => 1, 'info' => '修改密码成功');
        }else{
            $this->ret = array('status' => 0, 'info' => '修改密码失败');
        }
        return $this->ret;
    }


    /**
     *获取用户配角
     *@param int $user_id
     *@return array
     *@author huangzhongxi | huangzhongxi@yundun.com
     */
    public function getUserRole($user_id=0)
    {
        $where = array('user_id' => $user_id);
        $role_user = $this->userService->getUserRoleByWhere($where);
        return $role_user ? $role_user : array();
    }

    /**
     * 验证密码是否合法
     * @param string $password
     * 密码规则：
     *  1. 不能为空
     *  2. 长度大于8位
     *  3. 不能纯数字
     *  4. 不能纯字母
     *  5. 不能和用户名相同
     * @param int $user_id
     * @return array
     */
    public function checkPassValid($password = '', $user_id = 0){
        $user_id = intval($user_id);
        $password = trim($password);
        $regex_list = array(
            'number' => '/^\d+$/',
            'string' => '/^[a-zA-Z]+$/',
        );
        if($user_id && $password){
            if(strlen($password) < 8){
                return array('status' => 0, 'info' => '密码长度不能低于8位！');
            }
            if(preg_match($regex_list['number'], $password)){
                return array('status' => 0, 'info' => '密码不能为纯数字！');
            }
            if(preg_match($regex_list['string'], $password)){
                return array('status' => 0, 'info' => '密码不能为纯字母！');
            }
            $where = array('id' => $user_id);
            $user_info = $this->models->user->getOneByWhere($where);
            if(!isset($user_info['account'])){
                return array('status' => 0, 'info' => '未查询到用户名');
            }
            if($user_info['account'] == $password){
                return array('status' => 0, 'info' => '密码不能和用户名相同！');
            }
        }else{
            return array('status' => 0, 'info' => '密码或用户名为空');
        }
        return array('status' => 1, 'info' => '密码验证正确');
    }
}