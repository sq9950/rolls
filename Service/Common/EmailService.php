<?php
/**
 * Desc: 邮件发送服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/21
 */

namespace Service\Common;
class EmailService extends \Service\Service{

    public $ret;
    public $queueService;
    public function __construct($queueService = ''){
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

    /*
		 * @状态变更的通知
		 * */
    public function mail_other_monitor_status( $id , $body , $title){
        /*
         * @获取发送设置
         * */
        $mail	= getEmail( $id );
        $domain	= getDomain( $id );
        $logService = new \Service\Log\LogService();
        $logService->writeSendLog( 1 , $mail , $title ? $title : "域名：{$domain}状态变更通知！");
        /*
         * @缺少日志记录
         * */
        $this->sendEmail( $mail , $title ? $title : "网站审核通知" , $body);
    }

    public function sendEmail($mailto, $subject, $body, $type='html'){

        if( !$this->models->member->isAllowSend( "email" , $mailto ) ||
            strlen($body) >= 8096 * 2
        ){
            return false;
        }

        $this->queueService->qw_bs->sendmail($mailto, $subject, $body, $type);
    }


}