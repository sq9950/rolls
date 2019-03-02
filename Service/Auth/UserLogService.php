<?php

/**
 * Desc: 导出日志默认1000条，根据时间，和关键字搜索条件
 * Created by PhpStorm.
 * User: <gaolu@yundun.com>
 * Date: 2015/12/15 12:23
 */
namespace Service\Auth;

class UserLogService extends \Service\Service{

    public $models_operateLog;

    public function __construct(){
        parent::__construct();
        $this->initModels();
    }

    public function initModels(){
        $this->models_operateLog = new \Model\AdminOperateLog();
    }


    public function getExportLogData($startTime, $endTime, $limit = 10000){

        $startTime && $where['addtime'] = array('egt', $startTime);
        $endTime && $where['`addtime`'] = array('elt', $endTime);

        $order = array('id' => 'desc');
        $fields = array('id', 'nickname', 'message', 'addtime', 'ip');
        $res = $this->models_operateLog->getListByWhere($where, 0, $limit, $order, $fields);
        $data = $this->_dealDataForExport($res);
        $result = array(
            'excel_data' => $data,
            'last_sql' => $this->models_operateLog->getLastSql()
        );
        return $result;
    }

    private function _dealDataForExport($res){
        $str = '';
        $title = array('ID', '操作者', '详情', '操作时间', 'IP');
        $str .= implode("\t", $title)."\n";
        foreach($res as $r){
            foreach($r as $k => $v){
                if(empty($v)){
                    $r[$k] = 'unknown';
                }else{
                    $r[$k] = str_replace(array('\n', '\r\n'), array('', ''), $v);
                }

            }
            $str .= implode("\t", $r)."\n";
        }

        return $str;
    }

    /**
     * 导出excel
     * @param $res
     */
    public function export($res){
        $encode = 'gb2312';
        $filename = 'auth_log_'. date('Y_m_d', time()).'.xls';
        header("Content-Type: application/vnd.ms-excel; charset=" . $encode);
        header("Content-Disposition: inline; filename=\"" . $filename . "\"");
        header("Pragma:no-cache");
//        echo iconv('utf-8', 'gb2312', $res);
        echo mb_convert_encoding($res, 'gb2312');
        exit();
    }

    public function exportAuthLog($params = array()){
        $result  = $this->getExportLogData($params['startTime'], $params['endTime']);
        $this->export($result['excel_data']);
    }
}