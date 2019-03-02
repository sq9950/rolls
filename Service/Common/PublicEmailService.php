<?php
/**
 * Desc: 公共邮件发送服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016-10-18 11:31:58
 */

namespace Service\Common;
class PublicEmailService extends \Service\Service {

    public $ret;
    public $queueService;

    public function __construct($queueService = '') {
        parent::__construct();
        $this->queueService = \Service\Common\QueueService::getInstance();

    }


    public function initModels() {

    }

    public function sendEmail($mailTo, $subject, $body, $type = 'html') {
        if (empty($mailTo) || strlen($body) >= 8096 * 2) {
            return false;
        }
        !is_array($mailTo) && $mailTo = [$mailTo];
        foreach ($mailTo as $mail) {
            $this->queueService->qw_bs->sendmail($mail, $subject, $body, $type);
        }
    }


}