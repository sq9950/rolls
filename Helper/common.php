<?php
/**
 * @param $data
 * @param $type dump|export|print|echo
 * @param $exit true exit otherwise no exit
 */
if (!function_exists('dump')) {
    function dump($data, $type = '', $exit = true, $header = true)
    {
        if ($header) {
            header('Content-Type:text/html; charset=utf-8');
        }

        echo '<pre>';
        switch (strtolower($type)) {
            case '':
            case 'dump':
                var_dump($data);
                break;
            case 'export':
                var_export($data);
                break;
            case 'print':
                print_r($data);
                break;
            case 'echo':
                echo $data;
                break;
            default:
                var_dump($data);
                break;
        }
        if ($exit) {
            exit;
        }

    }
}

/**
 * cli环境 返回\n 否则返回<br/>
 */
function nOrBr()
{
    return strtolower(php_sapi_name()) == 'cli' ? "\n" : '<br/>';
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
if (!function_exists('redirect')) {
    function redirect($url, $time = 0, $msg = '')
    {
        header('Content-Type:text/html; charset=utf-8');
        //多行URL地址支持
        $url = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg)) {
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        }

        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0) {
                $str .= $msg;
            }

            exit($str);
        }
    }
}

function send_http_status($code)
{
    static $_status = array(
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
    if (isset($_status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $_status[$code]);
    }
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
if (!function_exists('is_ssl')) {
    function is_ssl()
    {
        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }
}

/*获取客户端ip*/
if (!function_exists('get_real_ip')) {
    function get_real_ip()
    {
        $ip = false;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match('/^(?:10|172\.(?:1[6-9]|2\d|3[01])|192\.168)\./', $ips[$i])) {
                    if (ip2long($ips[$i]) != false) {
                        $ip = $ips[$i];
                        break;
                    }
                }
            }
        }
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

}

