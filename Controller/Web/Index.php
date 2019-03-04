<?php
namespace Controller\Web;

use Controller\ControllerWeb;

class Index extends ControllerWeb {

    public $versionLogService;

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $htmlpath = isset($this->get['htmlpath']) ? $this->get['htmlpath'] : $this->request->server['REQUEST_URI'];
        $layout_params = [
            'header' => '',
            'footer' => '',
            'slide'  => ['index']
        ];
        $this->setHeaderFooter($layout_params);
        $tpl = 'home.html';
        if($htmlpath != '/') $tpl = $htmlpath;
        $tplFile = sprintf("%s/Web/Common/main/%s", __VIEW__, $tpl);
        if(!file_exists($tplFile)) $tpl = 'home.html';
        $this->view->assign('main_home', $this->view->fetch(sprintf('Web/Common/main/%s', $tpl)));
        $this->view->display('Web/zh-CN/home.html');
    }

}
