<?php
/**
 * Desc: 会员服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015-8-6
 */

namespace Service\Auth;
class UserService extends \Service\Service{

    public $ret;
    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }

    public function initModels(){
        $this->models = new \stdClass();
        $this->models->user                          = new \Model\User();
        $this->models->role                          = new \Model\Role();
        $this->models->role_user                     = new \Model\RoleUser();
    }

    public function getUserList($params = array()){
        $result = array(
            'count'     =>  0,
            'data'      => array()
        );
        extract($params);
        empty($where) && $where = array();
        if(isset($where['`role_id`'])){
            $role_id = intval($where['`role_id`']);
            $where['_string'] = " id in ( select user_id from {$this->models->role_user->table_name} where role_id = {$role_id} ) ";
            unset($where['`role_id`']);
        }
        !isset($where['status']) && $where['status'] = array('in', array(0,1));
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 20;
        empty($order) && $order = array();
        $result['count'] = $this->models->user->getListCountByWhere($where);
        if($result['count']){
            $user_list = $this->models->user->getListByWhere($where, $offset, $limit, $order);
            if(is_array($user_list) && !empty($user_list)){
                foreach($user_list as $key => $val){
                    $user_ids[] = $val['id'];
                    $user_list[$key]['status_bool'] = $val['status'] ? true : false;
                }
                $where = array('user_id' => array('in', $user_ids));
                $role_list = $this->models->role->getListByWhere();
                $role_user = $this->models->role_user->getListByWhere($where);
                if(is_array($role_user) && !empty($role_user)){
                    foreach($role_user as $key => $val){
                        $user_role[$val['user_id']] = $val['role_id'];
                    }
                    foreach($user_list as $key => $val){
                        $user_list[$key]['role_id'] = isset($user_role[$val['id']]) ? $user_role[$val['id']] : 0;
                    }
                }
                $result['data']['user_list'] = $user_list;
                $result['data']['role_list'] = $role_list;
            }
        }

        return $result;
    }

    /**
     * 删除账户
     * 删除用户记录
     * 删除用户管理的角色记录
     * @param array $params
     * @return array
     */
    public function deleteUser($params = array()){
        $administor_id = $this->global_config['ADMIN_USER_ID'];
        $user_id = intval($params['user_id']);
        if($user_id){
            if($user_id != $administor_id){
                $where['id'] = $user_id;
                $user_info = $this->models->user->getOneByWhere($where);
                if(!empty($user_info)){
                    $res = $this->models->user->deleteByWhere($where);
                    if($res){
                        $where = ['user_id' => $user_id];
                        $res = $this->models->role_user->deleteByWhere($where);
                        if($res){
                            $this->ret = array('status' => 1, 'info' => '删除用户成功');
                        }else{
                            $this->ret = ['status' => 0, 'info' => '删除用户的角色关系失败'];
                        }
                    }else{
                        $this->ret = array('status' => 1, 'info' => '删除用户失败');
                    }
                    $memberServie = new \Service\Common\MemberService();
                    $member_info = $memberServie->getLoginedMemberInfo();
                    isset($member_info['id']) && $log_params['user_id'] = $member_info['id'];
                    isset($member_info['nickname']) && $log_params['nickname'] = $member_info['nickname'];
                    $log_params['method'] = 8;  //post
                    $userLogService = new \Service\Log\LogService($log_params);
                    $user_name = empty($user_info['nickname']) ? $user_info['account'] : $user_info['nickname'];
                    $userLogService->save( "删除账户{$user_name}", 'deleteUser');
                }else{
                    $this->ret = array('status' => 0, 'info' => '用户不存在');
                }

            }else{
                $this->ret = array('status' => 0, 'info' => '超级管理员不能删除');
            }
        }else{
            $this->ret = array('statua' => 0, 'info' => '未指定用户');
        }
        return $this->ret;
    }


    /**
     *获取所有用户id,nickname
     *return array("id"=>"nickname",...)
     */ 

    public function getUserNicknameAndId()
    {
        $list=$this->models->user->getListByWhere();

        $data=array();

        if(!empty($list)){
            foreach($list as $vo){
              $data[$vo['id']]=$vo['nickname'];
            }   
        }

        return $data;
    }

}