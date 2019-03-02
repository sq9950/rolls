<?php
namespace Model;
class User extends \Model\CommonModel{

	public function __construct(){
		parent::__construct();
        $this->table_name = 'user';
	}


    public function get_user_list($params = array()){
        $users = array();
        $current_page = $params['p'] ? $params['p'] : 1;
        $page_size = $params['pageSize'] ? $params['pageSize'] : 10;
        $offset = ($current_page - 1) * $page_size;
        $limit = $page_size;

        if(isset($params['user_name']) && $params['user_name']){
            $sql_where = " account like '%{$params['user_name']}%'";
            $list = $this->getListByWhere($sql_where, $offset, $limit);
        }else{
            $list = $this->getListByWhere('', $offset, $limit);
        }

        if(is_array($list) && !empty($list)){
            foreach($list as $val){
                $users[$val['id']] = $val;
            }
        }
        return $users;
    }

    public function get_user_total($params = array()){
        $sql_where = isset($params['user_name']) && $params['user_name'] ? " account like '%{$params['user_name']}%'" : '';
        return $this->getListCountByWhere($sql_where);
    }

    public function getMemberListByIds($user_ids = array()){
        $members = array();
        if(is_array($user_ids) && !empty($user_ids)){
            $where['id'] = array('in', $user_ids);
            $list = $this->getListByWhere($where);
            if(is_array($list) && !empty($list)){
                foreach($list as $val){
                    $members[$val['id']] = $val;
                }
            }
        }
        return $members;
    }

    /**
     * 查询单个后台账户的账户信息，过滤密码
     * @param int $user_id
     * @return array|mixed
     */
    public function getUserInfoById($user_id = 0){
        $user_info = array();
        $user_id = intval($user_id);
        if($user_id){
            $where['id'] = $user_id;
            $list = $this->getListByWhere($where);
            $user_info = array_shift($list);
        }
        return $user_info;
    }

    /**
     * 过滤后台用户中的密码字段
     * @param array $where
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @param array $fields
     * @param string $group
     * @param string $sortKey
     * @return mixed
     */
    public function getListByWhere($where = array(), $offset = 0, $limit = 500, $order = array(), $fields = array(), $group = '', $sortKey = ''){
        $list = parent::getListByWhere($where, $offset, $limit, $order, $fields, $group, $sortKey);
        foreach((array)$list as $key => $val){
            unset($list[$key]['password']);
        }
        return $list;
    }

    /**
     * 获取用户信息，过滤密码
     * @param array $where
     * @param string $field
     * @param string $group
     * @param bool|true $no_passwd
     * @return mixed
     */
    public function getOneByWhere($where = array(), $no_passwd = true, $field =  '', $group = ''){
        $user_info = parent::getOneByWhere($where, $field, $group);
        if($no_passwd){
            unset($user_info['password']);
        }
        return $user_info;
    }

}