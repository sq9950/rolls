<?php
/**
 * Desc: 图片上传到图片服务器服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016-3-29 15:52:48
 */

namespace Service\Common;
class UploadApiService extends \Service\Service{

    public $ret;
    public $curl;
    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
        require_once __LIBRARY__. '/Curl.php';
        $this->curl = new \Curl();
        $this->curl->set_request_option('CURLOPT_TIMEOUT', 5); //超时控制
    }


    public function initModels(){
        $this->models = new \stdClass();

    }

    /**
     * 上传图片
     * @param array $files
     * @return bool|\CurlResponse
     */
    public function uploadFiles($files = array()){
        $request_method = 'post';
        $request_url = 'http://images.yundun.vm/uploadImage';
        $request_params = array('files' => $files);
        $res = $this->curl->request($request_method, $request_url, $request_params);
        return $res;
    }

}