<?php
namespace Controller\Admin\Auth;
use Service\EsService\AdminLogEsService;

class UserLog extends \Controller\Admin\Common\Common {

    private $m_log = null;
    public  $userLogService;

    public function __construct() {
        parent::__construct();
        $this->m_log          = new \Model\AdminOperateLog();
        $this->userLogService = new \Service\Auth\UserLogService();
    }

    public function index() {
        $this->setHeaderFooter();
        $actions['exportAuthLog'] = $this->buildUrl('exportAuthLog');
        $actions['getLogList']    = $this->buildUrl('getLogList');
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Auth/userlog/index.html');
    }

    /**
     * @desc      查询日志列表
     */
    public function getLogList() {
        $param = $this->parseJplistStatuses($this->req['statuses']);
        $adminLogEsService = new AdminLogEsService();
        $result = $adminLogEsService->getLogList($param['where'], $param['offset'], $param['limit']);
        $result['data'] = $adminLogEsService->decorateData($result['data']);
        $this->ajaxReturn($result);
    }

    /**
     * @desc    导出日志
     */
    public function exportAuthLog() {
        $this->userLogService->exportAuthLog($this->post);
    }

}
