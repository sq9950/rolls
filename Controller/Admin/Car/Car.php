<?php
namespace Controller\Admin\Car;

use \Model\Car\CarLslsModel;

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
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Car/index.html');
	}

    public function list() {
        $cars = $this->_carLslsModel->gets(['status' => 1]);
        $this->_RD($cars);
    }

    public function detail() {
        $id = isset($this->req['id']) ? ($this->req['id']) : 0;
        $car = $this->_carLslsModel->get(['id' => $id]);
        $car ? $this->_RD($car) : $this->_RC('数据不存在');
    }

    public function save() {
        $id = isset($this->req['id']) ? intval($this->req['id']) : 0;
        $data = [
            'name_en' => isset($this->req['name_en']) ? trim($this->req['name_en']) : '',
            'name_zh' => isset($this->req['name_zh']) ? trim($this->req['name_zh']) : '',
            'image'   => isset($this->req['image'])   ? trim($this->req['image'])   : '',
            'summary' => isset($this->req['summary']) ? trim($this->req['summary']) : '',
            'status'  => isset($this->req['status'])  ? trim($this->req['status'])  :  1,
            'remark'  => isset($this->req['remark'])  ? trim($this->req['remark'])  : '',
        ];
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

}
