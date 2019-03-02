<?php
/**
 * Desc: 导出服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016/6/29 10:59
 */

namespace Service\Common;
class ExportService extends \Service\Service{

    public function __construct($queueService = ''){
        parent::__construct();
    }

    public function initModels(){

    }

    /**
     * 导出excel文件
     * @param array  $data
     * @param string $file_name
     */
    public function exportTxt($data = [], $file_name = ''){
        foreach($data as $key => $val){
            $content_arr[] = implode("\t\t\t\t", $val);
        }
        $content = implode("\r\n", $content_arr);
        $encode = 'gb2312';
        empty($file_name) && $file_name = 'export_'. date('Y_m_d', time()).'.txt';
        header("Content-Type: application/vnd.ms-excel; charset=" . $encode);
        header("Content-Disposition: inline; filename=\"" . $file_name . "\"");
        header("Pragma:no-cache");
        echo mb_convert_encoding($content, 'gb2312');
        exit();
    }

    /**
     * 导出excel文件
     * @param array  $data
     * @param string $file_name
     * @param string $encode
     */
    public function exportXls($data = [], $file_name = '',  $encode = 'gb2312'){
        $xls_text = $this->_dealDataForXls($data);
        
        empty($file_name) && $file_name = 'exportXls_'. date('Y_m_d', time()).'.xls';
        header("Content-Type: application/vnd.ms-excel; charset=" . $encode);
        header("Content-Disposition: inline; filename=\"" . $file_name . "\"");
        header("Pragma:no-cache");
        echo mb_convert_encoding($xls_text, $encode);
        exit();
    }

    /**
     * 格式化数据为xls文本格式
     * @param array $data
     * @return string
     */
    private function _dealDataForXls($data = []){
        $str = '';
        if(is_array($data) && !empty($data)){
            foreach($data as $one){
                foreach($one as $k => $v){
                    if(is_string($v) && !strlen($v)){
                        $one[$k] = 'unknown';
                    }else{
                        $one[$k] = str_replace(array('\n', '\r\n'), array('', ''), $v);
                    }
                }
                $str .= implode("\t", $one)."\n";
            }
        }
        return $str;
    }
}

