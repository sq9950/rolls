<?php

namespace Controller\Admin\Common;

use Service\Cache\CacheService;
use Service\Common\DomainCommonService;

class Common extends \Controller\Controller
{

    public $ret = array('status' => 0, 'info' => '操作失败');

    public $operations = array(); //合法操作

    public $operate_logs = null;

    //pre assgin data
    protected $config = null;

    public $pageSize = 20;
    public $userService;

    public function __construct()
    {
        parent::__construct();
        if (!$this->_isLogin()) {
            //            die('logout ...');
            $this->redirect($this->configall['USER_AUTH_GATEWAY']);
        }
        include_once __LIBRARY__ . '/Util/JplistHandle.class.php';
        include_once __LIBRARY__ . '/Util/Rbac.class.php';
        include_once __LIBRARY__ . '/Util/Url.class.php';
        $this->userService = new \Service\Common\UserService();
        $userService       = $this->userService;
        $user_status       = $this->userService->getLoggedUserStatus();
        if ($userService::USER_STATUS_OPEN != $user_status) {
            $this->view->display('Admin/Public/access.html');
            die;
        }
        $this->getActions();
        $have_access = \Rbac::AccessDecision($this->request->get);
        if (!$have_access) {
            $res                   = array(
                'status' => 0,
                'info'   => '无权限',
                'data'   => array(),
            );
            $log_params['params']  = json_encode($this->request->get, JSON_UNESCAPED_UNICODE);
            $log_params['message'] = "权限错误：{$res['info']}";
            $this->saveLog($log_params);
            $this->view->display('Admin/Public/access.html');
            die;
            //            $this->ajaxReturn($res);
        }
        $this->user_model   = new \Model\User();
        $memberinfo         = $this->getMemberInfo();
        $this->operate_logs = new \Service\Log\LogService(array(
            'user_id'    => $this->getMemberId(),
            'nickname'   => $memberinfo['nickname'],
            'module'     => $this->module_name,
            'controller' => $this->controller_name,
            'action'     => $this->function_name,
            'params'     => json_encode($this->req),
            'method'     => IS_GET ? 2 : (IS_POST ? 8 : 0),
            'addtime'    => date('Y-m-d H:i:s'),
            'ip'         => get_real_ip(),
        ));
    }

    /**
     * 获取当前所在导航菜单节点名称
     * @param int $pos
     * @return string
     */
    protected function get_current_nav_node($pos = 1)
    {
        $request_uri = $this->request->server['REQUEST_URI'];
        $uri_arr     = explode('/', $request_uri);
        return isset($uri_arr[$pos]) ? $uri_arr[$pos] : '';
    }

    //登陆检测
    protected function _checkMemberLogin()
    {
        $action = $this->getAction();
        if (in_array($action, array('alipaycallback', 'getProductByProductId'))) {
            return;
        }
        //跳过登录检测
        if (!$this->_isLogin()) {
            if ($this->isAjax()) {
                if ($this->ispost()) {
                    $data = array(
                        'status' => array(
                            'code'      => -18,
                            'message'   => '会话已过期，请重新登录',
                            'create_at' => date('Y-m-d H:i:s'),
                        ),
                    );
                    $this->ajax->output($data);
                } else {
                    die('会话已过期，请重新<a href="/login">登录</a>');
                }
            } else {
                $this->error('会话已过期，请重新<a href="/login">登录</a>', '/login');
            }

            unset($_SESSION[$this->configall['USER_AUTH_KEY']]);
            unset($_SESSION[$this->configall['HOME_LOGIN_VERIFY']]);
        }

        unset($_SESSION[$this->configall['USER_AUTH_KEY']]['password']);
    }

    //user agent and ip session check
    protected function _checkUserAgentAndIp()
    {
        return isset($_SESSION[$this->configall['HOME_LOGIN_VERIFY']]) && $_SESSION[$this->configall['HOME_LOGIN_VERIFY']] == md5IPUserAgent();
    }

    //设置头部 底部
    protected function setHeaderFooter($params = array())
    {
        $header_args = isset($params['header']) && is_array($params['header']) ? $params['header'] : array();
        $footer_args = isset($params['footer']) && is_array($params['footer']) ? $params['footer'] : array();
        $slide_args  = isset($params['slide']) && is_array($params['slide']) ? $params['slide'] : array();
        $this->view->assign('header', $this->action("\Controller\Admin\Common\Header\index", $header_args));
        $this->view->assign('slide_common', $this->action("\Controller\Admin\Common\SlideCommon\index", $slide_args));
        $this->view->assign('footer', $this->action("\Controller\Admin\Common\Footer\index", $footer_args));
    }

