<?php
/**
 *
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/6/9
 * Time: 16:26
 */

namespace Model;
class MemberDomainNs extends \Model\CommonModel{

    public function __construct(){
        parent::__construct();
        $this->table_name = 'member_domain_ns';
    }


}

