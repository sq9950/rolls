<?php
namespace Controller\Admin\Common;

use Service\Common\SlideService;

class SlideCommon extends \Controller\Admin\Common\Common {

    private $slideService;

    public function __construct() {
        parent::__construct();
        $this->slideService = new SlideService();
    }

    public function index() {
        $args = func_get_args();
        if (is_array($args) && !empty($args)) {
            foreach ($args as $sub_slide) {
                $slides[$sub_slide] = $this->sub_slide($sub_slide);
            }
            $this->view->assign('slides', $slides);
        }
        $slide_list   = $this->get_slide_nav_nodes();
        $current_node = $this->get_current_nav_node(2);
        empty($current_node) && $current_node = strtolower(CONTROLLER_NAME);
        foreach ((array)$slide_list as $key => $val) {
            $node_name                 = $val['nav_name'] ? strtolower($val['nav_name']) : strtolower($val['name']);
            $slide_list[$key]['class'] = $current_node == $node_name ? 'active' : '';
        }
        $group_list       = $this->slideService->getNodeGroupList();
        $group_slide_list = $this->formatGroupNodes($slide_list);
        $this->view->assign('group_slide_list', $group_slide_list);
        $this->view->assign('group_list', $group_list);
        $this->view->assign('slide_list', $slide_list);
        $this->view->assign('current_node', $this->get_current_nav_node());

        $actions['saveSort'] = \Url::get_function_url('setting', 'slide', 'saveSlideSort', array(), true);
        $this->view->assign('actions', $actions);

        return $this->view->fetch('Admin/Public/slide_common.html');
    }

    public function sub_slide($sub_slide = '') {
        if ($sub_slide) {
            return $this->view->fetch("Admin/Public/slide/slide_{$sub_slide}.html");
        }
    }

    private function formatGroupNodes(&$slide_list = []) {
        $node_trees = [];
        foreach ($slide_list as $key => $value) {
            if ($value['group_id']) {
                $value['url']                     = \Url::get_action_url(MODULE_NAME, $value['name']);
                $value['active']                  = strtolower(CONTROLLER_NAME) == strtolower($value['name']) ? 'active' : '';
                $node_trees[$value['group_id']][] = $value;
                unset($slide_list[$key]);
            }
        }
        return $node_trees;
    }
}