if (!function_exists('getCurrentUrl')) {
    function getCurrentUrl()
    {
        $http = is_ssl() ? 'https://' : 'http://';
        return $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

if (!function_exists('getHost')) {
    function getHost()
    {
        $http = is_ssl() ? 'https://' : 'http://';
        return $http . $_SERVER['HTTP_HOST'];
    }
}

//检测email
function validEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

//检测手机
function validMobile($mobile)
{
    return preg_match('/^0?1[3456789]\d{9}$/i', $mobile);
}

//检测手机
function validAccount($account)
{
    return preg_match('/^[A-Z0-9_-]+$/i', $account);
}

//检测是否域名
function validDomain($domain, $checkPort = false)
{
    return preg_match('/^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?$/i', $domain);
}

//检测端口
function checkPort($port)
{
    if (!is_numeric($port) || $port < 0 || $port >= 65535 || strlen($port) > 1 && (substr($port, 0, 1) == '0')) {
        return false;
    }
    return true;
}

//检测是否符合ipv4格式，$checkPort是否检测端口
function validIpv4($ip_addr, $checkPort = false)
{
    if ($checkPort) {
        if ($pos = strpos($ip_addr, ':')) {
            list($ip_addr, $port) = explode(':', $ip_addr, 2);
            if (!is_numeric($port) || $port < 0 || $port >= 65535 || strlen($port) > 1 && (substr($port, 0, 1) == '0')) {
                return false;
            }

        }
    }

    return filter_var($ip_addr, FILTER_VALIDATE_IP);

}

//ipv6
function validIpv6($ip_addr)
{
    return filter_var($ip_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}

//私有IP 127的只匹配127.0.0.1
function isPrivateIP($ip, $checkPort = true)
{
    if ($checkPort) {
        if ($pos = strpos($ip, ':')) {
            list($ip, $port) = explode(':', $ip, 2);
        }
    }
    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

//私有Ip实现2 $valid_127 是否验证127开头的
function isLanIp($ip, $valid_127 = true, $checkPort = true)
{
    if ($checkPort) {
        if ($pos = strpos($ip, ':')) {
            list($ip, $port) = explode(':', $ip, 2);
        }
    }
    $ip2l  = ip2long(trim($ip));
    $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
    $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
    $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址

    return $ip2l >> 24 === $net_a || $ip2l >> 20 === $net_b || $ip2l >> 16 === $net_c || ($valid_127 && substr(trim($ip), 0, 3) == '127');
}

//验证URL
function isUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL);
}

//获取域名主域名
function getPrimaryDomain($domain)
{
    if (strpos($domain, ':')) {
        $domain = strstr(trim($domain), ':', true);
    }

    $domains = explode('.', strtolower($domain));
    if (count($domains) > 2) {
        $exts = implode('.', array_slice($domains, -2));
        if (in_array($exts, array('ac.cn', 'com.cn', 'com.au', 'com.sg', 'net.cn', 'gov.cn', 'org.cn', 'edu.cn', 'com.hk', 'net.hk', 'net.in', 'co.uk'))) {
            return $domains[count($domains) - 3] . '.' . $exts;
        }
    }
    $ext = array_pop($domains);

    if (in_array($ext, array('ac', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'ao', 'aq', 'ar', 'as', 'asia', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'biz', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bw', 'by', 'bz', 'ca', 'cc', 'cd' /*, 'cf'*/, 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'cn', 'co', 'com', 'cr', 'cv', 'cw', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'edu', 'ee', 'eg', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr' /*, 'ga'*/, 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gov', 'gp', 'gr', 'gs', 'gt', 'gu', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'info', 'io', 'iq', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'kn', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mg', 'mk' /*, 'ml'*/, 'mm', 'mn', 'mo', 'mobi', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'name', 'nc', 'ne', 'net', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'org', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'se', 'sg', 'sh', 'si', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'st', 'su', 'sv', 'sx', 'sz', 'tc', 'td', 'tg', 'th', 'tj', /*'tk',*/
        'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'us', 'uy', 'uz', 'vc', 've', 'vg', 'vi', 'vn', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'za', 'zm', 'zw', 'xyz'))) {
        return array_pop($domains) . '.' . $ext;
    }
    return false;
}

//yundun_dns records table name field
function makeCnameRecordsName($pridomain, $subdomain)
{
    return substr(md5($subdomain), 1, 8) . '.' . $pridomain . '.cname';
}

//make member_domain_cname subdomain cname value
function makeSubdomainCnameValue($pridomain, $subdomain, $diyCnameDomain = 'jsd.cc')
{
    return substr(md5($subdomain), 1, 8) . '.' . $pridomain . '.cname.' . trim($diyCnameDomain) . '.';
}

//get Cname domain
function getCnameDomain($pridomain, $subdomain, $cname)
{
    $name = makeCnameRecordsName($pridomain, $subdomain);
    return substr($cname, strlen($name) + 1, -1);
}

/**
 * @param $subdomain
 * @return bool|string
 * @node_name cname_name
 * @link
 * @desc
 */
function makeCnameName($subdomain)
{
    return substr(md5($subdomain), 1, 8);
}

//获取yundun_dns records表name field $cname => xx.yy.com.jsd.cc
function getCnameRecordName($cname, $diyCnameDomain = 'jsd.cc')
{
    return str_replace('.' . trim($diyCnameDomain) . '.', '', $cname);
}

//ns subdomain
function getNsSubdomain($domain, $sub)
{
    return $sub . '.' . $domain;
}

//cname subdomain
function getCnameSubDomain($domain, $sub)
{
    return $sub . '.' . $domain;
}

//get short sub
function getShortSub($domain, $subfull)
{
    return str_replace('.' . $domain, '', $subfull);
}

/*
 * @二维数组排序
 *
 */
function array_sort($arr, $key, $order = "desc")
{
    $keyvalue = $new_arr = array();
    foreach ($arr as $k => $v) {
        $keyvalue[$k] = $v[$key];
    }
    switch ($order) {
        case "desc":
            arsort($keyvalue);
            break;
        default:
            asort($keyvalue);
            break;
    }
    foreach ($keyvalue as $k => $v) {
        $new_arr[$k] = $arr[$k];
    }
    return array_values($new_arr);
}

//二维数组去重
function assoc_unique($arr, $key)
{
    $tmp_arr = array();
    foreach ($arr as $k => $v) {
        if (in_array($v[$key], $tmp_arr)) {
            unset($arr[$k]);
        } else {
            $tmp_arr[] = $v[$key];
        }
    }
    sort($arr);
    return $arr;
}

//make random string  No 0's or O's
function generateRandomString($length, $allowedCharacters = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ')
{
    $maxIndex = strlen($allowedCharacters) - 1;
    $string   = '';
    for ($i = 1; $i <= $length; $i++) {
        $string .= $allowedCharacters[mt_rand(0, $maxIndex)];
    }
    return $string;
}

function generateRandomStringV2($length = 20)
{
    $ccid         = str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5));
    $random_start = mt_rand(0, (strlen($ccid) - $length));
    $code         = substr($ccid, $random_start, $length);
    return $code;
}

//31位
function generateSecret()
{
    return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
}

//32位
function genAccessToken()
{
    return md5(base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), uniqid())));
}

//生成app_id
function genAppId($length = 20)
{
    return generateRandomStringV2($length);
}

//生成app_secret
function genAppSecret($length = 32)
{
    return genAccessToken($length);
}

//生成盐化字符串
function genSalt($length = 14)
{
    return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, $length);
}

//yundun_dns db records  table value field  [4space    jsd.cc]
function makeRecordsValueByShareGroupId($id)
{
    return substr(md5($id), 0, 12) . "    jsd.cc";
}

function md5IPUserAgent()
{
    return md5(get_real_ip() . $_SERVER['HTTP_USER_AGENT']);
}

function md5ActiveCode($email, $check_time)
{
    return md5($email . $check_time);
}

function encryptMemberCheckCode($type, $data)
{
    switch ($type) {
        case 'getpwd': //找回密码邮件code
            return md5($data);
            break;
    }
}

//生成密码
function makeAccountPassWord($password, $salt)
{
    if (empty($password)) {
        return false;
    }

    //if(empty($salt)) $salt=genSalt(20);
    return md5(md5(md5($password) . $salt) . $salt);
}

