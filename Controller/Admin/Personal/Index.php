<?php
/**
 * Desc: 个人中心模块
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/12/25 13:25
 */
namespace Controller\Admin\Personal;
class Index extends \Controller\Admin\Common\Common{

	public $personalService;
	public $memberService;
	public $userService;
	public $nodeService;
	private $roleService;

	public function __construct(){
		parent::__construct();

		$this->setHeaderFooter();
		$this->personalService = new \Service\Personal\PersonalService();
		$this->memberService = new \Service\Common\MemberService();
		$this->userService = new \Service\Common\UserService();
		$this->nodeService= new  \Service\Auth\NodeService();
		$this->roleService = new \Service\Auth\RoleService();
	}

	public function index(){
		$action_names = array('editPass', 'getLoggedMemberInfo', 'editStatus', 'saveBasic');
		$actions = $this->buildUrl($action_names);
		$this->view->assign('actions', $actions);
		$this->view->assign('title' , '基础信息');
        $this->view->display('Admin/Personal/Index/index.html');

	}

	public function saveBasic(){
		$ret = array('status' => 0, 'info' => '编辑个人信息失败');
		$user_id = $this->memberService->getLoginedMemberId();
		if(!$user_id){
			$ret['info'] = '用户已退出';
		}else{
			$data = array(
				'account' => trim($this->post['account']),
				'nickname' => trim($this->post['nickname']),
				'mobile' => trim($this->post['mobile']),
				'remark' => trim($this->post['remark']),
			);
			$is_existed = $this->userService->isExistedAccount($data['account'],
				array('id' => array('neq' , $user_id)));
			if($is_existed){
				$this->ajaxReturn(array('status' => 0, 'info' => '用户名已经存在，请换一个'));
			}
			$check_data['account'] = $data['account'];
			!empty($data['mobile']) && $check_data['mobile'] = $data['mobile'];
			$res = $this->userService->checkUserFields($check_data);
			if($res['status']){
				$res = $this->userService->saveUserInfo($user_id, $data);
				if($res['status']){
					$this->userService->setLoginedUserSeesion($data);
					$ret = array('status' => 1, 'info' => '编辑个人信息成功');
				}
			}else{
				$ret = $res;
			}
		}
		$log_params['message'] = "编辑个人信息：{$ret['info']}";
		$this->saveLog($log_params);
		$this->ajaxReturn($ret);
	}

	public function editPass() {
		if (IS_POST) {
			$this->ret = $this->personalService->editPass($this->post);
			$data = array('password' => $this->userService->encryptPassword($this->post['new_password']));
			$this->ret['status'] && $this->userService->setLoginedUserSeesion($data);
			$log_params['message'] = "修改密码：{$this->ret['info']}";
			$this->saveLog($log_params);
		}
		$this->ajaxReturn($this->ret);
	}

	public function editStatus(){
		$status = $this->req['status'];
		$data['status'] = $status;
		$ret = array('status' => 0, 'info' => '修改状态失败');
		$user_id = $this->memberService->getLoginedMemberId();
		if(!$user_id){
			$ret['info'] = '用户已退出';
		}else{
			$res = $this->userService->saveUserInfo($user_id, $data);
			$res['status'] && $ret = array('status' => 1, 'info' => '修改状态成功');
			$res['status'] && $this->userService->setLoginedUserSeesion($data);
		}
		$message = $status ? '启用用户' : '禁用用户';
		$log_params['message'] = "{$message}：{$ret['info']}";
		$this->saveLog($log_params);
		$this->ajaxReturn($ret);
	}

	public function getLoggedMemberInfo(){
		$member_info = $this->memberService->getLoginedMemberInfo();
		if(!empty($member_info)){
			unset($member_info['password']);
		}

		/* start huangzhongxi*/
		$where = array('user_id' => $member_info['id']);
		$role = $this->userService->getUserRoleByWhere($where);
		if($role){
			$member_info['role_id']=$role['role_id'];
			$where=['id'=>$role['role_id']];

			$role_info=$this->roleService->getOneRoleInfo($where);
			$member_info['name']=$role_info['name'];
		}else{
			$member_info['name']="游客";
		}

		$member_info['manager']= \Url::get_function_url('Personal', 'Index', 'Manager');
		/*end*/
		$this->ajaxReturn($member_info);
	}

	/**
	 *获取权限页面
	 *@author huangzhongxi | huangzhongxi@yundun.com
	 */
	public function Manager()
	{
		$actions['getNode'] = \Url::get_function_url('Personal', 'Index', 'getUserNode');
		$this->view->assign('role_id', $this->request->get('role_id'));
		$this->view->assign('actions', $actions);
		$this->view->display('Admin/Personal/Index/manager.html');
	}

	/**
	 *根据用户角色role_id获取相应节点
	 *@param int $pid
	 *@param int $role_id 
	 *@return array
	 *@author huangzhongxi | huangzhongxi@yundun.com
	 */
	public function getUserNode()
	{
		$param=$this->request->get;
		$where=array();
		$where['pid']=isset($param['id'])?intval($param['id']):0;
		$where['role_id']=$param['role_id'];

		$list=$this->nodeService->getUserNode($where);

		$this->ajaxReturn($list);
	}
}
