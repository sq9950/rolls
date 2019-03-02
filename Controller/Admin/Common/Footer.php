<?php
namespace Controller\Admin\Common;

class Footer extends \Controller\Admin\Common\Common
{
    public function index()
    {

        return $this->view->fetch('Admin/Public/footer.html');
    }

}
