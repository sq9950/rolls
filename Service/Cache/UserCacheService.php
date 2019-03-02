<?php
/**
 * Desc: 缓存管理服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015-10-9 16:22:56
 */

namespace Service\Cache;

class UserCacheService extends \Service\Service{

    public $ret;
    public $memcache;
    public $userService;
    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
        $this->userService = new \Service\Common\UserService();
    }

    public function initModels(){
        $this->models = new \stdClass();
        $this->models->user_cache_keys  = new \Model\UserCacheKeys();
    }

    public function getUserCacheList($params = array()){
        $result = array(
            'count'     =>  0,
            'data'      => array()
        );
        extract($params);
        empty($where) && $where = array();
        if(isset($where['`group_name`'])){
            $group_name = trim($where['`group_name`'][1], '%');
            if('all' != $group_name && !is_numeric($group_name)){
                $user_info = $this->userService->getUserInfoByWhere(array('account' => $group_name));
                !empty($user_info) && $where['`group_name`'] = $user_info['id'];
            }

        }
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 20;
        empty($order) && $order = array();
        $result['count'] = $this->models->user_cache_keys->getListCountByWhere($where);
        $data = $this->models->user_cache_keys->getListByWhere($where, $offset, $limit, $order);
        foreach((array)$data as $key => $val){
            is_numeric($val['group_name']) && $user_ids[] = $val['group_name'];
            $cache_value = $this->cache_memcached->get($val['cache_key']);
            $data[$key]['cache_value'] = empty($cache_value) ? false : print_r($cache_value, 1);
        }
        if(!empty($user_ids)){
            $user_list = $this->userService->getUserListByWhere(array('id' => array('in', $user_ids)), '', true);
            foreach((array)$data as $key => $val){
                is_numeric($val['group_name']) && $data[$key]['user_name'] = $user_list[$val['group_name']]['account'];
            }
        }

        $result['data'] = $data;
        return $result;
    }

    public function clearUserCache($params = array()){
        if(!empty($params['alias_id'])){
            $where['id'] = $params['alias_id'];
            $info = $this->models->user_cache_keys->getOneByWhere($where);
            $res = $this->cache_memcached->delete($info['cache_key']);
            if($res){
                $this->models->user_cache_keys->updateByWhere($where, array('update_time' => date('Y-m-d H:i:s', time())));
                $this->ret = array('status' => 1, 'info' => '更新缓存成功');
            }else{
                $this->ret = array('status' => 1, 'info' => '更新缓存失败');
            }
        }else{
            $this->ret = array('status' => 0, 'info' => '未选择更新项');
        }
        return $this->ret;
    }
    public function clearUserCacheByGroupName($group_name = ''){
        if(!empty($group_name)){
            $where['group_name'] = $group_name;
            $clear_errors = [];
            $list = $this->models->user_cache_keys->getListByWhere($where);
            foreach($list as $value){
                $res = $this->cache_memcached->delete($value['cache_key']);
                !$res && array_push($clear_errors, $value['cache_key']);
            }
            if(empty($clear_errors)){
                $this->models->user_cache_keys->updateByWhere($where, array('update_time' => date('Y-m-d H:i:s', time())));
                $this->ret = array('status' => 1, 'info' => '更新缓存成功');
            }else{
                $this->ret = array('status' => 0, 'info' => '更新缓存失败', 'data' => $clear_errors);
            }
        }else{
            $this->ret = array('status' => 0, 'info' => '未选择更新项');
        }
        return $this->ret;
    }

    public function batchClearUserCache($params = array()){
        if(is_array($params['alias_ids']) && !empty($params['alias_ids'])){
            $where['id'] = array('in', $params['alias_ids']);
            $list = $this->models->user_cache_keys->getListByWhere($where);
			$delete_ok_ids = array();
            foreach((array)$list  as $val){
                $res = $this->cache_memcached->delete($val['cache_key']);
                $res && $delete_ok_ids[] = $val['id'];
            }
            $diff_ids = array_diff($params['alias_ids'], $delete_ok_ids);
            !empty($delete_ok_ids) && $this->models->user_cache_keys->updateByWhere(
                array('id' => array('in', $delete_ok_ids)),
                array('update_time' => date('Y-m-d H:i:s', time()))
            );
            if(!empty($diff_ids)){
                $this->ret = array('status' => 0, 'info' => '更新缓存失败的ID列表：'. implode(',', $diff_ids));
            }else{
                $this->ret = array('status' => 1, 'info' => '批量更新缓存成功');
            }
        }else{
            $this->ret = array('status' => 0, 'info' => '未选择更新项');
        }
        return $this->ret;
    }

}