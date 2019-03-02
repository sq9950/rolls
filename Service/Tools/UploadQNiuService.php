<?php
/**
 * Desc: 图片上传服务类
 * Created by PhpStorm.
 * User: jasong
 * Date: 2015/7/29
 */

namespace Service\Tools;

// 引入鉴权类
use Library\Qiniu\Auth;

// 引入上传类
use Library\Qiniu\Storage\UploadManager;

class UploadQNiuService extends \Service\Service {

    public $ret;

    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
    }

    public function initModels() {

    }

    /**
     * 二进制上传
     * @param array $params
     * @return array
     */
    public function uploadByQiNiu($params = array()){
//        $params = array(
//            'bucket' => 'xx',
//            'file_name_suffix' => 'xx',
//            'picContent' => 'xx',
//        );

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
        $token = $auth->uploadToken($bucket);

        //生成图片的文件名
        if(isset($params['file_name_suffix']) && !empty($params['file_name_suffix'])){
            $file_name_suffix = "-".$params['file_name_suffix'];
        }else{
            $file_name_suffix = '';
        }

        //二进制内容
        $data = isset($params['picContent'])?$params['picContent']:'';
        if(empty($data)){
            $this->ret = array('status' => 0, 'info' => '图片内容不能为空');
            return $this->ret;
        }

        // 上传到七牛后保存的文件名
        $key = uniqid(time()). "$file_name_suffix";

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 put 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->put($token, $key, $data);
        if(!empty($err)){
            return array('status' => 0, 'info' => '上传失败：'. $err);
        }else{
            $ret['key'] = $qiniu_base_url . '/' . $ret['key'];
            $this->ret = array('status' => 1, 'info' => '上传成功', 'data' => $ret);
        }
        return $this->ret;
    }

}