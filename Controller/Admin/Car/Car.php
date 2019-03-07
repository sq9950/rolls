<?php
namespace Controller\Admin\Car;

use \Model\Car\CarLslsModel;

class Car extends \Controller\Admin\Common\Common {

    private $_carLslsModel;

    public function __construct(){
        parent::__construct();
        $this->setHeaderFooter();
        $this->_carLslsModel = new CarLslsModel();
    }

	public function index() {
        $actions['list'] = \Url::get_action_url('car', 'gets', [], true);
        $actions['save'] = \Url::get_action_url('car', 'save', [], true);
        $actions['stop'] = \Url::get_action_url('car', 'stop', [], true);
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Car/index.html');
	}

    public function gets() {
        $cars = $this->_carLslsModel->gets(['status' => 1]);
        var_dump($cars); exit();
        $this->ajaxReturn($cars);
    }

}
