<?php

require_once('Socket_Beanstalk.php');

class QueueWorker_Beanstalk extends Socket_Beanstalk
{

    const QUEUE_HAND_MONITOR_CALLBACK = 'new_hand_monitor_callback';
    //construct
    public function __construct($config)
    {
        $this->setTubeProperty($config['tubeConf']);
        parent::__construct($config['conConf']);
    }

    //set tube property and unset tube property in config
    protected function setTubeProperty($config)
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
            unset($v);
        }
    }

    //get tube
    public function getTube($tubeKey)
    {
        return isset($this->$tubeKey) ? $this->$tubeKey : null;
    }


    //use tube then insert a job
    public function use_put($tube, $data, $pri = 0, $delay = 0, $ttr = 30)
    {
        $this->useTube($tube);
        $job_id = $this->put($pri, $delay, $ttr, $data);
        return $job_id;
    }


    /*
     * 发送邮件，内容以json格式
     * $mailto string 接收方
     * $subject string 邮件标题
     * $body string 邮件正文
     * $type string   text/html
     *
     */
    public function sendmail($mailto, $subject, $body, $type = 'html')
    {
        if (strlen($body) >= 8096 * 2) return false;
        $subject     = str_replace('云盾', 'YUNDUN', $subject);
        $content_arr = [
            'mailto'  => $mailto,
            'subject' => $subject,
            'type'    => $type,
            'body'    => $body
        ];
        $content     = json_encode($content_arr);
        $this->use_put($this->tube_mail_send, $content);
    }

    /*
     * 发送邮件
     * $mailto string 接收方
     * $subject string 邮件标题
     * $body string 邮件正文
     * $type string   text/html
     *
     */
    public function sendmail_old($mailto, $subject, $body, $type = 'text')
    {
        if (strlen($body) >= 8096 * 2) return false;
        $subject = str_replace('云盾', 'YUNDUN', $subject);
        $content = "{$mailto}\t{$subject}\t{$type}\t" . base64_encode($body);
        $this->use_put($this->tube_mail_send, $content);
    }

    /*
     * 发送短信
     * $mobile string 手机号，多个手机号用分号或逗号隔开
     * $body string 内容
     * $type string   text/html
     *
     */
    public function sendsms($mobile, $body)
    {
        $mobile = str_replace(',', ';', $mobile);
        if (strlen(urlencode($body)) > 750) return false;
        $content_arr = [
            'mobile' => $mobile,
            'body'   => $body
        ];
        $content     = json_encode($content_arr);
        $this->use_put($this->tube_sms_send, $content);
    }

    /*
    * 发送短信
    * $mobile string 手机号，多个手机号用分号或逗号隔开
    * $body string 内容
    * $type string   text/html
    *
    */
    public function batchSendSms($mobile, $body)
    {
//        $mobile = str_replace(',', ';', $mobile);
        if (strlen(urlencode($body)) > 750) return false;
        $content_arr = [
            'mobile' => $mobile,
            'body'   => $body
        ];
        $content     = json_encode($content_arr);
        $rs = $this->use_put($this->tube_market_batch_send_sms_v4, $content);
        return $rs;
    }

    /*
     * 发送短信
     * $mobile string 手机号，多个手机号用分号或逗号隔开
     * $body string 内容
     * $type string   text/html
     *
     */
    public function sendsms_old($mobile, $body)
    {
        $mobile = str_replace(',', ';', $mobile);
        $body   = mb_convert_encoding($body, 'gbk', 'utf-8');
        if (strlen(urlencode($body)) > 750) return false;
        $content = "{$mobile}\t" . base64_encode(urlencode($body));
        $this->use_put($this->tube_sms_send, $content);
    }

    /**
     * 解除Ip上绑定的域名
     * @param array $params
     */
    public function unbindIpDomains($params = array())
    {
        $data = $params;
        $this->use_put($this->tube_ip_dispatch, json_encode($data));
    }

    public function dispatcher($params = array())
    {
        $data       = $params;
        $queue_name = isset($params['args']['queue_name']) ? $params['args']['queue_name'] : 'z_ip_dispatch';
        //判断是否有域名。如果有域名查询 域名是否使用新调度。然后写入新调度队列
        if (isset($params['domain_id'])) {
            $webCdnDomainModel = new \Model\CpV4\WebCdnDomainModel();
            $where             =  [
                'id' => $params['domain_id']
            ];
            $domain_info = $webCdnDomainModel->getOneByWhere($where);
            if ($domain_info && $domain_info['is_new_dispatch']) {
                $queue_name = 'disp_domain';
            }
        }
        return $this->use_put($queue_name, json_encode($data));
    }

    public function syncDnsDomains($params = array())
    {
        $data = $params;
        $this->use_put($this->tube_sync_dns_domain, json_encode($data));
    }

    /**
     * @param array $params
     * @node_name 云解析同步
     * @link
     * @desc
     */
    public function syncDnsDomainsV4($params = array())
    {
        $data = $params;
        $this->use_put($this->queue_sync_dns_domain_v4, json_encode($data));
    }

    public function syncCnameRecords($params = array())
    {
        $data = $params;
        $this->use_put($this->tube_sync_cname_records, json_encode($data));
    }

    /**
     * @param array $params
     * @node_name 云加速同步
     * @link
     * @desc
     */
    public function syncCnameRecordsV4($params = array())
    {
        $data = $params;
        $this->use_put($this->queue_sync_cname_records_v4, json_encode($data));
    }

    public function syncCnameRecordsSingleDomain($domain_id, $domain, $domain_type = 'cname', $action = 'syncCnameRecordsSingleDomain', $member_id = 22)
    {
        $data = array(
            "action" => $action,
            "args"   => array(
                "member_id"   => $member_id,
                "action"      => $action,
                'domain_type' => $domain_type,
                'domain'      => $domain,
                'domain_id'   => $domain_id,
            ),
        );
        $this->use_put($this->tube_sync_cname_records, json_encode($data));
    }

    /**
     * @node_name 发送清除用户下线缓存到队列
     * @param array $params
     */
    public function clearAgentCache($params = array())
    {
        $data = $params;

        $this->use_put($this->tube_clear_member_agent_cache, json_encode($data));

    }

    /**
     * 发送job到redisConf
     * @param $params
     */
    public function sendJobToRedisConf($params)
    {
        $this->use_put($this->tube_queue_tube_redis_conf, json_encode($params));
    }

    /**
     * @node_name 添加记录后台操作日志到ES任务到队列
     * @param array $params
     * @return bool|int
     */
    public function addAdminLogQueue($params = [])
    {
        $job_id = $this->use_put($this->tube_queue_tube_admin_log_es, json_encode($params));
        return $job_id;
    }


    public function batchSendNotice($params=[]){
        $job_id = $this->use_put($this->tube_batch_send_notice_v4, json_encode($params));
        return $job_id;
    }

    /**
     * @node_name 发送迁移域名到队列
     * @param array $params
     * @return bool|int
     */
    public function sendSetMigrate($tube = '',$params = array()) {
        $data   = $params;
        $job_id = $this->use_put($tube, json_encode($data));
        return $job_id;
    }

    /**
     * @param array $params
     * @node_name 太极抗D plus套餐IP调度回调
     * @return bool|int
     */
    public function addTjkdPlusIpDispatchCallback($params = []){
        $job_id = $this->use_put($this->tjkd_plus_ip_dispatch_callback, json_encode($params));
        return $job_id;
    }

    /**
     * @param array $params
     * @node_name 调度回调分发
     * @return bool|int
     */
    public function addDispatchCallbackDistribute($params = []){
        $job_id = $this->use_put($params['tube'], json_encode($params['data']));

        return $job_id;
    }

    /**
     * @param array $params
     * @node_name 套餐手动添加删除 套餐IP
     * @link
     * @desc
     * @return  bool |int
     */
    public function addPlusHandProtectIpCallback($tube = '',$params = []) {
        $job_id = $this->use_put($tube , json_encode($params));
        return $job_id;
    }


    public function handMonitorCallback($data = []) {

        return $this->use_put(self::QUEUE_HAND_MONITOR_CALLBACK,json_encode($data));
    }


}
