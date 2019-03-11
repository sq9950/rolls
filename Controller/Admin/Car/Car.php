<?php
namespace Controller\Admin\Car;

use \Model\Car\CarLslsModel;
use Library\Qiniu\Auth;
use Library\Qiniu\Storage\UploadManager;
use \Ypf\Lib\Config as Cfg;

class Car extends \Controller\Admin\Common\Common {

    private $_carLslsModel;

    public function __construct(){
        parent::__construct();
        $this->_carLslsModel = new CarLslsModel();
    }

	public function index() {
        $this->setHeaderFooter();

        $actions['list']   = \Url::get_function_url('car', 'car', 'list',   [], true);
        $actions['save']   = \Url::get_function_url('car', 'car', 'save',   [], true);
        $actions['stop']   = \Url::get_function_url('car', 'car', 'stop',   [], true);
        $actions['edit']   = \Url::get_function_url('car', 'car', 'detail', [], true);
        $actions['delete'] = \Url::get_function_url('car', 'car', 'delete', [], true);
        $actions['upload'] = \Url::get_function_url('car', 'car', 'upload', [], true);
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Car/index.html');
	}

    public function list() {
        $cars = $this->_carLslsModel->gets([]);
        foreach($cars as &$car) {
            $car['status_bool'] = $car['status'] ? true : false;
            $url = $car['cfg_pdf'] ? 'http://jingwupublic.qiniudn.com/'.str_replace('content/', 'rolls/', $car['cfg_pdf']) : '';
            $car['cfg_pdf_jw'] = $url;
            $car['cfg_pdf_bool'] = $url ? true : false;
        }
        $this->_RD($cars);
    }

    public function detail() {
        $id = isset($this->req['id']) ? ($this->req['id']) : 0;
        $car = $this->_carLslsModel->get(['id' => $id]);
        $car ? $this->_RD($car) : $this->_RC('数据不存在');
    }

    public function save() {
        $id = isset($this->req['id']) ? intval($this->req['id']) : 0;
        $data = [];
        if(isset($this->req['name_en']))       $data['name_en']       = trim($this->req['name_en']);
        if(isset($this->req['name_zh']))       $data['name_zh']       = trim($this->req['name_zh']);
        if(isset($this->req['to_airpot_day'])) $data['to_airpot_day'] = trim($this->req['to_airpot_day']);
        if(isset($this->req['to_store_day']))  $data['to_store_day']  = trim($this->req['to_store_day']);
        if(isset($this->req['cfg_pdf']))       $data['cfg_pdf']       = trim($this->req['cfg_pdf']);

        if($id) {
            $result = $this->_carLslsModel->updateById($id, $data);
        } else {
            $result = $this->_carLslsModel->add($data);
        }
        $result ? $this->_RD([]) : $this->_RC('操作失败');
    }

    public function stop() {
        $id = $this->req['id'];
        $status = $this->req['status'];
        $result = $this->_carLslsModel->updateById($id, ['status' => $status]);
        $result ? $this->_RD([]) : $this->_RC('操作失败');
    }

    public function delete() {
        $id = $this->req['id'];
        $result = $this->_carLslsModel->delete(['id' => $id]);
        $result ? $this->_RD([]) : $this->_RC('操作失败');
    }

    public function upload() {
        $finfo = $_FILES['cfg_pdf_file'];
        $fkey = 'rolls/dam/rollsroyce-website/cfg_pdf/cfg_pdf_'.date('YmdHis').'.'.$finfo['name'];
        $content = file_get_contents($finfo['tmp_name']);
        require __LIBRARY__ . '/Qiniu/autoload.php';
        $cfg = Cfg::getInstance()->get('QINIU_CONFIG');

        $auth = new Auth($cfg['accessKey'], $cfg['secretKey']);
        $token = $auth->uploadToken($cfg['bucket']);

        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->put($token, $fkey, $content);
        $err ? $this->_RC("上传失败: {$err}") : $this->_RD(str_replace('rolls/', 'content/', $fkey));
    }

}
