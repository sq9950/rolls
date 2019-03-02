<?php
/**
 * Desc: 缓存管理服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015-10-9 16:22:56
 */

namespace Service\Cache;

class RbacCacheService extends \Service\Service{

    CONST RBAC_CACHE_PRE = 'adminv3rbac';      //admin-v3的缓存前缀
    CONST SEPARATOR  = '@';                     //键key分割符
    CONST MAX_KEY_LENGTH = 250;                 //memcache缓存key最大长度为250
    CONST TIMEOUT       =   1;               //缓存过期时间
    public $ret;
    public $memcache;
    CONST GROUP_NAME_ALL = 'all';
    public $group_name = 'all';
    public $memcache_key_prefix = '';
    public function __construct($group_name = ''){
        parent::__construct();
        !empty($group_name) && $this->group_name = $group_name;
        $this->memcache_key_prefix = self::RBAC_CACHE_PRE . self::SEPARATOR . $this->group_name;
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }

    public function initModels(){
        $this->models = new \stdClass();
        $this->models->user_cache_keys  = new \Model\UserCacheKeys();
        $this->models->role_user   = new \Model\RoleUser();
    }

    /**
     * 新增指定操作的缓存
     * @param $cache_key
     * @param $cache_value
     * @param $method_name
     * @return array
     */
    public function addCacheByMethod($cache_key, $cache_value, $method_name){
        if(is_array($cache_key)){
            array_push($cache_key, $method_name);
        }else{
            $cache_key = array($cache_key, $method_name);
        }
        $res = $this->addCache($cache_key, $cache_value);
        return $res;
    }

    /**
     * 查询指定操作的缓存
     * @param $cache_key
     * @param $method_name
     * @return bool
     */
    public function getCacheByMethod($cache_key, $method_name){
        if(is_array($cache_key)){
            array_push($cache_key, $method_name);
        }else{
            $cache_key = array($cache_key, $method_name);
        }
        return $this->getCache($cache_key);
    }

    /**
     * 生成指定规则的缓存KEY
     * @param $suffix_key
     * @return string
     */
    private function buildRbacCacheKey($suffix_key){
        if(is_array($suffix_key)){
            $suffix_key = implode(self::SEPARATOR, $suffix_key);
        }
        $suffix_key = self::SEPARATOR.$suffix_key;
        if(strlen($suffix_key) + strlen($this->memcache_key_prefix) > self::MAX_KEY_LENGTH){
            $memcache_key = $this->memcache_key_prefix.md5($suffix_key);
        }else{
            $memcache_key = $this->memcache_key_prefix.$suffix_key;
        }
        return $memcache_key;
    }


    /**
     * 查询缓存
     * @param string $cache_key
     * @return bool
     */
    protected function getCache($cache_key = ''){
        if(empty($cache_key) || !is_object($this->cache_memcached)){
            return false;
        }
        $cache_key = $this->buildRbacCacheKey($cache_key);

        return $this->cache_memcached->get($cache_key);
    }

    /**
     * 新增缓存
     * @param $cache_key
     * @param $cache_value
     * @return array
     */
    public function addCache($cache_key, $cache_value){
        if(!is_object($this->cache_memcached)){
            return array('status' => 0, 'info' => '缓存对象不存在');
        }
        if(empty($cache_key)){
            return array('status' => 0, 'info' => '缓存cache_key不存在');
        }
        if(empty($cache_value)){
            return array('status' => 0, 'info' => '缓存cache_value不存在');
        }

        $cache_key = $this->buildRbacCacheKey($cache_key);
        $res = $this->cache_memcached->save($cache_key, $cache_value, self::TIMEOUT);
        if($res){
            $data = array(
                'group_name' => $this->group_name,
                'cache_key' => $cache_key,
                'create_time' => date('Y-m-d H:i:s', time())
            );
            $where = array('cache_key' => $cache_key, 'type' => 'memcache');
            $count = $this->models->user_cache_keys->getListCountByWhere($where);
            if(!$count){
                $this->models->user_cache_keys->add($data);
            }
            $this->ret = array('status' => 1, 'info' => '新增缓存管理成功');
        }else{
            $this->ret = array('status' => 0, 'info' => '新增缓存管理失败');
        }
        return $this->ret;
    }


    /**
     * 更新缓存
     * @param $cache_key
     * @param $cache_value
     * @return array
     */
    protected function updateCache($cache_key, $cache_value){

    }

    /**
     * 删除缓存
     * @param string $cache_key
     * @return array
     */
    protected function deleteCache($cache_key = ''){
        if(!is_object($this->cache_memcached)){
            return array('status' => 0, 'info' => '缓存对象不存在');
        }
        if(empty($cache_key)){
            return array('status' => 0, 'info' => '缓存cache_key不存在');
        }
        $res = $this->cache_memcached->delete($cache_key);
        if($res){
            $this->ret = array('status' => 1, 'info' => '删除缓存成功');
        }else{
            $this->ret = array('status' => 0, 'info' => '删除缓存失败');
        }
        return $this->ret;
    }

    /**
     * 查询分组键名是否存在
     * @param string $cache_key
     * @return bool
     */
    protected function checkCacheExisted($cache_key = ''){
        $existed = false;
        if($cache_key && is_object($this->cache_memcached)){
            $cache_value = $this->cache_memcached->get($cache_key);
            false !== $cache_value && $existed = true;
        }
        return $existed;
    }

    public function clearUserCacheByRoleId($role_id = 0){
        if($role_id){
            $where['role_id'] = $role_id;
            $list = $this->models->role_user->getListByWhere($where);
            foreach((array)$list as $val){
                $user_ids[] = $val['user_id'];
            }
            if(!empty($user_ids)){
                array_push($user_ids, self::GROUP_NAME_ALL);
                $where = array('group_name' => array('in', $user_ids));
                $key_list = $this->models->user_cache_keys->getListByWhere($where);
                foreach($key_list as $val){
                    $ids[] = $val['id'];
                    $res = $this->deleteCache($val['cache_key']);
                }
//                $where = array('id' => array('in', $ids));
//                $res = $this->models->user_cache_keys->deleteByWhere($where);
                if($res){
                    $this->ret = array('status' => 1 ,'info' => '清除用户缓存成功');
                }else{
                    $this->ret = array('status' => 0 ,'info' => '清除用户缓存失败');
                }
            }
        }
        return $this->ret;
    }


    public function clearGetUserLevel2ListCache($user_id, $node_id){

        $cache_key = $this->buildRbacCacheKey([$user_id, $node_id , 'GetUserLevel2List']);
        $this->deleteCache($cache_key);
    }

}