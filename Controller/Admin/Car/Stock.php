<?php
namespace Controller\Admin\Car;

use \Model\Car\CarStockModel as StockModel;
use Library\Qiniu\Auth;
use Library\Qiniu\Storage\UploadManager;
use \Ypf\Lib\Config as Cfg;

class Stock extends \Controller\Admin\Common\Common {

    private $_stockModel;
    static public $qiniuDomain = 'http://jingwupublic.qiniudn.com/';

    public function __construct(){
        parent::__construct();
        $this->_stockModel = new StockModel();
    }

    private function _actions() {
        $actions = [];
        $actions['list']   = \Url::get_function_url('car', 'stock', 'list',   [], true);
        $actions['save']   = \Url::get_function_url('car', 'stock', 'save',   [], true);
        $actions['stop']   = \Url::get_function_url('car', 'stock', 'stop',   [], true);
        $actions['edit']   = \Url::get_function_url('car', 'stock', 'edit', [], true);
        $actions['delete'] = \Url::get_function_url('car', 'stock', 'delete', [], true);
        $actions['upload'] = \Url::get_function_url('car', 'stock', 'upload', [], true);
        return $actions;
    }

	public function index() {
        $this->setHeaderFooter();

        $this->view->assign('actions', $this->_actions());
        $this->view->display('Admin/Car/stock.html');
	}

    public function edit() {
        $id = isset($this->req['id']) ? ($this->req['id']) : 0;
        $stock = $this->_stockModel->get(['id' => $id]);
        $stock = $stock ? $stock : [];

        $this->view->assign('actions', $this->_actions());
        $this->view->assign('stock', $stock);
        $this->view->display('Admin/Car/stock_edit.html');
    }

    public function list() {
        $cars = $this->_stockModel->gets([]);
        foreach($cars as &$car) {
            $car['display_bool'] = $car['display'] ? true : false;
            $car['cfg_pdf_url']  = $car['cfg_pdf'] ? 'http://jingwupublic.qiniudn.com/'.str_replace('content/', 'rolls/', $car['cfg_pdf']) : '';
            $car['cfg_pdf_bool'] = $car['cfg_pdf_url'] ? true : false;
        }
        $this->_RD($cars);
    }

    public function detail() {
        $id = isset($this->req['id']) ? ($this->req['id']) : 0;
        $car = $this->_stockModel->get(['id' => $id]);
        $car ? $this->_RD($car) : $this->_RC('数据不存在');
    }

    public function save() {
        $id = isset($this->req['id']) ? intval($this->req['id']) : 0;
        $data = [];
        if(isset($this->req['name']))          $data['name']          = trim($this->req['name']);
        if(isset($this->req['to_airpot_day'])) $data['to_airpot_day'] = trim($this->req['to_airpot_day']);
        if(isset($this->req['to_store_day']))  $data['to_store_day']  = trim($this->req['to_store_day']);
        if(isset($this->req['cfg_pdf']))       $data['cfg_pdf']       = trim($this->req['cfg_pdf']);
        if(isset($this->req['remark']))        $data['remark']        = trim($this->req['remark']);

        if($id) {
            $result = $this->_stockModel->updateById($id, $data);
        } else {
            $result = $this->_stockModel->add($data);
        }
        $result ? $this->_RD([]) : $this->_RC('操作失败');
    }

    public function stop() {
        $id = $this->req['id'];
        $display = $this->req['display'];
        $result = $this->_stockModel->updateById($id, ['display' => $display]);
        $result ? $this->_RD([]) : $this->_RC('操作失败');
    }

    public function delete() {
        $id = $this->req['id'];
        $result = $this->_stockModel->delete(['id' => $id]);
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
