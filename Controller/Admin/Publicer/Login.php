<?php
namespace Controller\Admin\Publicer;
use Service\Common\PasswordService;
class Login extends \Controller\Controller{

    private $verify_code_key;   //验证码session键名
    private $userService;
    public function __construct() {
        parent::__construct();
        $this->verify_code_key = C('VERIFY_CODE_KEY') ? C('VERIFY_CODE_KEY') : 'adminV3Verify';
        $this->userService = new \Service\Common\UserService();
    }

    public function index()
    {
//        unset($_SESSION[C('USER_AUTH_KEY')]);

        //如果通过认证跳转到首页
        if($this->_isLogin()){
            $this->redirect('/');
        }

        $host=$_SERVER['HTTP_HOST'];

        if($host=="yunduncrm.com"){
            $title="云盾CRM管理系统";
        }else{
            $title="云盾后台管理系统";
        }

        $this->view->assign("title",$title);

        $this->view->display('Admin/Public/login.html');
    }


    public function yzm(){
        return $this->buildVerifyCode();
    }

    // 用户登出
    public function logout()
    {
        $log_params['message'] = '退出登录';
        $log_params['keyword'] = 'user_logout';
        $log_params['method'] = self::METHOD_TYPE_POST;
        $this->saveLog($log_params);
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);
            session_destroy();
        }

        $this->redirect(C('USER_AUTH_GATEWAY'));
    }

    /**
     * 登录验证
     *  1. 验证码验证
     *  2. 用户名验证
     *  3. 登录错误次数验证
     *  4. 密码验证
     *  5. 用户状态验证
     */
    public function check_login(){
        if( md5($_POST['yzm']) != $_SESSION[$this->verify_code_key] ){

            $log_params = [
                'account' =>   $_POST['account'],
                'message' =>   '后台登录失败：验证码错误！'
            ];
            $this->saveLog($log_params);
            $this->loginAjaxReturn(array('status' => 0, 'info' => '验证码错误!'));
        } else {
            $user_model = new \Model\User();
            $where['account'] = trim($_POST['account']);
            $info = $user_model->find($where);
            if(empty($info)){
                $log_params = [
                    'account' =>   $_POST['account'],
                    'message' =>   '后台登录失败：用户名不存在！'
                ];
                $this->saveLog($log_params);
                $this->loginAjaxReturn(array('status' => 0, 'info' => '用户名或密码错误'));
            } else {
                $error_passwd = $this->userService->checkErrorPasswd($_POST['account']);
                if(1 != $error_passwd['status']){
                    $this->loginAjaxReturn($error_passwd);
                }
                $log_params = [
                    'id' => $info['id'],
                    'nickname' => $info['nickname'],
                    'keyword' => 'user_login',
                    'method' => self::METHOD_TYPE_POST
                    ];
                if( $info['password'] != PasswordService::getEncryPassword($_POST['password']) ){
                    $this->userService->updateErrorPasswd($info['account']);
                    $log_params['message'] = '后台登录失败：密码不正确！';
                    $this->saveLog($log_params);
                    $this->loginAjaxReturn(array('status' => 0, 'info' => '用户名或密码错误！'));
                } else {
                    $userService = $this->userService;
                    if($userService::USER_STATUS_OPEN != $info['status']){
                        $log_params['message'] = '后台登录失败：用户已被禁用！';
                        $this->saveLog($log_params);
                        $this->loginAjaxReturn(array('status' => 0, 'info' => '用户已被禁用'));
                    }
                    $_SESSION[C('USER_AUTH_KEY')] = $info;
                    $_SESSION['CHECK_CODE']        = md5(date("YmdHis").(isset($info['account']) ? $info['account'] : ''));
                    $_SESSION['IP_AGENT']      = md5(get_real_ip().$_SERVER['HTTP_USER_AGENT']);
                    $this->save_user_log('user','login','<a>登陆</a>后台');
                    $log_params['message'] = '后台登录';
                    $this->saveLog($log_params);
                    $this->userService->updateLastLogin();
                    $this->userService->clearErrorPasswd($info['account']);
                    $this->loginAjaxReturn(array('status' => 1, 'info' => '信息正确'));
                }
            }
        }
    }

    private function save_user_log($table,$type,$message){
        $data["user_id"] = $_SESSION[C("USER_AUTH_KEY")]['id'];
        //$data["ip"]      = $_SERVER['REMOTE_ADDR'];
        $data["ip"]      = get_real_ip();
        $data["datetime"]= date("Y-m-d H:i:s");
        $data["message"] = $message;
        $data["table"]   = $table;
        $data["type"]    = $type;

        $userLog_model = new \Model\UserLog();
        $userLog_model->add($data);
    }

    private function loginAjaxReturn($ret = array()){
        $this->buildVerifyCode(false);
        $this->ajaxReturn($ret);
    }

    /**
     * 生成验证码
     * @param bool $output
     * @return string
     */
    private function buildVerifyCode($output = true){
        include_once(__LIBRARY__.'/Util/Yzm.class.php');
        return \Image::buildImageVerify(5,1,"png",120,28, $this->verify_code_key, $output);
    }
}