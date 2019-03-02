<?php
/**
 * Desc: 功能描述
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/9/10 17:49
 */
namespace Library\Util;
class IpCheck {
    public function __construct(){

    }

    /**
     * 验证IP段是否合法
     * @param string $ip_range
     * @return bool
     */
    static public function checkVaildIpRange($ip_range = ''){
        $valid = false;
        if(!empty($ip_range) && is_string($ip_range)){
            $ip_arr = explode('-', $ip_range);
            if(validIpv4($ip_arr[0])){
                if(isset($ip_arr[1])){
                    $ip_pres = explode('.', $ip_arr[0]);
                    $max = intval($ip_arr[1]);
                    if($max <= 255 && $max > array_pop($ip_pres)){
                        $valid = true;
                    }
                }else{
                    $valid = true;
                }
            }

        }
        return $valid;
    }

    /**
     * 获取解析后的IP段：
     *  如，1.1.1.1-3，返回
     *  array(
     *      1.1.1.1,
     *      1.1.1.2,
     *      1.1.1.3,
     *  );
     * @param string $ip_range
     * @return array
     */
    static function getExplodeIpRange($ip_range = ''){
        $explode_ips = array();
        $valid_ip_range = self::checkVaildIpRange($ip_range);
        if($valid_ip_range){
            $ip_arr = explode('-', $ip_range);
            array_push($explode_ips, $ip_arr[0]);
            $ip_pres = explode('.', $ip_arr[0]);
            $begin  = array_pop($ip_pres);
            $end    = $ip_arr[1];
            while($begin < $end){
                array_push($ip_pres, ++$begin);
                $new_ip = implode('.', $ip_pres);
                array_push($explode_ips, $new_ip);
                array_pop($ip_pres);
            }
        }
        return $explode_ips;
    }
}