<?php
namespace Controller\Admin\Node;
class Index extends \Controller\Admin\Common\Common{

    public function __construct(){
        parent::__construct();
        $layout_params = array(
            'header' => '',
            'footer' => '',
            'slide'  => array('node')
        );
        $this->setHeaderFooter($layout_params);
    }

	public function index(){

        $member = new \Model\Member();

        $where['dept_id'] = 1;
        $where['status'] = 1;

        $order['id'] = 'asc';
        $order['pay_type'] = 'asc';

        $fields = array('id, nickname, status');

        $offset = 0;
        $limit = 10;
        $list = $member->getListByWhere($where, $offset, $limit, $order, $fields);
        $this->view->display('Admin/Node/index.html');

	}

    public function alllist(){
        $this->view->display('Admin/Node/list.html');
    }

    public function get_json_list(){
        $_REQUEST['$top'] && $this->pageSize = $_REQUEST['$top'];
        $offset = $_REQUEST['$skip'] ? $_REQUEST['$skip'] : 0;
        $node_model = new \Model\Node();
        $where['level'] = 2;
        $order['id'] = 'asc';
        $list = $node_model->getListByWhere($where, $offset, $this->pageSize, $order);
        foreach( $list as $k=>$v ){
            $where = array();
            $where['pid'] = $v['id'];
            $offset = 0;
            $field = array('title');
            $titles = array();
            $info	= $node_model->getListByWhere($where, $offset, $this->pageSize, $order, $field);
            foreach($info as $val){
                $titles[] = $val['title'];
            }
            $list[$k]['titles']	= implode(' / ', $titles);
        }
        $ret = array(
            'd' => array(
                '__count' => 92,
                'result' => $list
            )
        );
        $callback = $_REQUEST['$callback'];
        header('Content-Type:application/json; charset=utf-8');
        exit($callback.'('.json_encode($ret).')');
    }
}
