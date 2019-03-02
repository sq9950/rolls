<?php
/**
 * 用户缓存KEY模型
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015-11-13 18:03:04
 */

namespace Model;
class UserCacheKeys extends \Model\CommonModel{

    public function __construct(){
        parent::__construct();
        $this->table_name = 'user_cache_keys';
    }

}

