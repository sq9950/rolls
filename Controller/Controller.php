<?php

namespace Controller;

use Ypf\Lib\Config;
use Service\Sdk\Sdk;
use GuzzleHttp\Client;
use Service\Log\LogService;
use Ypf\Core\Controller as BaseController;

class Controller extends BaseController
{
    const METHOD_TYPE_GET  = 2; //GET方法
    const METHOD_TYPE_POST = 8; //POST方法

    public $module_name;
    public $controller_name;
    public $function_name;
    public $post;
    public $get;
    public $req;

    public function __construct()
    {
        parent::__construct();

        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

        defined('REQUEST_METHOD') or define('REQUEST_METHOD', $method);

        defined('IS_GET') or define('IS_GET', $method === 'GET');
        defined('IS_POST') or define('IS_POST', $method === 'POST');
        defined('IS_PUT') or define('IS_PUT', $method === 'PUT');
        defined('IS_DELETE') or define('IS_DELETE', $method === 'DELETE');

        $this->post = !empty($this->request->post) ? $this->request->post : [];
        $this->get  = !empty($this->request->get) ? $this->request->get : [];
        $this->req  = array_merge($this->get, $this->post);
    }

    /**
     * 是否已登录
     *
     * @return boolean
     */
    public function _isLogin()
    {
        if (!isset($_SESSION)) {
            return false;
        }

        $key = $this->configall['USER_AUTH_KEY'];

        return isset($_SESSION[$key]) && isset($_SESSION[$key]['id']);
    }

    /**
     * 是否AJAX请求
     *
     * @return boolean
     */

