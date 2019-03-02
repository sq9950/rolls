<?php
namespace Model;
class Node extends \Model\CommonModel{
	public function __construct(){
        $this->table_name = 'rbac_node';
		parent::__construct();
	}


    public function get_action_node_names( $pid = 0 ){

        $where['pid'] = $pid;
        $where['status'] = 1;
        return $this->getListByWhere($where);
    }

    public function get_action_node_list( $pid = 0 ){
        $node_list = array();
        $where['pid'] = $pid;
        $where['status'] = 1;
        $nodes = $this->getListByWhere($where);
        if(is_array($nodes) && !empty($nodes)){
            foreach($nodes as $node){
                $node_list[$node['id']] = $node;
            }
        }
        return $node_list;
    }

    public function get_node_info($id = 0){
        $id = intval($id);
        $where['id'] = $id;
        return $this->getOneByWhere($where);
    }

    public function update_node_by_id($node_id = 0, $data = array()){
        $res = false;
        $node_id = intval($node_id);
        if($node_id && is_array($data) && !empty($data)){
            if(isset($data['id'])){
                unset($data['id']);
            }
            $res = $this->updateById($node_id, $data);
        }
        return $res;
    }

    public function add_node($data = array()){
        $res = false;
        if(is_array($data) && !empty($data)){
            $res = $this->add($data);
        }
        return $res;
    }

    public function delete_node($node_id = 0){
        $res = false;
        $node_id = intval($node_id);
        if($node_id){
            $where['id'] = $node_id;
            $res = $this->deleteByWhere($where);
        }
        return $res;
    }

    public function get_module_nodes(){
        $where['level'] = 1;
        $where['status'] = 1;
        return $this->getListByWhere($where);
    }
}