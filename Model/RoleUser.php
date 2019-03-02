<?php
namespace Model;
class RoleUser extends \Model\CommonModel{

	public function __construct(){
		parent::__construct();
        $this->table_name = 'rbac_role_user';
	}


}