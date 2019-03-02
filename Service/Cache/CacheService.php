<?php
/**
 * Desc: 缓存管理服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015-10-9 16:22:56
 */

namespace Service\Cache;

use Model\AdminCache;
use Model\AdminSettingGroup;
use Service\Service;

class CacheService extends Service
{
    CONST TOP_DOMAIN_SUFFIX_GROUPKEY     = 'topLevelDomainSufix';       //顶级域名缓存KEY配置分组名
    CONST SENCOND_DOMAIN_SUFFIX_GROUPKEY = 'twoLevelDomainSufix';   //二级域名缓存KEY配置分组名
    CONST DOMAINSUFFIX_CACHE_KEY         = 'domainSuffix-cache-key';        //域名后缀memcache缓存KEY
    public $ret;
    public $status_switch = [
        'on'  => 1,
        'off' => 0
    ];

    private $mdlAdminCache;
    private $mdlAdminSettingGroup;

    public function __construct()
    {
        parent::__construct();
        $this->initModels();

        $this->mdlAdminCache        = new AdminCache();
        $this->mdlAdminSettingGroup = new AdminSettingGroup();
    }

    public function initModels()
    {
    }

    /**
     * @param array $params
     * @return array
     * @node_name 获取缓存列表
     * @link
     * @desc
     */
    public function getCacheList($params = [])
    {
        $result = [
            'count' => 0,
            'data'  => []
        ];
        extract($params);
        empty($where) && $where = [];
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 20;
        empty($order) && $order = [];

        $result['count'] = $this->mdlAdminCache->getListCountByWhere($where);
        $cache_list      = $this->mdlAdminCache->getListByWhere($where, $offset, $limit, $order);
        foreach ((array)$cache_list as $key => $val) {
            $cache_value = $this->cache_memcached->get($val['cache_key']);
            if (!is_null(json_decode($cache_value))) {
                $arr                             = jsonToArray($cache_value);
                $cache_list[$key]['cache_value'] = json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                $cache_list[$key]['cache_value'] = $cache_value ? print_r($cache_value, 1) : $cache_value;
            }
        }
        $result['data'] = $cache_list;

        return $result;
    }


    /**
     * 新增缓存管理
     * @param array $params
     * @return array
     */
    public function addCache($params = [])
    {
        if (!isset($params['cache_key']) || empty($params['cache_key'])) {
            return ['status' => 0, 'info' => '缓存KEY不能为空'];
        }

        if ($this->checkCacheKeyExisted($params['cache_key'])) {
            return ['status' => 0, 'info' => '当前缓存KEY已经存在'];
        }

        if (isset($params['cache_value']) && !empty($params['cache_value'])) {
            $this->cache_memcached->save($params['cache_key'], $params['cache_value']);
        }
        if (!isset($params['status']) || !isset($this->status_switch[$params['status']])) {
            $data['status'] = $this->status_switch['off'];
        } else {
            $data['status'] = $this->status_switch[$params['status']];
        }
        $data['cache_key'] = trim($params['cache_key']);
        !empty($params['description']) && $data['description'] = $params['description'];
        $data['created_time'] = $data['updated_time'] = date('Y-m-d H:i:s', time());
        $res                  = $this->mdlAdminCache->add($data);
        if ($res) {
            $this->ret = ['status' => 1, 'info' => '新增缓存管理成功'];
        } else {
            $this->ret = ['status' => 0, 'info' => '新增缓存管理失败'];
        }
        return $this->ret;
    }

