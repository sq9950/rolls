<?php
namespace Controller\Web;

use Controller\ControllerWeb;

class Index extends ControllerWeb {

    public $versionLogService;

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $layout_params = [
            'header' => '',
            'footer' => '',
            'slide'  => ['index']
        ];
        $this->setHeaderFooter($layout_params);
        $this->view->display('Web/zh-CN/home.html');
    }

}