    /**
     * 网站全局配置
     */
    private function _preAssign()
    {
    }

    /**
     * 获取页面头部导航菜单
     */
    public function get_header_nav_nodes()
    {
        $nav_list = array();
        $user_id  = $this->getMemberId();
        if ($user_id) {
            $nav_list = \Rbac::getUserLevel1List($user_id);
            foreach ($nav_list as $key => $node) {
                $sort[$key] = $node['sort'];
            }
            array_multisort($sort, SORT_ASC, $nav_list);
        }
        return $nav_list;
    }

    /**
     * 获取页面二级菜单
     */
    public function get_slide_nav_nodes()
    {
        $slide_list = array();
        $user_id    = $this->getMemberId();
        if ($user_id) {
            $node_name = $this->get_current_nav_node();
            $pid       = \Rbac::getNodeIdByName($node_name);

            $slide_list = \Rbac::getUserLevel2List($user_id, $pid);
            foreach ($slide_list as $key => $node) {
                $ids[$key]  = $node['id'];
                $sort[$key] = $node['sort'];
            }
            isset($sort) && is_array($sort) && !empty($sort) && array_multisort($sort, SORT_ASC, $slide_list);
        }
        return $slide_list;
    }

    /**
     * 获取指定导航的二级菜单
     * @param string $pid
     * @return array|bool
     */
    public function get_slide_nav_nodes_by_pid($pid = '')
    {
        $slide_list = array();
        $user_id    = $this->getMemberId();
        if ($pid && $user_id) {
            $slide_list = \Rbac::getUserLevel2List($user_id, $pid);

            foreach ($slide_list as $key => $node) {
                $ids[$key]  = $node['id'];
                $sort[$key] = $node['sort'];
            }
            isset($sort) && is_array($sort) && !empty($sort) && array_multisort($sort, SORT_ASC, $slide_list);
        }
        return $slide_list;
    }

    /**
     * csrf todo... has problem
     */
    protected function _makeCsrfToken()
    {
    }

    protected function _validCsrfToken()
    {
    }

    //获取域名记录允许的记录类型和线路
    protected function getDomainRecordTypeLine($domain_id, $domain_type)
    {
    }

    /**
     * get domain control_id
     */
    protected function getDomainControlId($domain_id, $domain_type)
    {
    }

    //update domain status
    protected function updateDomainStatus($domain_id, $domain_type, $status)
    {
    }

    //获取上一步 下一步
    protected function _getGuideUrl($domain_id, $domain_type = 'cname')
    {
    }

    //设置分页样式 传递page对象
    protected function setPageConfig($page)
    {
    }

    //上传图片
    protected function uploadFile($type = 'default')
    {
        require_once __LIBRARY__ . '/UploadFile.class.php';
        $upload = new \UploadFile();
        $uid    = $this->getMemberId();
        switch ($type) {
            case 'invoice': //上传发票所需证件图片
                $upload->__set('savePath', './uploads/card/' . $uid . '/');
                $upload->__set('allowExts', array(
                    'gif',
                    'jpg',
                    'jpeg',
                    'bmp',
                    'png',
                ));
                $upload->__set('maxSize', 2097152);
                $cache_key = 'upload_cap_' . $uid . '_' . date('Ymd'); //key格式  upload_cap_用户ID_日期
                $now_cap   = $this->cache_memcached->get($cache_key);
                $now_cap   = $now_cap ? $now_cap : 0;
                //限制今天上传的容量，不超过10M
                if ($now_cap > 10485760) {
                    $this->ret['status'] = 0;
                    $this->ret['info']   = '今天上传超过限制！';
                    return $this->ret;
                }
                if ($upload->upload()) {
                    $result                = $upload->getUploadFileInfo();
                    $result[0]['savepath'] = substr($result[0]['savepath'], 1); //修正一下路径
                    $now_cap               = $now_cap + $result[0]['size'];
                    $this->cache_memcached->save($cache_key, $now_cap, 60 * 60 * 24);
                    $this->ret['status'] = 1;
                    $this->ret['info']   = $result[0];
                } else {
                    $this->ret['status'] = 0;
                    $this->ret['info']   = $upload->getErrorMsg();
                }
                return $this->ret;
                break;
            default:
                break;
        }
    }

