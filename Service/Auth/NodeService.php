<?php
namespace Service\Auth;
use Model\Auth\NodeGroupModel;

/**
 * 文档注释要放在命名空间的声明之后，否则无法获取！
 * @node_name 权限节点服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/28
 */
class NodeService extends \Service\Service {

    public $ret;

    public function __construct() {
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }

    public function initModels() {
        $this->models             = new \stdClass();
        $this->models->node       = new \Model\Node();
        $this->models->node_group = new NodeGroupModel();
        $this->models->access     = new \Model\Access();
        $this->models->roleUser     = new \Model\RoleUser();
    }

    public function getNodeList($params = array()) {
        $result = array(
            'count' => 0,
            'data'  => array()
        );
        extract($params);
        empty($where) && $where = array();
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 20;
        empty($order) && $order = array();
        $result['count'] = $this->models->node->getListCountByWhere($where);
        if ($result['count']) {
            $result['data'] = $this->models->node->getListByWhere($where, $offset, $limit, $order);
        }

        return $result;
    }

    public function setControlNodeStatus($params = array()) {
        if (empty($params['id'])) {
            $this->ret['info'] = '请选择节点';
        } else {
            $data['status'] = $params['status'];
            $sub_list       = $this->getNodeSubListByPid($params['id']);
            if (!$params['status'] && !empty($sub_list)) {
                $this->ret['status'] = 0;
                $this->ret['info']   = '该节点存在子节点无法禁用';
            } else {
                $res = $this->models->node->updateById($params['id'], $data);
                if ($res) {
                    $this->ret = array('status' => 1, 'info' => '设置成功');
                } else {
                    $this->ret['info'] = '状态设置失败';
                }
            }
        }
        return $this->ret;
    }

    public function getNodeSubListByPid($pid = 0) {
        $sub_list = array();
        if ($pid) {
            $where['pid'] = $pid;
            $sub_list     = $this->models->node->getListByWhere($where);
            if (is_array($sub_list) && !empty($sub_list)) {
                $this->ret = array('status' => 1, 'info' => '查询节点子节点列表成功');
            } else {
                $this->ret['info'] = '查询节点的子节点列表失败';
            }
        } else {
            $this->ret['info'] = '节点不存在';
        }
        return $sub_list;
    }

    public function getNodeInfoByWhere($where = array()) {

        return $this->models->node->getOneByWhere($where);
    }

    public function getExistedNodeList($where = array()) {

        return $this->models->node->getListByWhere($where);
    }

    /**
     * 获取节点的所有父节点
     * @param int $node_id
     * @return mixed
     */
    public function getNodeRealClass($node_id = 0) {
        static $node_list = array();
        $node_id = intval($node_id);
        if ($node_id) {
            $where['id'] = $node_id;
            $node_info   = $this->models->node->getOneByWhere($where);
            array_push($node_list, $node_info);
            $pid = intval($node_info['pid']);
            $pid && $this->getNodeRealClass($pid);
        }
        return $node_list;
    }

    /**
     * 获取菜单节点信息
     * @param int $node_id
     * @return array
     */
    public function getNodeBasic($node_id = 0) {
        $node_info = array();
        if ($node_id) {
            $node_info = $this->models->node->get_node_info($node_id);
            if (is_array($node_info) && !empty($node_info)) {
                $where['level']            = abs($node_info['level'] - 1);
                $parent_nodes              = $this->models->node->getListByWhere($where);
                $node_info['parent_nodes'] = $parent_nodes;
            }
        }
        return $node_info;
    }

    public function getNewNodeList($dir = '', $dir_prefix = '') {
        $node_list = array();
        if (is_dir($dir)) {
            $filter_list = array('.', '..');
            $file_list   = scandir($dir);
            $dir_arr     = explode(DIRECTORY_SEPARATOR, $dir);
            $nav_prefix  = array_pop($dir_arr);
            foreach ($file_list as $key => $file_name) {
                if (!in_array($file_name, $filter_list)) {
                    $tmp               = array();
                    $class_name        = $dir_prefix . '\\' . $nav_prefix . '\\' . $file_name;
                    $class_name        = str_replace(array('/', '.php'), array('\\', ''), $class_name);
                    $tmp['name']       = str_replace('.php', '', $file_name);
                    $tmp['class_name'] = $class_name;
                    $reflector         = new \ReflectionClass($class_name);
                    $comment           = $reflector->getDocComment();
                    if (!empty($comment) && preg_match('/@node_name[^\r\n\n]+/', $comment, $matches)) {
                        isset($matches[0]) && $nav_name = str_replace(['@node_name', ' ', '\t'], '', $matches[0]);
                    } else {
                        $nav_name = '';
                    }
                    $tmp['title'] = $nav_name;
                    array_push($node_list, $tmp);
                }
            }
        }
        return $node_list;
    }

    /**
     * 根据相应的pid和role_id 获取node_id
     * @params array $where
     * @author huangzhongxi | huangzhongxi@yundun.com
     */
    public function getUserNode($where = array()) {
        $pid['pid'] = $where['pid'];
        $list       = $this->models->node->getListByWhere($pid);
        if (is_array($list) && !empty($list)) {

            $access_node_ids = $this->models->access->get_role_node_ids($where['role_id']);
            foreach ($list as $key => $node) {
                $where        = array();
                $where['pid'] = $node['id'];
                $count        = $this->models->node->getListCountByWhere($where);
                $count && $list[$key]['isParent'] = true;
                in_array($node['id'], $access_node_ids) && $list[$key]['checked'] = true;
            }
        }

        return $list ? $list : array();
    }

    public function batchAdd($data, $level = 0) {
        $res = ['status' => 0, 'info' => '批量新增失败'];
        if (is_array($data) && !empty($data)) {
            foreach ($data as $one) {
                if (!empty($one['node_name'])
                    && !empty($one['node_title'])
                    && !empty($one['node_pid'])
                ) {
                    $insert_data = [
                        'name'     => $one['node_name'],
                        'nav_name' => !empty($one['node_nav_name']) ? $one['node_nav_name'] : '',
                        'title'    => $one['node_title'],
                        'pid'      => $one['node_pid'],
                        'status'   => 1,
                        'level'    => $level,
                        'display'  => 1
                    ];
                    $res         = $this->models->node->add($insert_data);
                }
            }
            return ['status' => 1, 'info' => '批量新增成功'];
        }
        return $res;
    }

    public function batchDelete($ids = []) {
        $res = ['status' => 0, 'info' => '批量删除失败'];
        if (is_array($ids) && !empty($ids)) {
            $where = ['id' => ['in', $ids]];
            $res   = $this->models->node->deleteByWhere($where);
            if ($res) {
                return ['status' => 1, 'info' => '批量删除成功'];
            }
        }
        return $res;
    }

    public function getGroupList() {
        $where = [];
        $list = $this->models->node_group->getListByWhere($where);
        return $list;
    }

    public function getUserIdsByNodeId($node_id = 0){
        $user_ids = [];
        $where = ['node_id' => intval($node_id)];
        $list = $this->models->access->getListByWhere($where);
        $role_ids = !empty($list) ? array_column($list, 'role_id') : [];
        if($role_ids){
            $list = $this->models->roleUser->getListByWhere(['role_id' => ['in', $role_ids]]);
            !empty($list) && $user_ids = array_column($list, 'user_id');
        }
        return $user_ids;
    }
}