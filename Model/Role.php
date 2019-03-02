<?php
namespace Model;
class Role extends \Model\CommonModel{



	public function __construct(){
		parent::__construct();
        $this->table_name = 'rbac_role';
	}


    public function get_role_list($where = array()){
        $roles = array();
        $list = $this->getListByWhere($where);
        if(is_array($list) && !empty($list)){
            foreach($list as $val){
                $roles[$val['id']] = $val;
            }
        }
        return $roles;
    }

    /**
     * 获取角色信息
     * @param int $role_id
     * @return array|mixed
     */
    public function get_role_info($role_id = 0){
        $role_info = array();
        $role_id = intval($role_id);
        if($role_id){
            $where['id']=  $role_id;
            $res = $this->getOneByWhere($where);
            $role_info = $res ? $res : array();
        }
        return $role_info;
    }

    public function get_action_node_list( $pid = 0 ){
        $node_list = array();
        $nodes = $this->db->table($this->tbl_node)->where(" pid = {$pid} and status = 1 ")->select();
        if(is_array($nodes) && !empty($nodes)){
            foreach($nodes as $node){
                $node_list[$node['id']] = $node;
            }
        }
        return $node_list;
    }

    public function update_role_by_id($node_id = 0, $data = array()){
        $res = false;
        $node_id = intval($node_id);
        if($node_id && is_array($data) && !empty($data)){
            if(isset($data['id'])){
                unset($data['id']);
            }
            $res = $this->db->table($this->tbl_node)->where(" id = {$node_id} ")->update($data);
        }
        return $res;
    }

    public function add_node($data = array()){
        $res = false;
        if(is_array($data) && !empty($data)){
            $res = $this->db->table($this->tbl_node)->insert($data);
        }
        return $res;
    }

    public function delete_node($node_id = 0){
        $res = false;
        $node_id = intval($node_id);
        if($node_id){
            $res = $this->db->table($this->tbl_node)->where(" id = {$node_id} ")->delete();
        }
        return $res;
    }

    public function get_module_nodes(){
        return $this->db->table($this->tbl_node)->where(" level = 1 AND status = 1 ")->select();
    }
}