<?php
/**
 * Created by PhpStorm.
 * @node_name 权限控制类测试名称
 * User: xuan
 * Date: 2015/6/3
 * Time: 14:44
 */

namespace Controller\Admin\Auth;
use Service\Cache\RbacCacheService;

class Index extends \Controller\Admin\Common\Common {

    public    $node_model;
    public    $controller_name_prefix = 'Controller\Admin';
    public    $except_functions       = array('operate');
    protected $nodeService;
    protected $rbacCacheService;

    public function __construct() {
        parent::__construct();
        $this->setHeaderFooter();
        $this->node_model  = new \Model\Node();
        $this->nodeService = new \Service\Auth\NodeService();
        $this->rbacCacheService = new RbacCacheService();
        include_once(__LIBRARY__ . '/Util/Url.class.php');
    }

    public function index() {

        $actions['getNodeList']          = \Url::get_function_url('auth', 'index', 'getNodeList', array(), true);
        $actions['edit']                 = \Url::get_function_url('auth', 'index', 'edit', array(), true);
        $actions['controlAdd']           = \Url::get_function_url('auth', 'index', 'controlAdd', array(), true);
        $actions['controlDelete']        = \Url::get_function_url('auth', 'index', 'controlDelete', array(), true);
        $actions['setControlNodeStatus'] = \Url::get_function_url('auth', 'index', 'setControlNodeStatus', array(), true);
        $this->view->assign('actions', $actions);
        $this->view->display('Admin/Auth/index.html');
    }

    public function getNodeList() {
        $params                   = $this->parseJplistStatuses($this->req['statuses']);
        $params['where']['level'] = 2;
        $nodeService              = new \Service\Auth\NodeService();
        $result                   = $nodeService->getNodeList($params);
        include_once(__LIBRARY__ . '/Util/Url.class.php');
        foreach ((array)$result['data'] as $key => $node) {
            $parent_node                              = $this->node_model->get_node_info($node['pid']);
            $result['data'][$key]['parent_node_name'] = $parent_node['title'];
            $action_nodes                             = $this->node_model->get_action_node_names($node['id']);
            if (is_array($action_nodes) && !empty($action_nodes)) {
                $actions_name = array();
                foreach ($action_nodes as $val) {
                    $actions_name[] = $val['title'];
                }
                $result['data'][$key]['action_name'] = $actions_name;
            }
            $result['data'][$key]['status_bool']      = $node['status'] ? true : false;
            $result['data'][$key]['action']['edit']   = \Url::get_short_function_url('Auth', 'edit', array('id' => $node['id']));
            $result['data'][$key]['action']['delete'] = \Url::get_short_function_url('Auth', 'delete', array('id' => $node['id']));
        }

        $list           = $result['data'];
        $result['data'] = [
            'list'       => $list,
            'group_list' => $this->nodeService->getGroupList()

        ];

        $this->ajaxReturn($result);
    }

    public function edit() {
        $node_id = $this->request->get('id');
        if ($node_id) {
            $node_info = $this->node_model->get_node_info($node_id);
            if (is_array($node_info) && !empty($node_info)) {
                $where['level']            = abs($node_info['level'] - 1);
                $parent_nodes              = $this->node_model->getListByWhere($where);
                $node_info['parent_nodes'] = $parent_nodes;
            }
            $action_list = $this->node_model->get_action_node_list($node_id);
        }


        $action_url = array(
            'nodeSave'        => \Url::get_short_function_url('Auth', 'nodeSave'),
            'nodeAdd'         => \Url::get_short_function_url('Auth', 'nodeAdd'),
            'nodeEdit'        => \Url::get_short_function_url('Auth', 'nodeEdit'),
            'nodeDelete'      => \Url::get_short_function_url('Auth', 'nodeDelete'),
            'getClassNode'    => \Url::get_function_url('auth', 'index', 'getClassNode', array(), true),
            'nodeBatchAdd'    => \Url::get_function_url('auth', 'index', 'nodeBatchAdd', array(), true),
            'nodeBatchDelete' => \Url::get_function_url('auth', 'index', 'nodeBatchDelete', array(), true),
        );
        $group_list = $this->nodeService->getGroupList();
        $this->view->assign('basic_info', $node_info);
        $this->view->assign('action_list', $action_list);
        $this->view->assign('group_list', $group_list);
        $this->view->assign('action_url', $action_url);
        $this->view->assign('node_id', $node_id);

        $this->view->display('Admin/Auth/edit.html');
    }