    protected function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
        }

        return false;
    }


    /**
     * is pjax request
     */
    protected function isPjax()
    {
        foreach (['HTTP_X_PJAX', 'X_PJAX'] as $key) {
            if (isset($_SERVER[$key]) && $_SERVER[$key]) {
                return true;
            }
        }

        return false;
    }

    /**
     * 有不存在的请求时调用
     * isPost() ...
     */
    public function __call($method, $args = null)
    {
        switch (strtolower($method)) {
            case 'ispost':
            case 'isget':
            case 'ishead':
            case 'isdelete':
            case 'isput':
                return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method, 2));
            default:
                die("controller {$this->getRoute()} function ( $method ) not exist...");
        }
    }

    /*
         * redirect
    */
    protected function redirect($url, $delay = 0, $msg = '', $params = array())
    {
        redirect($url, $delay, $msg);
    }

    /**
     * get route
     */
    protected function getRoute()
    {
        return isset($this->request->get['route']) ? $this->request->get['route'] : '';
    }

    /**
     *
     */
    protected function getAction()
    {
        $route = $this->getRoute(); //\Controller\Home\Account\Index\index
        if ($route) {
            $className = get_class($this); //Controller\Home\Account\Index
            $pos       = strripos($route, '\\');
            return substr($route, $pos + 1);
        }
    }


    public function isMethod($method)
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
        }

        return false;
    }

    public function isPost()
    {
        return $this->isMethod('POST');
    }

    public function isGet()
    {
        return $this->isMethod('GET');
    }

    public function isHead()
    {
        return $this->isMethod('HEAD');
    }

    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    public function isPut()
    {
        return $this->isMethod('PUT');
    }


    /**
     * 获取当前操作的模块、控制器、方法名
     * @return array
     */
    protected function getActions()
    {
        $res   = array();
        $route = $this->getRoute(); //\Controller\Home\Account\Index\index
        if ($route) {
            $actions               = explode('\\', $route);
            $this->function_name   = array_pop($actions);
            $this->controller_name = array_pop($actions);
            $this->module_name     = array_pop($actions);
            !defined('MODULE_NAME') && define('MODULE_NAME', $this->module_name);
            !defined('CONTROLLER_NAME') && define('CONTROLLER_NAME', $this->controller_name);
            !defined('ACTION_NAME') && define('ACTION_NAME', $this->function_name);
            !defined('FUNCTION_NAME') && define('FUNCTION_NAME', $this->function_name);

        }
        return $res;
    }

    protected function dispatchJump(
        $message,
        $status = 1,
        $jumpUrl = '',
        $waitSecond = 0,
        $ajax = false,
        $closeWin = false
    ) {
        if ($ajax) {
            $data['info']   = $message;
            $data['status'] = $status;
            $data['url']    = $jumpUrl;
            $this->ajax->output($data);
        }
        $this->view->assign('msgTitle',
            $status ? $this->configall['DISPATH_JUMP']['OPERATION_SUCCESS'] : $this->configall['DISPATH_JUMP']['OPERATION_FAIL']);
        $this->view->assign('closeWin', $closeWin);
        $this->view->setTemplateDir(__VIEW_COMMON__); //set template dir
        switch ($status) {
            case 1:
                $this->view->assign('message', $message);
                $this->view->assign('waitSecond',
                    $waitSecond ? $waitSecond : $this->configall['DISPATH_JUMP']['SUCCESS_WAIT_SECOND']);
                $this->view->assign("jumpUrl",
                    $closeWin ? $this->configall['DISPATH_JUMP']['CLOSE_WIN_JUMP_URL'] : ($jumpUrl ? $jumpUrl : $this->configall['DISPATH_JUMP']['SUCCESS_JUMP_URL']));
                $this->view->display($this->configall['DISPATH_JUMP']['TPL_JUMP']);
                break;
            case 0:
                $this->view->assign('error', $message);
                $this->view->assign('waitSecond',
                    $waitSecond ? $waitSecond : $this->configall['DISPATH_JUMP']['ERROR_WAIT_SECOND']);
                $this->view->assign("jumpUrl",
                    $closeWin ? $this->configall['DISPATH_JUMP']['CLOSE_WIN_JUMP_URL'] : ($jumpUrl ? $jumpUrl : $this->configall['DISPATH_JUMP']['ERROR_JUMP_URL']));
                $this->view->display($this->configall['DISPATH_JUMP']['TPL_JUMP']);
                exit;
                break;
        }
    }

    protected function error($message, $jumpUrl = '', $waitSecond = 0, $ajax = false, $closeWin = false)
    {
        $this->dispatchJump($message, 0, $jumpUrl, $waitSecond, $ajax, $closeWin);
    }

    protected function success($message, $jumpUrl = '', $waitSecond = 0, $ajax = false, $closeWin = false)
    {
        $this->dispatchJump($message, 1, $jumpUrl, $waitSecond, $ajax, $closeWin);
    }

    /**
     * Ajax方式返回数据到客户端
     * @author 张鹏玄 | <zhangpengxuan@yundun.com>
     * @time    2015-6-1 09:44:38
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data, $type = 'JSON', $json_option = 0)
    {
        if (empty($type)) {
            $type = C('DEFAULT_AJAX_RETURN');
        }

        switch (strtoupper($type)) {
            case 'JSON':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data, $json_option));
            case 'XML':
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($this->configall['DEFAULT_JSONP_HANDLER']) ? $this->configall['DEFAULT_JSONP_HANDLER'] : 'callback';
                exit($handler . '(' . json_encode($data, $json_option) . ');');
            case 'text':
            case 'html':
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default:
                exit(json_encode($data, $json_option));
        }
    }

    /**
     * @return null
     * @node_name 获取登陆用户id
     * @link
     * @desc
     */
    public function getMemberId()
    {
        return isset($_SESSION[$this->configall['USER_AUTH_KEY']]['id']) ? $_SESSION[$this->configall['USER_AUTH_KEY']]['id'] : null;
    }

    /**
     * @return null
     * @node_name 获取登陆用户信息
     * @link
     * @desc
     */
    public function getMemberInfo()
    {
        return isset($_SESSION[$this->configall['USER_AUTH_KEY']]) ? $_SESSION[$this->configall['USER_AUTH_KEY']] : null;
    }

    /**
     * @param array $params
     * @return bool
     * @node_name 记录操作日志
     * @link
     * @desc
     */
    public function saveLog($params = array())
    {
        $this->getActions();
        $user_info                 = $this->getMemberInfo();
        $nickname                  = isset($user_info['nickname']) ? $user_info['nickname'] : '';
        $init_params['user_id']    = isset($params['user_id']) ? $params['user_id'] : $this->getMemberId();
        $init_params['nickname']   = isset($params['nickname']) ? $params['nickname'] : $nickname;
        $init_params['module']     = isset($params['module']) ? $params['module'] : $this->module_name;
        $init_params['controller'] = isset($params['controller']) ? $params['controller'] : $this->controller_name;
        $init_params['action']     = isset($params['action']) ? $params['action'] : $this->function_name;
        $init_params['method']     = isset($params['method']) ? $params['method'] : self::METHOD_TYPE_POST;

        !isset($params['message']) && $params['message'] = '';
        !isset($params['keyword']) && $params['keyword'] = '';
        !isset($params['obj_user']) && $params['obj_user'] = '';
        !isset($params['domain']) && $params['domain'] = '';
        !isset($params['remark']) && $params['remark'] = '';
        $es_params             = $params;
        $es_params             = array_merge($es_params, $init_params);
        $init_params['params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
        $userLogService        = new LogService($init_params);
        $res                   = $userLogService->save($params['message'], $params['keyword'], $params['obj_user'],
            $params['domain'], $params['remark']);

        return $res;
    }

    /**
     * @param array $params
     * @param string $field
     * @return array
     * @node_name 记录客服操作日志
     * @link
     * @desc
     */
    public function saveSupportLog($params = array(), $field = '')
    {
        $this->getActions();
        $user_info                 = $this->getMemberInfo();
        $init_params               = $params;
        $nickname                  = isset($user_info['nickname']) ? $user_info['nickname'] : '';
        $init_params['user_id']    = isset($params['user_id']) ? $params['user_id'] : $this->getMemberId();
        $init_params['nickname']   = isset($params['nickname']) ? $params['nickname'] : $nickname;
        $init_params['module']     = isset($params['module']) ? $params['module'] : $this->module_name;
        $init_params['controller'] = isset($params['controller']) ? $params['controller'] : $this->controller_name;
        $init_params['action']     = isset($params['action']) ? $params['action'] : $this->function_name;
        $init_params['method']     = isset($params['method']) ? $params['method'] : self::METHOD_TYPE_POST;

        !isset($params['message']) && $params['message'] = '';
        !isset($params['keyword']) && $params['keyword'] = '';
        !isset($params['obj_user']) && $params['obj_user'] = '';
        !isset($params['domain']) && $params['domain'] = '';
        !isset($params['remark']) && $params['remark'] = '';
        $init_params['json_params'] = json_encode($params);

        $logSupportService = new LogSupportService();
        $res               = $logSupportService->addSupportLog($init_params, $field);
        return $res;
    }

    /**
     * @param array $params
     * @return array
     * @node_name 记录用户备注日志
     * @link
     * @desc
     */
    public function saveLogMember($params = array())
    {
        $this->getActions();
        $user_info                 = $this->getMemberInfo();
        $init_params               = $params;
        $nickname                  = isset($user_info['nickname']) ? $user_info['nickname'] : '';
        $init_params['member_id']  = isset($params['id']) ? $params['id'] : 0;
        $init_params['user_id']    = isset($params['user_id']) ? $params['user_id'] : $this->getMemberId();
        $init_params['nickname']   = isset($params['nickname']) ? $params['nickname'] : $nickname;
        $init_params['module']     = isset($params['module']) ? $params['module'] : $this->module_name;
        $init_params['controller'] = isset($params['controller']) ? $params['controller'] : $this->controller_name;
        $init_params['action']     = isset($params['action']) ? $params['action'] : $this->function_name;
        $init_params['method']     = isset($params['method']) ? $params['method'] : self::METHOD_TYPE_POST;

        !isset($params['message']) && $params['message'] = '';
        !isset($params['keyword']) && $params['keyword'] = '';
        !isset($params['obj_user']) && $params['obj_user'] = '';
        !isset($params['domain']) && $params['domain'] = '';
        !isset($params['remark']) && $params['remark'] = '';
        $init_params['json_params'] = json_encode($params);

        $logMemberService = new LogMemberService();
        $res              = $logMemberService->addLogMemberLog($init_params);
        return $res;
    }

    /**
     * @node_name 请求合法验证过滤器
     * @link
     * @desc
     */
    public function operate()
    {
        $operate = $this->req['op'];
        if (method_exists($this, $operate)) {
            $this->$operate();
        } else {
            echo "调用方法({$operate})不存在\r\n";
        }
    }

    /**
     * @param string $url
     * @param array $payload
     * @param string $request_method
     * @return array
     * @node_name 调用adminv5.yundun.cn api  方法
     * @link
     * @desc
     */
    public function sdkv5($url = "", $payload = [], $request_method = "get")
    {
        $sdk = new Sdk;
        $res = $sdk->sdkCommand($url, $payload, $request_method, 'sdk_v5.conf');

        return $res;
    }

    /**
     * @param string $url
     * @param array $payload
     * @param string $request_method
     * @return array|mixed
     * @node_name 调用apiV4 的接口
     * @link
     * @desc
     */
    public function sdkv4($url = "", $payload = [], $request_method = "get")
    {
        $sdk = new Sdk;
        $res = $sdk->sdkCommand($url, $payload, $request_method, 'sdk_v4.conf');

        return $res;
    }

    /**
     * @param $uri
     * @param string $method
     * @param array $options
     * @return \Psr\Http\Message\StreamInterface
     * @node_name guzzle request
     * @link
     * @desc
     *
     */
    public function guzzleRequest($uri, $method = 'post', $options = []){

        //useage::

        // Send a GET request to /get?foo=bar
//        $client->request('GET', '/get', ['query' => ['foo' => 'bar']]);

//        $client->request('POST', '/post', [
//            'form_params' => [
//                'foo' => 'bar',
//                'baz' => ['hi', 'there!']
//            ]
//        ]);

//The body option is used to control the body of an entity enclosing request (e.g., PUT, POST, PATCH)
//        $client->request('PUT', '/put', ['body' => 'foo']);

//The json option is used to easily upload JSON encoded data as the body of a request.
// A Content-Type header of application/json will be added if no Content-Type header is already present on the message.
//        $response = $client->request('PUT', '/put', ['json' => ['foo' => 'bar']]);


// Timeout if a server does not return a response in 3.14 seconds.
//        $client->request('GET', '/delay/5', ['timeout' => 3.14]);

        try{
            $client = new Client();
            $response = $client->request($method, $uri, $options);
            logAdminV3($response->getBody()->getContents());

            return $response->getBody()->getContents();
        }catch (\Exception $e){
            logAdminV3($e->getMessage());
        }

    }

    public function __get($key) {
        return $key == 'configall' ? Config::$config : parent::__get($key);
    }

}
