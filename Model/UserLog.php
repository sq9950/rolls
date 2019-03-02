<?php
namespace Model;
class UserLog extends \Model\CommonModel{

	public function __construct(){
		parent::__construct();
        $this->table_name = 'user_log';
	}


}