/*
 *  获取发送Email的模板
 *  $search  需要替换的变量数组
 *  $replace 替换的内容数组
 *  $tplfile 指定的模板
 */
function replacemailtpl($search = array(), $replace = array(), $tplfile = "")
{
    header('Content-Type: text/html; charset=utf-8');
    $mailtplurl = __APP__ . '/Public/sendmailtpl/' . $tplfile;
    $str        = file_get_contents($mailtplurl);
    if (!$str) {
        return "";
    }

    $str_replace = str_replace($search, $replace, $str);
    return $str_replace;
}

/*
 *  获取发送Email的模板
 *  $search  需要替换的变量数组
 *  $replace 替换的内容数组
 *  $tplfile 指定的模板
 *  $autoload 自动加载模板头部底部
 */
function newReplacemailtpl($search = array(), $replace = array(), $tplfile = "", $autoload = true)
{
    header('Content-Type: text/html; charset=utf-8');
    $mail_header = $mail_footer = '';
    if ($autoload) {
        $mail_header_adr = __APP__ . '/Public/sendmailtpl/mail_header.html';
        $mail_footer_adr = __APP__ . '/Public/sendmailtpl/mail_footer.html';
        $mail_header     = file_get_contents($mail_header_adr);
        $mail_footer     = file_get_contents($mail_footer_adr);
    }
    $mailtplurl = __APP__ . '/Public/sendmailtpl/' . $tplfile;
    $str        = file_get_contents($mailtplurl);
    $str        = $mail_header . $str . $mail_footer;
    if (!$str) {
        return "";
    }

    $str_replace = str_replace($search, $replace, $str);
    return $str_replace;
}

//is homepage or not
if (!function_exists('isIndex')) {
    function isIndex()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if ($uri == '/') {
            return true;
        }
        return false;
    }
}

/**
 * one-dimensional arr to two diamensional
 * array('a', 'b') => array( array('k'=>'a'), array('k'=>'b'))
 */
if (!function_exists('switchOne2Two')) {
    function switchOne2Two($arr, $k)
    {
        $result = array();
        foreach ($arr as $v) {
            $result[] = array($k => $v);
        }
        return $result;
    }
}

/*
 *无限级分类显示(包含depth字段)
 */
function showClass($result, $pid = 0, $pidfield = 'parentid', $idfield = 'id')
{
    $arrClass = array();
    foreach ($result as $key => $v) {
        if ($pid == $v[$pidfield]) {
            $arrClass[] = $v;
            $arrClass   = array_merge($arrClass, showClass($result, $v[$idfield], $pidfield)); //合并数组
        }
    }
    return $arrClass;
}

function showClassForArticleCate($result, $pid = 0)
{
    $arrClass = array();
    foreach ($result as $key => $v) {
        if ($pid == $v['parentid']) {
            $arrClass[] = $v;
            $arrClass   = array_merge($arrClass, showClassForArticleCate($result, $v['cid'])); //合并数组
        }
    }
    return $arrClass;
}

/*
 *无限级分类下拉显示文章栏目(包含depth字段)
 */
function dropProClass($result, $pid = 0)
{
    $arrClass = array();
    foreach ($result as $key => $v) {
        if ($pid == $v['parentid']) {
            $str = '';
            if ($pid != 0) {
                for ($i = 0; $i < $v['depth']; $i++) {
                    $str = $str . '&nbsp;&nbsp;&nbsp;';
                }
                $str = $str . '└ ';
            }
            $arrClass[$v['cid']] = $str . $v['cname'];
            $arrClass            = $arrClass + dropProClass($result, $v['cid']); //合并数组
        }
    }
    return $arrClass;
}

//树形二维数组
function tree(&$list, $pid = 0, $level = 0, $html = '--')
{
    static $tree = array();
    foreach ($list as $v) {
        if ($v['parent_id'] == $pid) {
            $v['level'] = $level;
            $v['html']  = str_repeat($html, $level);
            $tree[]     = $v;
            tree($list, $v['id'], $level + 1);
        }
    }
    unset($list);
    return $tree;
}

function treeRemark(&$list, $pid = 0, $level = 0, $html = '&nbsp;&nbsp;&nbsp;&nbsp;')
{
    static $tree = array();
    foreach ($list as $v) {
        if ($v['parent_id'] == $pid) {
            $v['sort'] = $level;
            $f         = '';
            if ($pid != 0) {
                $f = '└';
            }
            $v['html'] = str_repeat($html, $level) . $f;
            $tree[]    = $v;
            treeRemark($list, $v['id'], $level + 1);
        }
    }
    return $tree;
}

//转化为树形数组
function arrToTree($data, $pid = 0, $pidfield = 'parent_id', $sonfield = 'son', $pri = 'id')
{
    $tree = array();
    foreach ($data as $k => $v) {
        if ($v[$pidfield] == $pid) {
            $v[$sonfield] = arrToTree($data, $v[$pri]);
            $tree[]       = $v;
        }
    }
    return $tree;
}

