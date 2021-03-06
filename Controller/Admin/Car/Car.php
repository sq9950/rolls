<?php
namespace Controller\Admin\Car;

use \Model\Car\CarLslsModel;
use Library\Qiniu\Auth;
use Library\Qiniu\Storage\UploadManager;
use \Ypf\Lib\Config as Cfg;

class Car extends \Controller\Admin\Common\Common {

    private $_carLslsModel;
    static public $qiniuDomain = 'http://jingwupublic.qiniudn.com/';

    public function __construct(){
        parent::__construct();
        $this->_carLslsModel = new CarLslsModel();
    }

    private function _actions() {
        $actions = [];
        $actions['list']   = \Url::get_function_url('car', 'car', 'list',   [], true);
        $actions['save']   = \Url::get_function_url('car', 'car', 'save',   [], true);
        $actions['stop']   = \Url::get_function_url('car', 'car', 'stop',   [], true);
        $actions['edit']   = \Url::get_function_url('car', 'car', 'edit',   [], true);
        $actions['delete'] = \Url::get_function_url('car', 'car', 'delete', [], true);
        $actions['upload'] = \Url::get_function_url('car', 'car', 'upload', [], true);
        return $actions;
    }

	public function index() {
        $this->setHeaderFooter();

        $this->view->assign('actions', $this->_actions());
        $this->view->display('Admin/Car/index.html');
	}

	public function edit() {
        $id = isset($this->req['id']) ? ($this->req['id']) : 0;
        $car = $this->_carLslsModel->get(['id' => $id]);
        $car = $car ? $car : [];
        $car['cfg_pdf_url'] = $car['cfg_pdf'] ? self::$qiniuDomain.str_replace('content/', 'rolls/', $car['cfg_pdf']) : '';

        $this->view->assign('actions', $this->_actions());
        $this->view->assign('car', $car);
        $this->view->display('Admin/Car/car_edit.html');
	}

    public function list() {
        $cars = $this->_carLslsModel->gets([]);
        foreach($cars as &$car) {
            $car['status_bool'] = $car['status'] ? true : false;
            $car['cfg_pdf_url'] = $car['cfg_pdf'] ? self::$qiniuDomain.str_replace('content/', 'rolls/', $car['cfg_pdf']) : '';
            $car['cfg_pdf_bool'] = $car['cfg_pdf_url'] ? true : false;
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
        if(isset($this->req['remark']))        $data['remark']        = trim($this->req['remark']);

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
        $err ? $this->_RC("上传失败: {$err}") : $this->_RD(['cfg_pdf_url' => self::$qiniuDomain.$fkey, 'cfg_pdf' => str_replace('rolls/', 'content/', $fkey)]);
    }

}