    /**
     * 查询类的节点控制器下新增方法列表
     */
    public function getClassNode() {
        $node_id         = intval($this->get['node_id']);
        $controller_name = $this->controller_name_prefix;
        if ($node_id) {
            $nodeService = new \Service\Auth\NodeService();
            $node_list   = $nodeService->getNodeRealClass($node_id);
            foreach (array_reverse($node_list) as $key => $val) {
                $controller_name .= "\\" . ucfirst($val['name']);
            }
            if (class_exists($controller_name)) {
                $valid_functions   = $this->getValidMethods($controller_name);
                $existed_functions = $this->getExistedNodes($node_id);
                $reflector         = new \ReflectionClass($controller_name);
                $parents           = array();
                $class             = $reflector;
                while ($parent = $class->getParentClass()) {
                    $parents[] = $parent->getName();
                    $class     = $parent;
                }
                foreach ($parents as $key => $val) {
                    $tmp = $this->getValidMethods($val, true);
                    !empty($tmp) && $valid_functions = array_merge($valid_functions, $tmp);
                }

                $existed_function_names = [];
                foreach ($existed_functions as $key => $val) {
                    $existed_function_names[] = $val['name'];
                }
                $new_valid_functions = [];
                foreach ($valid_functions as $key => $val) {
                    $valid_function_names[] = $val['name'];
                    if (!in_array($val['name'], $existed_function_names)) {
                        $current_function            = $val;
                        $current_function['existed'] = false;
                        $new_valid_functions[]       = $current_function;
                    }
                }

                foreach ($existed_functions as $key => $val) {
                    $existed_functions[$key]['real_func'] = in_array($val['name'], $valid_function_names) ? true : false;
                }

                $nodes_list = array_merge((array)$existed_functions, (array)$new_valid_functions);
                $this->ret  = array('status' => 1, 'info' => '刷新节点方法成功', 'data' => $nodes_list);

            } else {
                $this->ret = array('status' => 0, 'info' => '当前控制器类名不存在');
            }
        } else {
            $this->ret = array('status' => 0, 'info' => '未指定节点');
        }

        $this->ajaxReturn($this->ret);
    }

    /**
     * 获取节点已经添加的方法列表
     * @param int $node_id
     * @return array
     */
    private function getExistedNodes($node_id = 0) {
        $existed_list = array();
        if ($node_id) {
            $nodeService  = new \Service\Auth\NodeService();
            $existed_list = $nodeService->getNodeSubListByPid($node_id);
            foreach ((array)$existed_list as $k => $v) {
                $existed_list[$k]['existed']         = true;
                $existed_list[$k]['node_edit_url']   = true;
                $existed_list[$k]['node_edit_url']   = \Url::get_short_function_url('Auth', 'nodeEdit');
                $existed_list[$k]['node_delete_url'] = \Url::get_short_function_url('Auth', 'nodeDelete');
            }
        }
        return $existed_list;
    }

    /**
     * 获取符合权限节点显示条件的方法列表
     * @param string $class_name
     * @param bool   $check_comment
     * @return array
     */
    private function getValidMethods($class_name = '', $check_comment = false) {
        $valid_functions = array();
        if (class_exists($class_name)) {
            $reflector = new \ReflectionClass($class_name);
            $functions = $reflector->getMethods();
            foreach ((array)$functions as $key => $obj) {
                $show_function  = $check_comment ? $this->isValidCommentMethod($obj) : true;
                $obj_names[]    = $obj->name;
                $reflect_method = new \ReflectionMethod($class_name, $obj->name);
                if (($class_name == $obj->class) &&
                    ($reflect_method->isProtected() || $reflect_method->isPublic()) &&
                    !$reflect_method->isAbstract() &&
                    !$reflect_method->isConstructor() &&
                    $reflect_method->isUserDefined() && $show_function
                ) {
                    $comment = $obj->getDocComment();
                    if (!empty($comment) && preg_match('/@node_name[^\r\n\n]+/', $comment, $matches)) {
                        isset($matches[0]) && $node_name = str_replace(['@node_name', ' ', '\t'], '', $matches[0]);
                    } else {
                        $node_name = $obj->name;
                    }
                    $valid_functions[] = array(
                        'name'        => $obj->name,
                        'node_name'   => $obj->name,
                        'title'       => $node_name,
                        'node_title'  => $node_name,
                        'doc_comment' => $obj->getDocComment(),
                        'class_name'  => $class_name
                    );
                }

            }
        }
        return $valid_functions;
    }