//输出树形数组jquery ui menu样式
function outputTree($tree, $pidfield = 'parent_id', $sonfield = 'son')
{
    if ($tree) {
        $html = '';
        foreach ($tree as $t) {
            if (empty($t[$sonfield])) {
                $html .= '<li data-name="' . $t['name'] . '">' . $t['desc'] . '</li>';
            } else {
                $html .= '<li data-name="' . $t['name'] . '">' . $t['desc'] . '<ul>';
                $html .= outputTree($t[$sonfield]);
                $html .= '</ul></li>';
            }
        }
        return $html;
    }
}

//输出树形菜单数组符合bootstrap样式
function outputTreeBoot($tree, $pidfield = 'parent_id', $sonfield = 'son')
{
    if ($tree) {
        $html = '';
        foreach ($tree as $t) {
            if (empty($t[$sonfield])) {
                $html .= '<li data-name="' . $t['name'] . '"><a tabindex>' . $t['desc'] . '</a></li>';
            } else {
                $html .= '<li data-name="' . $t['name'] . '" class="dropdown-submenu"><a data-toggle="dropdown" tabindex aria-expanded="false">' . $t['desc'] . '</a><ul class="dropdown-menu">';
                $html .= outputTreeBoot($t[$sonfield]);
                $html .= '</ul></li>';
            }
        }
        return $html;
    }
}

//生成订单号
function makeOrderCode($prefix)
{
    return $prefix . date('YmdHis') . mt_rand(1000, 9999);
}

//获取
function getSubDomainHead($domain, $subdomain)
{
    return str_replace($subdomain, '', '.' . $domain);
}

//格式化 where in 条件
function formatWhereIn($arr, $str = true)
{
    return $str ? "'" . implode("','", $arr) . "'" : implode(',', $arr);
}

/**
 * 数字格式化
 */
function formatNumCC($num, $precision = 1)
{
    if ($num >= 10000 && $num < 100000000) {
        $num = round($num / 10000, $precision) . " 万";
    } else if ($num >= 100000000) {
        $num = round($num / 100000000, $precision) . "亿";
    } else {
        $num = $num;
    }
    return $num . '次';
}

/**
 *流量格式化
 */
function formatByteFlow($data, $precision = 1)
{

    if ($data >= 1024 && $data < 1024 * 1024) {
        $data = round($data / 1024, $precision) . "KB";
    } else if ($data >= 1024 * 1024 && $data < 1024 * 1024 * 1024) {
        $data = round($data / (1024 * 1024), $precision) . "MB";
    } else if ($data >= 1024 * 1024 * 1024 && $data < 1024 * 1024 * 1024 * 1024) {
        $data = round($data / (1024 * 1024 * 1024), $precision) . "GB";
    } else if ($data >= 1024 * 1024 * 1024 * 1024) {
        $data = round($data / (1024 * 1024 * 1024 * 1024), $precision) . "TB";
    } else {
        $data = $data . "B";
    }
    return $data;
}

/**
 * format interval time
 */
function formatTiemInterval($begintime, $endtime, $precision = 1)
{

    if ($begintime > $endtime) {
        return false;
    }

    $interval = strtotime($endtime) - strtotime($begintime);

    $data = '';
    if ($interval < 60) {
        $data = "{$interval}秒";
    } else if ($interval >= 60 && $interval < 60 * 60) {
        $data = round($interval / 60, $precision) . "分钟";
    } else if ($interval >= 60 * 60 && $interval < 60 * 60 * 60) {
        $data = round($interval / (60 * 60), $precision) . "小时";
    } else if ($interval >= 60 * 60 * 60 && $interval < 60 * 60 * 60 * 60) {
        $data = round($interval / (60 * 60 * 60), $precision) . "天";
    } else if ($interval >= 60 * 60 * 60 * 60 && $interval < 60 * 60 * 60 * 60 * 60) {
        $data = round($interval / (60 * 60 * 60 * 60), $precision) . "月";
    } else if ($interval >= 60 * 60 * 60 * 60 * 60) {
        $data = round($interval / (60 * 60 * 60 * 60 * 60)) . "年";
    }
    return $data;
}

//get total page
function getTotalPage($total, $pageSize)
{
    return ceil((int)$total / (int)$pageSize);
}

//get offset
function getPageOffset($page, $pageSize)
{
    if ($page < 1) {
        $page = 1;
    }

    return (int)$pageSize * ((int)$page - 1);
}

//获取当前时间
function getCurDateTime($format = 'Y-m-d H:i:s')
{
    return date($format);
}

//过滤
function filterParam($param, $hs = false)
{
    if ($hs) {
        return htmlspecialchars(strip_tags($param));
    } else {
        return strip_tags($param);
    }

}

/**
 * [获取开始结束时间]
 * daystype 时间类型 今天、昨天、前天、上周、指定某天
 * diytime 自定义时间名称
 * return Array date Y-m-d
 */
