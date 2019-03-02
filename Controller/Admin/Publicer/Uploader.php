<?php
namespace Controller\Admin\Publicer;
/**
 * @node_name 公共上传
 * Desc: 功能描述
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016/7/13 18:16
 */


use Service\Tools\UploadQNiuService;

class Uploader extends \Controller\Controller{

    protected $uploadQNiuService;

    /**
     * @node_name 上传图片
     */
    public function uploadProfileImg(){
        if(empty($_FILES)){
            $this->ajaxReturn(['status' => 0, 'info' => '上传失败']);
        }
        $this->uploadQNiuService = new UploadQNiuService();
        $file_info = array_pop($_FILES);
        $params = [
            'file_name_suffix' => $file_info['name'],
            'picContent' => file_get_contents($file_info['tmp_name'])
        ];
        $res = $this->uploadQNiuService->uploadByQiNiu($params);
        $this->ajaxReturn($res);

    }
}
