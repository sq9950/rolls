<?php
/**
 * Desc: password服务类
 * Created by PhpStorm.
 */

namespace Service\Common;
class PasswordService extends \Service\Service{

    const PASSWORD_PRE = 'Yundun#8GtFJfj27#';
    public function __construct(){
        parent::__construct();
        $this->initModels();

    }

    public function initModels(){
        $this->models = new \stdClass();
        $this->models->member                   = new \Model\Member() ;
        $this->models->user                     = new \Model\User() ;
    }

    /**
     * 获取加密后的密码
     * @param string $password
     * @return string
     */
    static public function getEncryPassword($password = ''){
        $password_md5 = md5(self::PASSWORD_PRE . $password);
        $password_8_8 = substr($password_md5, 8, 8);
        $password_crypt = crypt($password_8_8, self::PASSWORD_PRE);   //crypt只影响前8位
        $password = md5(self::PASSWORD_PRE . $password_crypt . $password_md5);
        return $password;
    }


}