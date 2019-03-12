<?php

namespace Controller;

use Service\Cache\CacheService;
use Service\Common\DomainCommonService;

class ControllerWeb extends Controller
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
        $result = $this->getActions();
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
        $header_args = isset($params['header']) && is_array($params['header']) ? $params['header'] : [];
        $footer_args = isset($params['footer']) && is_array($params['footer']) ? $params['footer'] : [];
        $cookie_args = isset($params['cookie']) && is_array($params['cookie']) ? $params['cookie'] : [];
        $letSlide_args = isset($params['letSlide']) && is_array($params['letSlide']) ? $params['letSlide'] : [];
        $wechat_pop_footer_args = isset($params['wechat-pop-footer']) && is_array($params['wechat-pop-footer']) ? $params['wechat-pop-footer'] : [];
        $wechat_pop_page_args   = isset($params['wechat-pop-page']) && is_array($params['wechat-pop-page']) ? $params['wechat-pop-page'] : [];
        $main_home_args   = isset($params['main_home']) && is_array($params['main_home']) ? $params['main_home'] : [];

        $this->view->assign('header', $this->action("\Controller\Web\Common\header",  $header_args));
        $this->view->assign('footer', $this->action("\Controller\Web\Common\\footer", $footer_args));
        $this->view->assign('cookie', $this->action("\Controller\Web\Common\cookie",  $cookie_args));
        $this->view->assign('letSlide', $this->action("\Controller\Web\Common\letSlide", $letSlide_args));
        $this->view->assign('wechat_pop_footer', $this->action("\Controller\Web\Common\wechatPopFooter", $wechat_pop_footer_args));
        $this->view->assign('wechat_pop_page', $this->action("\Controller\Web\Common\wechatPopPage", $wechat_pop_page_args));
        //$this->view->assign('main_home', $this->action("\Controller\Web\Common\mainHome", $main_home_args));
        $this->view->assign('public1', $this->action("\Controller\Web\Common\public1", []));
        $this->view->assign('stock', $this->action("\Controller\Web\Common\stock", []));
    }

    /**
     * 网站全局配置
     */
    private function _preAssign() {
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
