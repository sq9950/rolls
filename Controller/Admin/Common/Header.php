<?php

namespace Controller\Admin\Common;

class Header extends \Controller\Admin\Common\Common {
	public function index() {
		$current_node = $this->get_current_nav_node();
		$nav_list = $this->get_header_nav_nodes();

		if(!empty($nav_list)){
			foreach($nav_list as $key => $val){
				$slide_nav = $this->get_slide_nav_nodes_by_pid($val['id']);
				if(is_array($slide_nav) && !empty($slide_nav)){
					$default_slide = $slide_nav[0];
					$nav_list[$key]['active'] = (strtolower($current_node) == strtolower($val['name']) ) ? 'active' : '';
					$nav_list[$key]['nav_url'] = \Url::get_action_url($val['name'], $default_slide['name']);
				}
			}
		}

		$host=$_SERVER['HTTP_HOST'];
        $title="劳斯莱斯";

        $this->view->assign("title",$title);

        $this->view->assign('user_info', $this->getMemberInfo());
		$this->view->assign('current_node', $current_node);
        $this->view->assign('nav_list', $nav_list);
		$actions['logout_url'] = $this->configall['USER_LOGOUT_GATEWAY'];
		$actions['saveSort'] = \Url::get_function_url('setting', 'slide', 'saveSlideSort',array(),true);
        $this->view->assign('actions', $actions);
		return $this->view->fetch("Admin/Public/header.html");
	}
	
	public function dash() {
		//title keywords description
		$tdk['title'] = $this->document->getTitle()?$this->document->getTitle():$this->config['webtitle'];
		$tdk['keywords'] = $this->document->getKeywords()?$this->document->getKeywords():$this->config['webkeywords'];
		$tdk['description'] = $this->document->getDescription()?$this->document->getDescription():$this->config['webdescription'];
		$this->view->assign('tdk', $tdk);
		
		//load dashboard js css
		$uri = $this->request->server['REQUEST_URI'];
		$dashboard = array('domainList', 'dashboard', 'ns_dns_record_list' ,'addDomain');
		$isdash = false;
		foreach($dashboard as $d){
			if(false !== strpos($uri, $d)){
				$isdash = true;
				break;
			}
		}
		$this->view->assign('isdash', $isdash);
		
		$this->view->assign('mustache_template', $this->view->fetch('Public/mustache_template.html'));
		return $this->view->fetch("Admin/Public/header_dashboard.html");
	}
}
