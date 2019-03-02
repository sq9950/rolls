<?php
namespace Model;
class SendLog extends \Model\CommonModel{

	public function __construct(){
		parent::__construct();
        $this->table_name = 'send_log';
	}


}