function getStartEndTime($daystype = 1, $diytime = "")
{
    switch ($daystype) {
        case 1: //今天
            $startdate = date('Y-m-d', time());
            $enddate   = $startdate;
            break;
        case -1: //昨天
            $startdate = date('Y-m-d', strtotime('-1 day'));
            $enddate   = $startdate;
            break;
        case -2: //前天
            $startdate = date('Y-m-d', strtotime('-2 days'));
            $enddate   = $startdate;
            break;
        case -3: //上周
            $startdate = last_monday(0, false);
            $enddate   = last_sunday(0, false);
            break;
        case -4: //自定义时间

            if (empty($diytime)) {
                return false;
            }
            list($startdate, $enddate) = explode("~", $diytime);
            if (!$startdate && !$enddate) {
                return false;
            } else if ($startdate && $enddate) {
                $startdate = date('Y-m-d', strtotime($startdate));
                $enddate   = date('Y-m-d', strtotime($enddate));
            } else {
                $startdate = $enddate = ($startdate) ? date('Y-m-d', strtotime($startdate)) : date('Y-m-d', strtotime($enddate));
            }

            break;

        case -5: //30天
            $startdate = date('Y-m-d', strtotime('-30 day'));
            $enddate   = date('Y-m-d', time());
            break;
        default: //今天
            $startdate = date('Y-m-d', time());
            $enddate   = $startdate;

    }

    return array(
        'startdate' => $startdate,
        'enddate'   => $enddate,
    );
}

/**
 * [构造报表时间]
 * daystype 时间类型 今天、昨天、前天、上周、指定某天(1,-1,-2,-3,-4)
 * granularity 粒度 1m 5m 1h 1d
 * tmparrKeyName tmparr数组键名
 * return array
 */
function constructInitTime($daystype = 1, $diytime = "", $granularity = '5m', $tmparrKeyName = array('reporttime'))
{

    if (!empty($diytime)) {
        $date = getStartEndTime($daystype, $diytime);
    } else {
        $date = getStartEndTime($daystype);
    }
    if ($date) {
        $startdate = $date['startdate'];
        $enddate   = $date['enddate'];
    } else {
        $startdate = date('Y-m-d', time());
        $enddate   = $startdate;
    }
    $startdate_unix = strtotime("{$startdate} 00:00:00");
    $enddate_unix   = strtotime("{$enddate} 23:59:59");
    $tmparr         = array();
    switch ($granularity) {
        case '1m':
            $format = 'Y-m-d H:i:00';
            $step   = 60;
            break;
        case '5m':
            $format = 'Y-m-d H:i:00';
            $step   = 60 * 5;
            break;
        case '1h':
            $format = 'Y-m-d H:00:00';
            $step   = 60 * 60;
            break;
        case '1d':
            $format = 'Y-m-d';
            $step   = 24 * 60 * 60;
            break;
    }

    for ($t1 = strtotime(date($format, $startdate_unix)), $i = $t1; $i <= $enddate_unix; $i = $i + $step) {
        foreach ($tmparrKeyName as $key => $name) {
            $tmparr[$name][$i] = 0;
        }
    }

    return $tmparr;
}

/**
 * days    2015-03-10~2015-04-01|-4 | 1 -1
 * 获取开始 结束时间 return array
 */
function _getTimeArr($days)
{
    $daystype_diytime = _getDaysType($days);

    if ($daystype_diytime['diytime']) {
        $timeArr = getStartEndTime($daystype_diytime['daystype'], $daystype_diytime['diytime']);
    } else {
        $timeArr = getStartEndTime($daystype_diytime['daystype']);
    }

    return $timeArr ? $timeArr : array();
}

/**
 * days    2015-03-10~2015-04-01|-4 | 1 -1
 * 获取daystype 用于构造报表时间
 */
function _getDaysType($days)
{
    $diytime  = '';
    $daystype = $days;
    if (strstr($days, "|")) {
        list($diytime, $daystype) = explode("|", $days);
    }

    return array(
        'daystype' => $daystype,
        'diytime'  => $diytime,
    );
}

/**
 * 获取粒度
 */
function _getGranularity($timeArr)
{
    $startdate    = $timeArr['startdate'];
    $enddate      = $timeArr['enddate'];
    $intervalTime = strtotime($enddate) - strtotime($startdate);
    $granularity  = '1h';
    if ($intervalTime > 86400 && $intervalTime <= 7 * 86400) {
//1-7天按小时
        $granularity = '1h';
    } else if ($intervalTime > 7 * 86400) {
//大于7天按天
        $granularity = '1d';
    } else {
//否则按5分钟
        $granularity = '1h';
    }
    return $granularity;
}

/*
 * 安全评级
 */
function safeRating($total)
{
    $rate = '';
    $code = 0;
    if ($total == 0) {
        $rate = '安全';
        $code = 0;
    } else if ($total >= 1 && $total <= 1000) {
        $rate = '中等';
        $code = 1;
    } else if ($total > 1000) {
        $rate = '危险';
        $code = 2;
    }

    return array('code' => $code, 'rate' => $rate);
}

/*
 * 子域名级别
 */