    /**
     * 判定父类中的方法是否允许设置为子类的权限节点
     * @param string $function_obj
     * @return bool
     */
    private function isValidCommentMethod($function_obj = '') {
        $isShow = false;
        if (is_object($function_obj)) {
            $has_doc_comment = strpos($function_obj->getDocComment(), '@show_in_subClass');
            (false !== $has_doc_comment) && ($isShow = true);
        }
        return $isShow;
    }

    /**
     * 保存节点基础信息
     */
    public function nodeSave() {
        $post_info = $this->request->post;
        $ret       = array('status' => 0, 'info' => '保存失败');
        if (!isset($post_info['node_id']) || empty($post_info['node_id'])) {
            $ret['info'] = '非法操作！';
        } elseif (!isset($post_info['node_title']) || empty($post_info['node_title'])) {
            $ret['info'] = '标题不能为空';
        } else if (!isset($post_info['node_name']) || empty($post_info['node_name'])) {
            $ret['info'] = '控制器名不能为空';
        } else if (!isset($post_info['node_pid']) || empty($post_info['node_pid'])) {
            $ret['info'] = '请选择管理模块';
        } else {
            $node_id          = intval($post_info['node_id']);
            $data['name']     = $post_info['node_name'];
            $data['nav_name'] = $post_info['node_nav_name'];
            $data['title']    = $post_info['node_title'];
            $data['pid']      = intval($post_info['node_pid']);
            $data['group_id'] = intval($post_info['group_id']);
            $data['status']   = isset($post_info['node_status']) ? 1 : 0;
            $data['display']  = $post_info['display'];
            if (!empty($data['nav_name'])) {
                $where = array(
                    'id'       => array('not in', $node_id),
                    'nav_name' => $data['nav_name']
                );
                $count = $this->node_model->getListCountByWhere($where);
            } else {
                $count = 0;
            }
            if ($count) {
                $ret['info'] = '菜单别名已存在';
            } else {
                $res = $this->node_model->update_node_by_id($node_id, $data);
                if($res){
                    $user_ids = $this->nodeService->getUserIdsByNodeId($node_id);
                    foreach($user_ids as $user_id){
                        $res && $this->rbacCacheService->clearGetUserLevel2ListCache($user_id, $node_id);
                    }
                }
                $res && $ret = array('status' => 1, 'info' => '保存成功', 'data' => $res);
            }
        }
        $log_params['params']  = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log_params['message'] = "编辑节点信息：{$ret['info']}";
        $log_params['keyword'] = 'nodeSave';
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    /**
     * 保存节点基础信息
     */
    public function nodeEdit() {
        $post_info = $this->request->post;
        $ret       = array('status' => 0, 'info' => '保存失败');
        if (!isset($post_info['node_id']) || empty($post_info['node_id'])) {
            $ret['info'] = '非法操作！';
        } elseif (!isset($post_info['node_title']) || empty($post_info['node_title'])) {
            $ret['info'] = '标题不能为空';
        } else if (!isset($post_info['node_name']) || empty($post_info['node_name'])) {
            $ret['info'] = '操作名不能为空';
        } else {
            $node_id          = intval($post_info['node_id']);
            $data['name']     = $post_info['node_name'];
            $data['nav_name'] = $post_info['node_nav_name'];
            $data['title']    = $post_info['node_title'];
            $node_info        = $this->node_model->get_node_info($node_id);
            if (!is_array($node_info) || empty($node_info)) {
                $ret['info'] = '当前节点不存在！';
            } else {
                $where['id']    = array('not in', $node_id);
                $where['name']  = $data['name'];
                $where['level'] = $node_info['level'];
                $where['pid']   = $node_info['pid'];

                $count = $this->node_model->getListCountByWhere($where);
                if ($count) {
                    $ret['info'] = '操作名在当前层级下已经存在';
                } else {
                    if (!empty($data['nav_name'])) {
                        $where = array(
                            'id'       => array('not in', $node_id),
                            'nav_name' => $data['nav_name']
                        );
                        $count = $this->node_model->getListCountByWhere($where);
                    } else {
                        $count = 0;
                    }
                    if ($count) {
                        $ret['info'] = '菜单别名已存在';
                    } else {
                        $res = $this->node_model->update_node_by_id($node_id, $data);
                        $res && $ret = array('status' => 1, 'info' => '修改成功', 'data' => $res);
                    }
                }
            }
        }
        $log_params['params']  = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log_params['message'] = "编辑方法节点信息：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    /**
     * 删除方法节点基础信息
     */
    public function nodeDelete() {
        $node_id = intval($this->request->post('node_id'));
        $ret     = array('status' => 0, 'info' => '删除失败');
        if (!$node_id) {
            $ret['info'] = '非法操作！';
        } else {
            $res = $this->node_model->delete_node($node_id);
            $res && $ret = array('status' => 1, 'info' => '删除成功', 'data' => array('node_id' => $node_id));
        }
        $log_params['params']  = json_encode($this->post, JSON_UNESCAPED_UNICODE);
        $log_params['message'] = "删除方法节点：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    /**
     * 删除控制器节点
     */
    public function controlDelete() {
        $node_id = intval($this->request->post('node_id'));
        $ret     = array('status' => 0, 'info' => '删除失败');
        if (!$node_id) {
            $ret['info'] = '非法操作！';
        } else {
            $count = $this->node_model->getListCountByWhere(" pid = {$node_id} ");
            if ($count) {
                $ret['info'] = '当前模块下有方法节点，请先删除方法节点';
            } else {
                $res = $this->node_model->delete_node($node_id);
                $res && $ret = array('status' => 1, 'info' => '删除成功', 'data' => array('node_id' => $node_id));
            }
        }
        $log_params['message'] = "删除控制器节点：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    /**
     * 新增方法节点
     */
    public function nodeAdd() {
        $post_info = $this->request->post;
        $ret       = array('status' => 0, 'info' => '添加新方法失败');
        if (!isset($post_info['node_title']) || empty($post_info['node_title'])) {
            $ret['info'] = '标题不能为空';
        } else if (!isset($post_info['node_name']) || empty($post_info['node_name'])) {
            $ret['info'] = '操作名不能为空';
        } else if (!isset($post_info['node_pid']) || empty($post_info['node_pid'])) {
            $ret['info'] = '未指定上级节点';
        } else {
            $data['name'] = $post_info['node_name'];
            !empty($post_info['node_nav_name']) && $data['nav_name'] = $post_info['node_nav_name'];
            $data['title'] = $post_info['node_title'];
            $data['pid']   = intval($post_info['node_pid']);
            $where         = array('pid' => $data['pid'], 'name' => $data['name']);
            $count         = $this->node_model->getListCountByWhere($where);
            if ($count) {
                $ret = array('status' => 0, 'info' => '方法名已经存在');
            } else {
                $where             = array();
                $data['status']    = 1;
                $data['level']     = 3;
                $data['nav_name']  = '';
                if ($post_info['node_nav_name']) {
                    $where['nav_name'] = $data['nav_name'];
                    $count             = $this->node_model->getListCountByWhere($where);
                } else {
                    $count = 0;
                }
                if ($count) {
                    $this->ret['info'] = '菜单别名已存在';
                } else {
                    $res = $this->node_model->add_node($data);
                    if ($res) {
                        $ret['data'] = array(
                            'node_id'         => $res,
                            'node_name'       => $data['name'],
                            'node_nav_name'   => $data['nav_name'],
                            'node_title'      => $data['title'],
                            'node_edit_url'   => \Url::get_short_function_url('Auth', 'nodeEdit'),
                            'node_delete_url' => \Url::get_short_function_url('Auth', 'nodeDelete')
                        );

                        $ret['status'] = 1;
                        $ret['info']   = '添加新方法成功';
                    }
                }
            }
        }
        $log_params['params']  = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log_params['message'] = "新增节点：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    /**
     * 批量新增方法节点
     */
    public function nodeBatchAdd() {
        if (IS_POST) {
            if (is_array($this->post['batch_data']) && !empty($this->post['batch_data'])) {
                $ret                   = $this->nodeService->batchAdd($this->post['batch_data'], 3);
                $log_params['params']  = json_encode($this->post['batch_data'], JSON_UNESCAPED_UNICODE);
                $log_params['message'] = "批量新增方法节点：{$ret['info']}";
                $this->saveLog($log_params);
                $this->ajaxReturn($ret);
            }
        }

    }

    /**
     * 批量新增方法节点
     */
    public function nodeBatchDelete() {
        if (IS_POST) {
            if (is_array($this->post['node_ids']) && !empty($this->post['node_ids'])) {
                $ret                   = $this->nodeService->batchDelete($this->post['node_ids']);
                $log_params['params']  = json_encode($this->post['node_ids'], JSON_UNESCAPED_UNICODE);
                $log_params['message'] = "批量删除方法节点：{$ret['info']}";
                $this->saveLog($log_params);
                $this->ajaxReturn($ret);
            }
        }

    }

    public function controlAdd() {
        $module_nodes = $this->node_model->get_module_nodes();
        $this->view->assign('control_save_url', \Url::get_short_function_url('Auth', 'controlSave'));
        $this->view->assign('module_nodes', $module_nodes);
        $this->view->display('Admin/Auth/controladd.html');
    }

    /**
     * 保存节点基础信息
     */
    public function controlSave() {
        $post_info = $this->request->post;
        $ret       = array('status' => 0, 'info' => '保存失败');
        if (!isset($post_info['node_title']) || empty($post_info['node_title'])) {
            $ret['info'] = '标题不能为空';
        } else if (!isset($post_info['node_name']) || empty($post_info['node_name'])) {
            $ret['info'] = '控制器名不能为空';
        } else if (!isset($post_info['node_pid']) || empty($post_info['node_pid'])) {
            $ret['info'] = '请选择父级节点';
        } else {
            $data['name'] = $post_info['node_name'];
            !empty($post_info['nav_name']) && $data['nav_name'] = $post_info['nav_name'];
            $data['title']    = $post_info['node_title'];
            $data['pid']      = intval($post_info['node_pid']);
            $data['status']   = 1;
            $data['level']    = 2;
            $data['display']  = $post_info['display'];
            $data['nav_name'] = '';

            $count = $this->node_model->getListCountByWhere(" name = '{$data['name']}' AND pid = {$data['pid']} ");
            if ($count) {
                $ret['info'] = '控制器名已存在！';
            } else {
                if ($data['nav_name']) {
                    $where['nav_name'] = $data['nav_name'];
                    $count             = $this->node_model->getListCountByWhere($where);
                } else {
                    $count = 0;
                }
                if ($count) {
                    $this->ret['info'] = '菜单别名已存在';
                } else {
                    $res = $this->node_model->add_node($data);
                    $res && $ret = array('status' => 1, 'info' => '保存成功', 'data' => $res);
                }
            }
        }
        $log_params['params']  = json_encode($this->post, JSON_UNESCAPED_UNICODE);
        $log_params['message'] = "保存控制器节点信息：{$ret['info']}";
        $this->saveLog($log_params);
        $this->ajaxReturn($ret);
    }

    public function setControlNodeStatus() {
        $nodeService           = new \Service\Auth\NodeService();
        $this->ret             = $nodeService->setControlNodeStatus($this->post);
        $message               = $this->post['status'] ? '启用节点' : '禁用节点';
        $log_params['message'] = "{$message}：{$this->ret['info']}";
        $log_params['params']  = json_encode($this->post, JSON_UNESCAPED_UNICODE);
        $this->saveLog($log_params);
        $this->ajaxReturn($this->ret);
    }
}
