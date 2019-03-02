<?php
/**
 * Desc: 日志服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/7 16:50
 */

namespace Service\Log;
use Model\Model;
class LogService extends \Service\Service{

    public $config;
    public $ret;
    public $domainInfo;
    private $sys_params = array();
    private $m_log = null;
    
    public function __construct($array = array()){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
        $this->sys_params = array(
            'user_id' => isset($array['user_id']) ? $array['user_id'] : '0',
            'nickname' => isset($array['nickname']) ? $array['nickname'] : '未知',
            'module' => isset($array['module']) ? $array['module'] : 'unKnown',
            'controller' => isset($array['controller']) ? $array['controller'] : 'unKnown',
            'action' => isset($array['action']) ? $array['action'] : 'unKnown',
            'params' => isset($array['params']) ? $array['params'] : '',
            'method' => isset($array['method']) ? $array['method'] : '0',
            'addtime' => isset($array['addtime']) ? $array['addtime'] : date('Y-m-d H:i:s'),
            'ip' => isset($array['ip']) ? $array['ip'] : '',
        );
        empty($this->sys_params['ip']) && ($this->sys_params['ip'] = get_real_ip());
        $this->m_log = new \Model\AdminOperateLog();
    }


    public function initModels(){
        $this->models = new \stdClass();
        $this->models->user_log                        = new \Model\UserLog();
        $this->models->member_log                        = new \Model\MemberLog();
        $this->models->send_log                        = new \Model\SendLog();
//        $this->models->api_log                        = new \Model\ApiLog();
//        $this->models->dispatch_log                        = new \Model\DispatchLog();
    }

    public function save_user_log($table,$type,$message){
        $data["user_id"] = $_SESSION[$this->global_config["USER_AUTH_KEY"]]['id'];
        $data['ip']      = get_real_ip();
        $data["datetime"]= date("Y-m-d H:i:s");
        $data["message"] = $message;
        $data["table"]   = $table;
        $data["type"]    = $type;

        $this->models->user_log->add($data);
    }
    //使用此方法记录用户操作日志
    public function save($message, $keyword = '', $obj_userid = '', $domain = '', $remark = '', $params = ''){
    	$data = $this->sys_params;
    	$data['message'] = $message;
    	$data['keyword'] = $keyword;
    	$data['obj_user'] = intval($obj_userid);
    	$data['domain'] = $domain;
    	$data['remark'] = $remark;
        !empty($params) && $data['params'] = $params;
    	return $this->m_log->add($data);
    }
    
    //使用此方法读取日志
    public function getLog(){
    	
    }

    public function writeSendLog( $type , $adress , $content ){
        $data	= array(
            'type'		=> $type,
            'adress'	=> $adress,
            'info'		=> $content,
            'datetime'	=> date("Y-m-d H:i:s"),
        );
        $this->models->send_log->add($data);
    }
}
