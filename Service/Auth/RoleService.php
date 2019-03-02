<?php
/**
 * Desc: 角色授权服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/28
 */

namespace Service\Auth;
class RoleService extends \Service\Service{

    public $ret;
    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }

    public function initModels(){
        $this->models = new \stdClass();
        $this->models->role                          = new \Model\Role();
        $this->models->role_user                     = new \Model\RoleUser();
    }

    public function getRoleList($params = array()){
        $result = array(
            'count'     =>  0,
            'data'      => array()
        );
        extract($params);
        empty($where) && $where = array();
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 20;
        empty($order) && $order = array();
        $result['count'] = $this->models->role->getListCountByWhere($where);
        if($result['count']){
            $result['data'] = $this->models->role->getListByWhere($where, $offset, $limit, $order);
            if(is_array($result['data']) && !empty($result['data'])){
                foreach($result['data'] as $key => $val){
                    $result['data'][$key]['status_bool'] = $val['status'] ? true : false;
                }
            }
        }

        return $result;
    }

    public function getRoleListByWhere($where = array()){
        $role_list = [];
        $list = $this->models->role->getListByWhere($where);
        if(!empty($list)){
            foreach($list as $val){
                $role_list[$val['id']] = $val;
            }
        }
        return $role_list;
    }

    public function saveRole($params = array()){
        if(empty($params['name'])){
            $this->ret['info'] = '角色名称不能为空';
        }else{
            $where['name'] = $params['name'];
            $count = $this->models->role->getListCountByWhere($where);
            if($count){
                $this->ret['info'] = '角色名称已经存在';
            }else{
                $data['name'] = $params['name'];
                $data['status'] = 1;
                $res = $this->models->role->add($data);
                if($res){
                    $this->ret = array('status' => 1, 'info' => '新增角色成功');
                }else{
                    $this->ret['info'] = '新增角色失败';
                }
            }
        }
        return $this->ret;
    }

    public function deleteRole($params = array()){
        if(!isset($params['role_id']) || empty($params['role_id'])){
            $ret['info'] = '请选择要删除的角色';
        }else{
            $where['role_id'] = $params['role_id'];
            $count = $this->models->role_user->getListCountByWhere($where);
            if($count){
                $this->ret['info'] = '该角色已被使用，无法删除';
            }else{
                $res = $this->models->role->deleteByWhere(array('id' => $params['role_id']));
                if($res){
                    $this->ret = array('status' => 1, 'info' => '删除角色成功');
                }else{
                    $this->ret['info'] = '删除角色失败';
                }
            }

        }
        return $this->ret;
    }

    public function editStatus($params = array()){
        $role_id = $params['id'];
        $status = $params['status'];
        if(!$role_id){
            $this->ret['info'] = '角色不存在';
        }else{
            $where['role_id'] = $role_id;
            $count = $this->models->role_user->getListCountByWhere($where);
            if(!$status && $count){
                $this->ret['info'] = '该角色已被使用，无法禁用';
            }else{
                $data['status'] = $status;
                $res = $this->models->role->updateById($role_id, $data);
                if($res){
                    $this->ret = array('status' => 1, 'info' => '设置状态成功');
                }else{
                    $this->ret['info'] = '设置状态失败';
                }
            }
        }
        return $this->ret;
    }

    /**
     * @node_name 获取一条角色信息
     * @param array $where
     * @return array
     */
    public function getOneRoleInfo($where=[])
    {
        if(empty($where)) return false;

        $info=$this->models->role->getOneByWhere($where);

        return $info;
    }

}