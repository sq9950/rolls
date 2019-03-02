<?php
/**
 * Desc: 导航菜单管理服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015-8-28 14:33:45
 */

namespace Service\Auth;
class NavService extends \Service\Service{

    public $ret;
    public function __construct(){
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }

    public function initModels(){
        $this->models = new \stdClass();
        $this->models->node                          = new \Model\Node();
    }

    public function getNavList($params = array()){
        $result = array(
            'count'     =>  0,
            'data'      => array()
        );
        extract($params);
        empty($where) && $where = array();
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 20;
        empty($order) && $order = array();
        $where['level'] = 1;
        $result['count'] = $this->models->node->getListCountByWhere($where);
        if($result['count']){
            $result['data'] = $this->models->node->getListByWhere($where, $offset, $limit, $order);
        }

        return $result;
    }

    public function setNavStatus($params = array()){
        if(empty($params['id'])){
            $this->ret['info'] = '请选择节点';
        }else{
            $data['status'] = $params['status'];
            $sub_list = $this->getNodeSubListByPid($params['id']);
            if(!$params['status'] && !empty($sub_list)){
                $this->ret['status'] = 0;
                $this->ret['info'] = '该节点存在子节点无法禁用';
            }else{
                $res = $this->models->node->updateById($params['id'], $data);
                if($res){
                    $this->ret = array('status' => 1, 'info' => '设置成功');
                }else{
                    $this->ret['info'] = '状态设置失败';
                }
            }
        }
        return $this->ret;
    }

    public function getNodeSubListByPid($pid = 0){
        $sub_list = array();
        if($pid){
            $where['pid'] = $pid;
            $sub_list = $this->models->node->getListByWhere($where);
            if(is_array($sub_list) && !empty($sub_list)){
                $this->ret = array('status' => 1, 'info' => '查询节点子节点列表成功');
            }else{
                $this->ret['info'] = '查询节点的子节点列表失败';
            }
        }else{
            $this->ret['info'] = '节点不存在';
        }
        return $sub_list;
    }

    public function getNodeInfoByWhere($where = array()){

        return $this->models->node->getOneByWhere($where);
    }

    /**
     * 获取节点的所有父节点
     * @param int $node_id
     * @return mixed
     */
    public function getNodeRealClass($node_id = 0){
        static $node_list = array();
        $node_id = intval($node_id);
        if($node_id){
            $where['id'] = $node_id;
            $node_info = $this->models->node->getOneByWhere($where);
            array_push($node_list, $node_info);
            $pid = intval($node_info['pid']);
            $pid && $this->getNodeRealClass($pid);
        }
        return $node_list;
    }
}