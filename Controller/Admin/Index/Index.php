<?php
namespace Controller\Admin\Index;
class Index extends \Controller\Admin\Common\Common{

    public $versionLogService;

    public function __construct(){
        parent::__construct();
    }

	public function index(){
        $layout_params = array(
            'header' => '',
            'footer' => '',
            'slide'  => array('index')
        );
        $this->setHeaderFooter($layout_params);
        $this->view->display('Admin/Index/index.html');

	}

    public function getLatestVersionLog(){
//        $payload = array(
//            'domain_id' => 123456,
//            'domain_type' => 'ns'
//        );
//        $res = $this->sdk->api_call('Domain.Dispatch.domainTask.add', $payload,true);
//        $payload = array(
//            'ip' => '162.221.12.106',
//            'source' => __METHOD__
//        );
//        $res = $this->sdk->api_call('Domain.Dispatch.IpTask.add', $payload,true);
        $this->ret = $this->versionLogService->getLatestVersionLog();
        $this->ajaxReturn($this->ret);
    }
}
