<?php
/**
 * Desc: 功能描述
 * Created by PhpStorm.
 * User: <duyifan@yundun.com>
 * Date: 2017/12/1 12:04
 */

namespace Service\Cache;

use Service\Service;
//use Service\Redis\RedisService;
use Service\Redis\HttpRedisService;

class ConfRedisService extends Service {
    protected $httpRedisService;
    const DEFAULT_DB = 0;
    public function __construct() {
        parent::__construct();
        $this->initModels();
//        RedisService::setSentinel('redis_sentinel1');
//        RedisService::setSentinel('redis_sentinel2');
//        RedisService::setDb(0);
//        RedisService::setConfig($this->configall);
//        RedisService::setRedisMaster('redis_Master');
//        RedisService::getInstance();
        $this->httpRedisService = new HttpRedisService(self::DEFAULT_DB,$this->configall['REDIS_HTTP_HOST']);
    }

    public function initModels() {
    }

//    public function initRedis($db = 0) {
//        RedisService::setSentinel('redis_sentinel1');
//        RedisService::setSentinel('redis_sentinel2');
//        RedisService::setDb($db);
//        RedisService::setConfig($this->configall);
//        RedisService::setRedisMaster('redis_Master');
//        RedisService::getInstance();
//    }

    public function getCacheList($params = []) {
        $result = array(
            'count' => 0,
            'data'  => array()
        );
        extract($params);
        empty($where) && $where = array();
        if (isset($where['`db_type`'])) {
            $this->httpRedisService->setDb((int)$where['`db_type`']);
//            RedisService::selectDb($where['`db_type`']);;
        } else {
            $this->httpRedisService->setDb(0);
//            RedisService::setDb(0);
        }
        if (isset($where['`cache_key`'])) {
            $cache_key  = mb_substr($where['`cache_key`'][1], 1, -1);
            $pattern    = "*" . $cache_key . "*";
            $list       = $this->httpRedisService->scan($pattern);
            $value_list = $this->httpRedisService->mGet(array_values($list));
            $tmp_list = [];
            if ($value_list) {
                foreach ($value_list as $k => $v) {
                    $value = $v ? json_decode($v, 1) : [];
                    if (isset($value['ssl']) && isset($value['ssl']['cert'])) {
                        $value['ssl']['cert'] = '已隐藏';
                    }
                    if (isset($value['ssl']) && isset($value['ssl']['key'])) {
                        $value['ssl']['key'] = '已隐藏';
                    }
                    if (isset($value['custom_errorpage']) && isset($value['custom_errorpage']['global_page'])) {
                        $value['custom_errorpage']['global_page'] = htmlspecialchars($value['custom_errorpage']['global_page']);
                    }
                    if (isset($value['page500']) && isset($value['page500']['content'])) {
                        $value['page500']['content'] = htmlspecialchars($value['page500']['content']);
                    }
                    if (isset($value['page404']) && isset($value['page404']['content'])) {
                        $value['page404']['content'] = htmlspecialchars($value['page404']['content']);
                    }
                    if (isset($value['page502']) && isset($value['page502']['content'])) {
                        $value['page502']['content'] = htmlspecialchars($value['page502']['content']);
                    }
                    if (isset($value['customer_Intercept']) && isset($value['customer_Intercept']['intercept_page'])) {
                        $value['customer_Intercept']['intercept_page'] = htmlspecialchars($value['customer_Intercept']['intercept_page']);
                    }
                    $value_list[$k] = json_encode($value);
                }
                foreach ($list as $k => $v) {
                    $tmp_list[$k]['cache_key']   = $v;
                    $tmp_list[$k]['cache_value'] = $value_list[$k];
                }
                $result['data'] = $tmp_list;
            } else {
                return $result;
            }
        }
        return $result;
    }

    public function getCacheInfo() {

    }
}