function getSubDomainLevel($sub)
{
    return count(explode('.', $sub)) + 1;
}

//txt 'v=spf1 include:spf.mail.qq.com ~all::60'
function parseTXTValue($_value)
{
    $stalk = explode(":", $_value);
    $ttl   = isset($stalk[count($stalk) - 1]) ? $stalk[count($stalk) - 1] : 300;
    $mx    = isset($stalk[count($stalk) - 2]) ? $stalk[count($stalk) - 2] : 0;
    unset($stalk[count($stalk) - 1], $stalk[count($stalk) - 1]);
    $value = implode(":", $stalk);
    return array(
        'mx'    => $mx,
        'ttl'   => $ttl,
        'value' => $value,
    );
}

//get host 1.1.1.1:80
function getRealValue($host)
{
    if ($pos = strpos($host, ':')) {
        $host = substr($host, 0, $pos);
    } else if ($pos = strpos($host, '-')) {
        $host = substr($host, 0, $pos);
    }

    return $host;
}

//replace - to :
function replaceIP($ip)
{
    if ($pos = strpos($ip, '-')) {
        return str_replace('-', ':', $ip);
    }

    return $ip;
}

//ip地址转换为  CIDR
function ip2cidr($ip_start, $ip_end)
{
    if (long2ip(ip2long($ip_start)) != $ip_start or long2ip(ip2long($ip_end)) != $ip_end) {
        return null;
    }

    $ipl_start = bindec(decbin(ip2long($ip_start)));
    $ipl_end   = bindec(decbin(ip2long($ip_end)));
    if ($ipl_start > 0 && $ipl_end < 0) {
        $delta = ($ipl_end + 4294967296) - $ipl_start;
    } else {
        $delta = $ipl_end - $ipl_start;
    }

    $netmask = str_pad(decbin($delta), 32, "0", STR_PAD_LEFT);
    if (bindec(decbin(ip2long($ip_start))) == 0 && substr_count($netmask, "1") == 32) {
        return "0.0.0.0/0";
    }

    if ($delta < 0 or ($delta > 0 && $delta % 2 == 0)) {
        return null;
    }

    for ($mask = 0; $mask < 32; $mask++) {
        if ($netmask[$mask] == 1) {
            break;
        }
    }

    if (substr_count($netmask, "0") != $mask) {
        return null;
    }

    return "$ip_start/$mask";
}

//ip地址转换为 数字段
function cidr2ip($cidr, $isint = true)
{
    $range = array();
    $cidr  = explode('/', $cidr);
    //$range[0] = $cidr[0];//long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
    //$range[1] = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
    $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
    $range[1] = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1);

    if ($isint == true) {
        $range[0] = bindec(decbin(ip2long($range[0])));
        $range[1] = bindec(decbin(ip2long($range[1])));
    }
    return $range;
}

//判断一个ip是否在另一个IP段里
function net_match($network, $ip)
{

}

function C($config_name = '')
{
    $config_val = '';
    if ($config_name) {
        $configall = \Ypf\Lib\Config::$config; //config
        $names     = explode('.', $config_name);
        if (count($names) > 1) {
            $first_name = array_shift($names);
            $config_val = isset($configall[$first_name]) ? $configall[$first_name] : array();
            foreach ($names as $name) {
                isset($config_val[$name]) && $config_val = $config_val[$name];
            }
        } else {
            isset($configall[$config_name]) && $config_val = $configall[$config_name];
        }
    }
    return $config_val;
}

function v($content = '')
{
    var_dump($content);
}

/*
 * @获取控制ID
 * @type    {cname / ns}
 * @id        id
 */
function getControlId($type, $id)
{
    trim(strtolower($type));
    $domain_model = new \Model\Domain();
    $where        = array(
        'domain_id' => $id,
        'type'      => $type,
    );
    $info         = $domain_model->getOneByWhere($where);
    return $info["id"];
}

/*
 * @获取域名审核状态的信息
 */
function getReason($type, $id, $status)
{
    $status         = "status" . $status;
    $where          = array(
        'id_type'    => $type,
        'uid'        => $id,
        'false_type' => $status,
    );
    $yundunFalseLog = new \Model\YundunFalseLog();
    $info           = $yundunFalseLog->getOneByWhere($where);
    return $info['false_reason'] ? $info['false_reason'] : '很抱歉，工作人员没有说明原因。请联系云盾官网了解更多信息！';
}

/*
 * @获取当前域名的默认手机号
 */
function getMobile($control_id)
{
    $where['id'] = $control_id;
    $domain      = new \Model\Domain();
    $member      = new \Model\Member();
    $info        = $domain->getOneByWhere($where);
    if ('ns' == $info['type']) {
        $member_domain = new \Model\MemberDomainNs();
    } else {
        $member_domain = new \Model\MemberDomainCname();
    }
    $where     = array('id' => $info['domain_id']);
    $member_id = $member_domain->getOneFieldByWhere($where, 'member_id');
    $where     = array('id' => $member_id);
    $minfo     = $member->getOneByWhere($where);
    return $minfo['mobile'] ? $minfo['mobile'] : false;
}

