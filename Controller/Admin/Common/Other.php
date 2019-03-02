<?php
namespace Controller\Admin\Common;
class Other extends \Controller\Admin\Common\Common{
	public function __construct(){
		parent::__construct();
	}
	
	//msgbox /msgbox
	public function msgbox(){
		$this->view->display('Public/msgbox.html');
	}
	
	//feedback
	public function feedback(){
		$this->view->display('Public/feedback.html');
	}
	
}