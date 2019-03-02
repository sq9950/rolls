<?php

namespace Controller\Web;

class Common extends \Controller\ControllerWeb {
	public function header() {
        $this->view->assign("title", "劳斯莱斯");
		return $this->view->fetch("Web/Common/header.html");
	}
	
    public function footer() {
        return $this->view->fetch('Web/Common/footer.html');
    }

    public function cookie() {
        return $this->view->fetch('Web/Common/cookie.html');
    }

    public function letSlide() {
        return $this->view->fetch('Web/Common/letSlide.html');
    }

    public function wechatPopFooter() {
        return $this->view->fetch('Web/Common/wechat-pop-footer.html');
    }

    public function wechatPopPage() {
        return $this->view->fetch('Web/Common/wechat-pop-page.html');
    }

}