function getEmail($id)
{
    $where['id'] = $id;
    $domain      = new \Model\Domain();
    $member      = new \Model\Member();
    $info        = $domain->getOneByWhere($where);
    if ('ns' == $info['type']) {
        $member_domain = new \Model\MemberDomainNs();
    } else {
        $member_domain = new \Model\MemberDomainCname();
    }
    $where     = array('id' => $info['domain_id']);
    $member_id = $member_domain->getOneFieldByWhere($where, 'member_id');
    $where     = array('id' => $member_id);
    $minfo     = $member->getOneByWhere($where);
    return $minfo['email'];
}

/*
 * @根据控制ID 获取到域名
 */
function getDomain($id)
{
    $domain      = new \Model\Domain();
    $where['id'] = $id;
    $info        = $domain->getOneByWhere($where);
    if ('ns' == $info['type']) {
        $member_domain = new \Model\MemberDomainNs();
    } else {
        $member_domain = new \Model\MemberDomainCname();
    }
    $where  = array('id' => $info['domain_id']);
    $domain = $member_domain->getOneByWhere($where, 'domain');
    return $domain['domain'] ? $domain['domain'] : "域名不存在或者已经被删除";
}

/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $charset
 * @param $dot
 */
function str_cut($string, $length, $charset = "utf-8", $dot = '...')
{
    $strlen = strlen($string);
    if ($strlen <= $length) {
        return $string;
    }

    $string = str_replace(array(' ', '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵', ' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strcut = '';
    if ($charset == 'utf-8') {
        $length = intval($length - strlen($dot) - $length / 3);
        $n      = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn  = 2;
                $n   += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn  = 3;
                $n   += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn  = 4;
                $n   += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn  = 5;
                $n   += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn  = 6;
                $n   += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
        $strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
    } else {
        $dotlen      = strlen($dot);
        $maxi        = $length - $dotlen - 1;
        $current_str = '';
        $search_arr  = array('&', ' ', '"', "'", '“', '”', '—', '<', '>', '·', '…', '∵');
        $replace_arr = array('&amp;', '&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;', ' ');
        $search_flip = array_flip($search_arr);
        for ($i = 0; $i < $maxi; $i++) {
            $current_str = ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            if (in_array($current_str, $search_arr)) {
                $key         = $search_flip[$current_str];
                $current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
            }
            $strcut .= $current_str;
        }
    }
    return $strcut . $dot;
}

//check tel
function checktel($tel)
{
//        if(preg_match('/^1[3458][0-9]{9}$/',$tel)){
    //            return true;
    //        }
    //        return false;
    return preg_match('/^0?1[3578]\d{9}$/i', $tel);
}

/**
 *过滤<script> 和php
 */
function removesp($subject)
{

    $pattern = array('/<\?php.*\?>/isU', '/<script.*<\s*\/script\s*>/isU');
    $replace = array("", "");
    $res     = preg_replace($pattern, $replace, $subject);

    return $res;
}

/**
 *只过滤php
 */
function removejustphp($subject)
{
    $pattern = array('/<\?php.*\?>/isU');
    $replace = array("");
    $res     = preg_replace($pattern, $replace, $subject);

    return $res;
}

/**
 * 格式化文件大小
 * @param int $size
 * @return string
 */
function byte_format($size = 0)
{
    $sizetext = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizetext[$i];
}

/**
 * 加载模板文件
 * @param string $template_file 模板文件
 * @param string $template_base_dir 模板所在目录（默认为Views目录）
 * @param bool|true $is_echo
 * @return string
 */
function Templater($template_file = '', $template_base_dir = '', $is_echo = true)
{
    if (!empty($template_file)) {
        if (is_array($template_file)) {
            $temp_content = \Library\TemplateInclude::include_templates($template_file, $template_base_dir);
        } else {
            $temp_content = \Library\TemplateInclude::include_template($template_file, $template_base_dir);
        }
    } else {
        $temp_content = '';
    }

    if ($is_echo) {
        echo $temp_content;
    } else {
        return $temp_content;
    }
}

//验证CIDR
function validYdCIDR($ip, $checkPrivate = true)
{

    if ($checkPrivate && validIpv4($ip, false) && isPrivateIP($ip)) {
        //不能是局域网ip
        return false;
    } else if (validIpv4($ip, false)) {
        //是IP直接返回
        return $ip;
    }

    if (false !== ($pos = strpos($ip, "/"))) {
        //exp:127.0.0.1/24
        $sip  = trim(substr($ip, 0, $pos));
        $mask = intval(substr($ip, $pos + 1));
        if (validIpv4($sip) && $mask >= 8 && $mask <= 32) {
            list($a, $b) = cidr2ip($ip, false);
            if (ip2cidr($a, $b) != null) {
                list($from, $to) = cidr2ip($ip);
                return array($from, $to);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    return false;
}

/**
 * @node_name 判断字符串是否是序列化类型
 * @param $data
 * @return bool
 */
function is_serialized($data)
{
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }

    if (!preg_match('/^([adObis]):/', $data, $badions)) {
        return false;
    }

    switch ($badions[1]) {
        case 'a':
        case 'O':
        case 's':
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                return true;
            }

            break;
        case 'b':
        case 'i':
        case 'd':
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                return true;
            }

            break;
    }
    return false;
}

/**
 * CC格式化
 */
function formatNumByCCQPS($num, $precision = 1)
{
    if ($num >= 10000 && $num < 100000000) {
        $num = round($num / 10000, $precision) . " 万";
    } else if ($num >= 100000000) {
        $num = round($num / 100000000, $precision) . "亿";
    } else {
        $num = $num;
    }
    return $num . 'QPS';
}

/**
 * @node_name xhprof检测结束，并生成分析文件
 * @param string $output_dir 输出文件保存路径
 * @param string $visit_domain 访问url主域名
 */
function handler_xhprof_end($output_dir = '', $visit_domain = 'xhprof.vm')
{
    try {
        $XHPROF_ROOT = !empty($output_dir) ? $output_dir : "/usr/local/src/xhprof-0.9.4";

        $xhprof_data = xhprof_disable();
        include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
        include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

        $xhprof_runs = new \XHProfRuns_Default();

        $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");

//        echo "\n". "http://{$visit_domain}/index.php?run=$run_id&source=xhprof_foo\n". "\n";
    } catch (\Exception $e) {
        var_dump(__FUNCTION__);
        var_dump($e->getMessage());
    }
}

/**
 * @desc 多某一列的类型是多维数组的情况下，取数组一列的值
 * @param array $data 数组
 * @param mixed $column 列名
 * @param mixed $index_key 作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
 * @return array
 */
function multi_array_column($data = [], $column = '', $index_key = '')
{
    $ret = [];
    if (!empty($data) && is_array($data) && !empty($column)) {
        $columns = explode('.', $column);
        if (1 == count($columns)) {
            return array_column($data, $column, $index_key);
        } else {
            $ret  = $data;
            $loop = 0;
            $keys = [];
            foreach ($columns as $val) {
                $ret = array_column($ret, $val, !$loop ? $index_key : '');
                !$loop && $keys = array_keys($ret);
                $loop++;
            }
            empty($keys) && $keys = rand(0, count($ret));
            $ret = array_combine($keys, $ret);
        }
    }
    return $ret;
}

/**
 * @param $str
 * @return array
 * @node_name json转数组
 * @link
 * @desc 递归
 */
function jsonToArray($str)
{
    if (is_string($str)) {
        $str = json_decode($str);
    }
    $arr = [];
    foreach ($str as $k => $v) {
        if (is_object($v) || is_array($v) || !is_null(json_decode($v))) {
            $arr[$k] = jsonToArray($v);
        } else {
            $arr[$k] = $v;
        }
    }
    return $arr;
}

/**
 * @node_name 调试日志
 * @link
 * @desc
 */
function __fileDebug()
{
    $file = '/tmp/yundun_' . $_SERVER['HTTP_HOST'] . '_' . date('Ymd') . '.log';

    $params = func_get_args();
    foreach ($params as $param) {
        if (is_array($param) || is_object($param)) {
            $param = var_export($param, 1);
        } elseif (is_resource($param)) {
            $msgType = get_resource_type($param);
            $param   = "resource of type ($msgType)";
        }
        $message = sprintf("%s\t%s\t\n", "[" . date("Y-m-d H:i:s") . "]", $param);
        file_put_contents($file, $message, FILE_APPEND);
    }
}


// windows系统
function isWin()
{
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        return true;
    }
    return false;
}

function logAdminV3($value, $logFile = '')
{
    if (isWin()) {
        $file = 'E:/adminV3.log';
    } else {
        $file = '/tmp/adminV3'.date('Y-m-d').'.log';
    }
    if (!empty($logFile) && file_exists(dirname($logFile))) {
        $file = $logFile;
    }
    if (is_array($value)) {
        file_put_contents($file, getCurDateTime() . ':' . print_r($value, true) . "\n", FILE_APPEND);
    } else if (is_string($value)) {
        file_put_contents($file, getCurDateTime() . ':' . $value . "\n", FILE_APPEND);
    } else if (is_numeric($value)) {
        file_put_contents($file, getCurDateTime() . ':' . $value . "\n", FILE_APPEND);
    } else if (is_object($value)) {
        file_put_contents($file, getCurDateTime() . ':' . print_r($value, true) . "\n", FILE_APPEND);
    } else {
        file_put_contents($file, getCurDateTime() . ':' . $value . "\n", FILE_APPEND);
    }
}

//取行本机IP，使用socket, 无依赖, 支持ipv4, ipv6
function get_machine_ip($ipVersion = 4, $dest='119.29.29.29', $port=22) {
    $procotol = $ipVersion == 6 ? AF_INET6 : AF_INET;
    $socket = socket_create($procotol, SOCK_DGRAM, SOL_UDP);
    socket_connect($socket, $dest, $port);
    socket_getsockname($socket, $addr, $port);
    socket_close($socket);
    return $addr;
}