    /**
     * 编辑缓存管理
     * @param array $params
     * @return array
     */
    public function editCache($params = [])
    {
        if (!isset($params['id']) || empty($params['id'])) {
            return ['status' => 0, 'info' => '未指定缓存管理ID'];
        }
        if (!isset($params['cache_key']) || empty($params['cache_key'])) {
            return ['status' => 0, 'info' => '缓存KEY不能为空'];
        } elseif ($this->checkCacheKeyExisted($params['cache_key'], ['id' => ['not in', $params['id']]])) {
            return ['status' => 0, 'info' => '当前缓存KEY已经存在!'];
        }
        $cache_value = $this->cache_memcached->get($params['cache_key']);
        $cache_value && $cache_value = json_encode($cache_value);
        if (isset($params['cache_value']) && $cache_value != $params['cache_value']) {
            $this->cache_memcached->save($params['cache_key'], $params['cache_value']);
        }
        if (!isset($params['status']) || !isset($this->status_switch[$params['status']])) {
            $data['status'] = $this->status_switch['off'];
        } else {
            $data['status'] = $this->status_switch[$params['status']];
        }
        $data['cache_key'] = trim($params['cache_key']);
        !empty($params['description']) && $data['description'] = $params['description'];

        $res = $this->mdlAdminCache->updateById($params['id'], $data);
        if ($res) {
            $this->ret = ['status' => 1, 'info' => '更新缓存管理成功'];
        } else {
            $this->ret = ['status' => 0, 'info' => '更新缓存管理失败'];
        }
        return $this->ret;
    }

    /**
     * 更新缓存
     * @param array $params
     * @return array
     */
    public function updateCache($params = [])
    {
        if (!isset($params['id']) || empty($params['id'])) {
            return ['status' => 0, 'info' => '未指定缓存管理ID'];
        }
        $where      = array('id' => $params['id']);
        $cache_info = $this->getCacheInfoByWhere($where);
        if (empty($cache_info) || empty($cache_info['cache_key'])) {
            return ['status' => 0, 'info' => '缓存记录不存在'];
        }
        $res = $this->cache_memcached->delete($cache_info['cache_key']);

        $data['cache_update_time'] = date('Y-m-d H:i:s', time());
        $this->mdlAdminCache->updateById($params['id'], $data);
        if ($res) {
            $this->ret = array('status' => 1, 'info' => '更新缓存成功');
        } else {
            $this->ret = array('status' => 0, 'info' => '更新缓存失败');
        }
        return $this->ret;
    }

    /**
     * @param int $cache_id
     * @return array
     * @node_name 通过id删除cache
     * @link
     * @desc
     */
    public function deleteCacheById($cache_id = 0)
    {
        if ($cache_id) {
            $where = ['id' => $cache_id];
            $res   = $this->mdlAdminCache->deleteByWhere($where);
            if ($res) {
                $this->ret = ['status' => 1, 'info' => '删除缓存管理成功'];
            } else {
                $this->ret = ['status' => 0, 'info' => '删除缓存管理失败'];
            }
        } else {
            $this->ret = ['status' => 0, 'info' => '未指定删除ID'];
        }
        return $this->ret;
    }

    /**
     * 查询分组键名是否存在
     * @param string $cache_key
     * @param array $where
     * @return bool
     */
    private function checkCacheKeyExisted($cache_key = '', $where = [])
    {
        $existed = false;
        if ($cache_key) {
            $where['cache_key'] = trim($cache_key);
            $count              = $this->mdlAdminCache->getListCountByWhere($where);
            $count && $existed = true;
        }
        return $existed;
    }

    public function getCacheInfoByWhere($where = [])
    {
        return $this->mdlAdminCache->getOneByWhere($where);
    }

    public function clearDomainSufixCacheByGroupId($group_id = 0)
    {
        if ($group_id) {
            $where['id'] = $group_id;
            $group_info  = $this->mdlAdminSettingGroup->getOneByWhere($where);
            if (isset($group_info['groupkey'])
                && in_array(
                    $group_info['groupkey'],
                    [self::TOP_DOMAIN_SUFFIX_GROUPKEY, self::SENCOND_DOMAIN_SUFFIX_GROUPKEY]
                )
            ) {
                $this->cache_memcached->delete(self::DOMAINSUFFIX_CACHE_KEY);
            }
        }
    }


    public function clearCacheByKey($key){
        $res = $this->cache_memcached->delete($key);
        if ($res) {
            $this->ret = array('status' => 1, 'info' => '清除缓存成功');
        } else {
            $this->ret = array('status' => 0, 'info' => '清除缓存失败');
        }

        return $this->ret;
    }

}
