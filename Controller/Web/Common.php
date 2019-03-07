<?php
namespace Controller\Web;

use \Model\Car\CarLslsModel;

class Common extends \Controller\ControllerWeb {

    private $_carLslsModel;

    public function __construct() {
        parent::__construct();
        $this->_carLslsModel = new CarLslsModel();
    }

	public function header() {
        $this->view->assign("title", "劳斯莱斯");
		return $this->view->fetch("Web/Common/public/header.html");
	}
	
    public function footer() {
        return $this->view->fetch('Web/Common/public/footer.html');
    }

    public function cookie() {
        return $this->view->fetch('Web/Common/public/cookie.html');
    }

    public function letSlide() {
        $cars = $this->_carLslsModel->gets(['status' => 1]);
        $this->view->assign('cars', $cars);
        return $this->view->fetch('Web/Common/public/letSlide.html');
    }

    public function wechatPopFooter() {
        return $this->view->fetch('Web/Common/public/wechat-pop-footer.html');
    }

    public function wechatPopPage() {
        return $this->view->fetch('Web/Common/public/wechat-pop-page.html');
    }

    public function mainHome() {
        return $this->view->fetch('Web/Common/main/home.html');
    }

    public function public1() {
        return $this->view->fetch('Web/Common/public/1.html');
    }

}