    protected function dispatch_ip($dispatch_params = array(), $is_ajaxreturn = true)
    {
        //发送任务到队列
        empty($dispatch_params) && $dispatch_params = $this->post;
        $action_type         = $dispatch_params['action'];
        $tube_config         = $this->configall['db']['tube'];
        $domainCommonService = new DomainCommonService();
        switch ($action_type) {
            case 'dispatchIp':
                $queue_params = array(
                    'action' => $action_type,
                    'args'   => array(
                        'member_id'  => $this->getMemberId(),
                        'action'     => 'dispatchIp',
                        'ip'         => $dispatch_params['ip'],
                        'type'       => $dispatch_params['dispatch_type'],
                        // flush | inObserve | outObserve | inNormal | OutNormal
                        'queue_name' => $tube_config['QUEUE_NEW_IP_DISPATCH'],
                    ),
                );
                break;
            case 'dispatch_node':
                $queue_params = array(
                    'args' => array(
                        'queue_name' => $tube_config['QUEUE_NEW_IP_DISPATCH'],
                    ),
                );
                break;
            default:
                $queue_params = array(
                    'action' => 'dispatcher',
                    'args'   => array(
                        'member_id'   => $this->getMemberId(),
                        'action'      => 'dispatcher',
                        'domain_id'   => $dispatch_params['domain_id'],
                        'domain_type' => isset($dispatch_params['domain_type']) ? $dispatch_params['domain_type'] : $this->domain_type,
                        'type'        => $dispatch_params['dispatch_type'],
                        // flush | inObserve | outObserve | inNormal | OutNormal
                        'queue_name'  => $tube_config['QUEUE_NEW_IP_DISPATCH_DOMAIN'],
                    ),
                );
                $domain       = $domainCommonService->getDomainByIdType($dispatch_params['domain_id'],
                    $this->domain_type);
                $is_open_ssl  = $domainCommonService->isDomainOpenSsl($domain);
                $is_open_ssl && $queue_params['args']['queue_name'] = $tube_config['QUEUE_NEW_IP_DISPATCH_SSL'];
                break;
        }
        $queue_params['args']['source'] = isset($dispatch_params['source']) ? $dispatch_params['source'] : '手动IP调度';
        $res                            = $this->qw_bs->dispatcher($queue_params);
        $log_params['params']           = json_encode($queue_params, JSON_UNESCAPED_UNICODE);
        $log_params['domain']           = $domainCommonService->getDomainByIdType($dispatch_params['domain_id'],
            isset($dispatch_params['domain_type']) ? $dispatch_params['domain_type'] : $this->domain_type);
        $log_params['message']          = "手动IP调度";
        $this->saveLog($log_params);
        //sleep(1);
        if ($is_ajaxreturn) {
            $this->ajaxReturn($this->ret);
        } else {
            return $this->ret;
        }
    }

