<?php
/**
 * Desc: 图片上传服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/29
 */

namespace Service\Common;

// 引入鉴权类
use Library\Qiniu\Auth;

// 引入上传类
use Library\Qiniu\Storage\UploadManager;

class UploadService extends \Service\Service{

    public $ret;

    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }


    public function initModels(){
        $this->models = new \stdClass();
        $this->models->user                        = new \Model\User();
    }

    public function upload($files = array()){
        if(empty($files)){
            $this->ret['info'] = '必须选择上传文件';
        }else{
            include_once(__LIBRARY__.'/UploadFile.class.php');
            $upload = new \UploadFile();// 实例化上传类
            $upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->allowTypes=array('image/png','image/jpg','image/jpeg','image/gif','application/octet-stream');//检测mime类型
            $upload->autoSub = true;
            $upload->subType = 'date';
            $upload->dateFormat = 'Ymd';
            $upload->savePath =  './uploads/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                $this->ret['info'] = $upload->getErrorMsg();
            }else{// 上传成功 获取上传文件信息

                $info =  $upload->getUploadFileInfo();
                $logService = new \Service\Log\LogService();
                $logService->save_user_log("index_article","upload","上传文件:<a>"."/uploads/".$info[0]["savename"]."</a>");
                $this->ret = array('status' => 1, 'info' => '上传成功', 'data' => array('error'=>0 , 'url'=>"/uploads/".$info[0]["savename"]));
            }
        }

        return $this->ret;

    }

    public function uploadByQiNiu($params = array()){
        if(!isset($params['file'])){
            return array('status' => 0, 'info' => '上传失败：未指定上传图片');
        }elseif(!is_file($params['file'])){
            return array('status' => 0, 'info' => '上传失败：上传图片不存在');
        }
        $this->ret = array('status' => 0, 'info' => '上传失败');
        require_once __LIBRARY__ . '/Qiniu/autoload.php';
        // 需要填写你的 Access Key 和 Secret Key

        $accessKey = $this->configall['QINIU_CONFIG']['accessKey'];
        $secretKey = $this->configall['QINIU_CONFIG']['secretKey'];
        $qiniu_bucket_domain = $this->configall['QINIU_CONFIG']['bucket_domain'];
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        if(!isset($params['bucket']) || empty($params['bucket']) || !in_array($params['bucket'], array_keys($qiniu_bucket_domain))){
            $bucket_list = array_keys($qiniu_bucket_domain);
            $bucket = array_shift($bucket_list);
        }else{
            $bucket = $params['bucket'];
        }
        if(!isset($qiniu_bucket_domain[$bucket]) || empty($qiniu_bucket_domain[$bucket])){
            return ['status' => 0, 'info' => '未设置七牛bucket的默认域名'];
        }else{
            $pre_list = array_keys($qiniu_bucket_domain[$bucket]);
            $domain_pre = array_shift($pre_list);
            $domain_list = array_values($qiniu_bucket_domain[$bucket][$domain_pre]);
            $domain = array_shift($domain_list);
        }
        $qiniu_base_url = $domain_pre . "://" . $domain;

        // 生成上传 Token
        $policy = [
            'saveKey' => date('YmdHis') . "-$(key)",    //自定义文件名（？）
        ];
        $token = $auth->uploadToken(
            $bucket,
            null,
            3600,
            $policy
        );

        // 要上传文件的本地路径
        $filePath = $params['file'];

        if(!isset($params['file_name']) || empty($params['file_name'])){
            $file_name = basename($filePath);
        }else{
            $file_name = basename($params['file_name']);
        }
        $file_name = str_replace(['\\', '/', ':'], '', $file_name);
        // 上传到七牛后保存的文件名
        $key = uniqid(time()). "-{$file_name}";

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if(!empty($err)){
            return array('status' => 0, 'info' => '上传失败：'. $err->message());
        }else{
            $ret['key'] = $qiniu_base_url . '/' . $ret['key'];
            $this->ret = array('status' => 1, 'info' => '上传成功7', 'data' => $ret);
        }
        return $this->ret;
    }

    /**
     * 图片上传
     */
    public function commonUpload(){
        $this->ret = $this->upload($_FILES);
        if(1 == $this->ret['status']){
            $data = $this->ret['data'];
            if(0 == $data['error'] && !empty($data['url'])){
                $params = array(
                    'file' => __ROOT__. '/Public' . $data['url'],
                    'file_name' => isset($_POST['localUrl']) ? $_POST['localUrl'] : ''
                );
                $res = $this->uploadByQiNiu($params);
                if(1 != $res['status']){
                    $this->ret = $res;
                }else{
                    $data['url'] = $res['data']['key'];
                }
            }
        }else{
            $data = array('error' => 1, 'message' => $this->ret['info']);
        }
        return $data;
    }
}