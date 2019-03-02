<?php
namespace Model;
class MemberLog extends \Model\CommonModel{

	public function __construct(){
		parent::__construct();
        $this->table_name = 'member_log';
	}


}