<?php
/**
 * Desc: 短信发送服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/21
 */

namespace Service\Common;
class SmsService extends \Service\Service{

    public $ret;
    public $queueService;

    public function __construct(){
        parent::__construct();
        $this->queueService = \Service\Common\QueueService::getInstance();
        $this->initModels();
    }


    public function initModels(){
        $this->models->member = new \Model\Member();
        $this->models->domain = new \Model\Domain();
        $this->models->member_domain_cname = new \Model\MemberDomainCname();
        $this->models->member_domain_ns = new \Model\MemberDomainNs();
    }


    /**
     * @状态变更的短信通知
     **/
    public function sms_other_monitor_status( $id , $body ){

        $mobile	= getMobile( $id );
        $domain	= getDomain( $id );
        $logService = new \Service\Log\LogService();
        $logService->writeSendLog( 2 , $mobile , "域名：{$domain}状态变更通知！" );
        $this->sendSms( $mobile, $body );
    }

    public function sendSms($mobile, $body){
        $res=$this->queueService->qw_bs->sendsms($mobile, $body);
        return $res;
    }

}