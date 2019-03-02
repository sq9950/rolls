<?php
/**
 * LOG_PATH . 'prefix-'. date("Y-m") . ".log";
 * use 
 * new(file)-> writeLog()
 */
class Log{
	
	private $log_file = null;
	
	public function __construct($log_file){
		$this->log_file = $log_file;
	}
	
	
	 /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    public function writeLog($word='') {
        if(!$this->log_file) return ;
        $fp = fopen($this->log_file,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y-%m-%d %H:%M:%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
	
	//用于记录数据 不自动记录执行日期
	public function recordLog($word=''){
		if(!$this->log_file) return;
		$fp = fopen($this->log_file,"a");
		flock($fp,LOCK_EX);
		fwrite($fp,$word."\n");
		flock($fp,LOCK_UN);
		fclose($fp);
	}
}