    /**
     * 解析分页插件传递的分页参数statuses
     * @param string $statuses
     * @return array
     */
    public function parseJplistStatuses($statuses = '')
    {
        $where    = array();
        $jplister = new \JplistHandle($statuses);

        $jplister->getStatusesByType("filterEq"); //解析文本查询参数 等于 查询
        $jplister->getStatusesByType("filterLeq"); //解析文本查询参数 小于等于 查询
        $jplister->getStatusesByType("filterGeq"); //解析文本查询参数 大于等于 查询
        $jplister->getStatusesByType("filterIn"); //解析文本查询参数 IN 查询
        $jplister->getStatusesByType("filterNotIn"); //解析文本查询参数 NOT IN 查询
        $jplister->getStatusesByType("filter"); //解析文本查询参数 like 查询
        $jplister->getStatusesByType("radio"); //解析单选项
        foreach ((array)$jplister->filterEqStatuses as $obj) {
            if (isset($obj->data->value) && !empty($obj->data->value)) {
                $obj->name        = '`' . $obj->name . '`';
                $obj->data->value = trim($obj->data->value);
                $obj->data->value && $where[$obj->name] = $obj->data->value;
            }
        }
        foreach ((array)$jplister->filterLeqStatuses as $obj) {
            if (isset($obj->data->value) && !empty($obj->data->value)) {
                $obj->name        = '`' . $obj->name . '`';
                $obj->data->value = trim($obj->data->value);
                $obj->data->value && $where[$obj->name] = ['elt', $obj->data->value];
            }
        }
        foreach ((array)$jplister->filterGeqStatuses as $obj) {
            if (isset($obj->data->value) && !empty($obj->data->value)) {
                $obj->name        = '`' . $obj->name . '`';
                $obj->data->value = trim($obj->data->value);
                $obj->data->value && $where[$obj->name] = ['egt', $obj->data->value];
            }
        }
        foreach ((array)$jplister->filterInStatuses as $obj) {
            if (isset($obj->data->value) && !empty($obj->data->value)) {
                $name  = '`' . $obj->name . '`';
                $value = explode(',', $obj->data->value);
                is_array($value) && !empty($value) && $where[$name] = array('in', $value);
            }
        }
        foreach ((array)$jplister->filterNotInStatuses as $obj) {
            if (isset($obj->data->value) && !empty($obj->data->value)) {
                $name  = '`' . $obj->name . '`';
                $value = explode(',', $obj->data->value);
                is_array($value) && !empty($value) && $where[$name] = array('not in', $value);
            }
        }
        foreach ((array)$jplister->filterStatuses as $obj) {
            switch ($obj->type) {
                case 'checkbox-dropdown':
                    if (!empty($obj->data->pathGroup)) {
                        $where[$obj->name] = $obj->data->pathGroup;
                    }
                    break;
                default:
                    $obj->name        = '`' . $obj->name . '`';
                    $obj->data->value = trim($obj->data->value);
                    $obj->data->value && $where[$obj->name] = array('like', '%' . $obj->data->value . '%');
                    break;
            }
        }
        foreach ((array)$jplister->radioStatuses as $obj) {
            if ($obj->data->path == '' && $obj->data->path == 0) {
                unset($where[$obj->name]);
            } else {

                $where["`{$obj->name}`"] = $obj->data->path;
            }
        }
        $jplister->getStatusesByType("paging"); //解析分页参数

        $jplister->getPagingQuery();

        $order['id'] = 'DESC';

        $params = array(
            'where'  => $where,
            'offset' => $jplister->jplsit_page->offset,
            'limit'  => $jplister->jplsit_page->pageSize,
            'order'  => $order,
        );
        return $params;
    }

    /**
     * @param $params
     * @return array
     * @node_name 分页参数命名
     * @link
     * @desc  跟Api的分页参数同步
     */
    public function parseParams4Api($params)
    {
        $res = [];
        foreach ($params as $key => $param) {
            switch ($key) {
                case 'offset':
                    $res['page'] = $params['offset'] / $params['limit'] + 1;
                    break;
                case 'limit':
                    $res['per_page'] = $param;
                    break;
                case 'where':
                    if (!empty($param)) {
                        foreach ($params['where'] as $conKey => $con) {
                            $parseKey       = trim($conKey, '`');
                            $res[$parseKey] = $con;
                        }
                    }
                    break;
                default:
                    $res[$key] = $param;
                    break;
            }
        }
        return $res;
    }


    /**
     * 修改记录状态公共操作
     * @param $statusModel
     * @param $where
     * @param $data
     */
    public function changeStatusCommon($statusModel, $where = '', $data = '')
    {
        if ($statusModel instanceof \Model\CommonModel) {
            if (empty($where)) {
                $where = isset($this->request->post['id']) ? intval($this->request->post['id']) : 0;
            }
            if (empty($data)) {
                $data = isset($this->request->post['status']) ? (in_array($this->request->post['status'],
                    array(0, 1)) ? $this->request->post['status'] : 0) : 0;
            }
            $statusService = new \Service\Common\StatusService();
            $statusService->setStatusModel($statusModel);
            $this->ret = $statusService->changeStatus($where, $data);
        } else {
            $this->ret = array('status' => 0, 'info' => '数据模型不合法');
        }
    }

    /**
     * 生成URL
     * @param array $function_names
     * @param array $args
     * @param bool|true $case_sensitive
     * @return string
     */
    public function buildUrl($function_names = array(), $args = array(), $case_sensitive = true)
    {
        $current_this_controller = get_class($this);
        $controller_arr          = explode('\\', $current_this_controller);
        $controller_name         = strtolower(array_pop($controller_arr));
        $module_name             = strtolower(array_pop($controller_arr));
        if (is_array($function_names)) {
            foreach ($function_names as $function_key => $function_name) {
                $result[is_string($function_key) ? $function_key : $function_name] = \Url::get_function_url($module_name,
                    $controller_name, $function_name, $args, $case_sensitive);
            }
        } else {
            $result = \Url::get_function_url($module_name, $controller_name, $function_names, $args, $case_sensitive);
        }
        return $result;
    }


    public function clearCacheByKey($key){
        $cacheService = new CacheService();

        return $cacheService->clearCacheByKey($key);
    }
}
