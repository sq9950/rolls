<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 +------------------------------------------------------------------------------
 * 基于角色的数据库方式验证类
 +------------------------------------------------------------------------------
 */
// 配置文件增加设置
// USER_AUTH_ON 是否需要认证
// USER_AUTH_TYPE 认证类型
// USER_AUTH_KEY 认证识别号
// REQUIRE_AUTH_MODULE  需要认证模块
// NOT_AUTH_MODULE 无需认证模块
// USER_AUTH_GATEWAY 认证网关
// RBAC_DB_DSN  数据库连接DSN
// RBAC_ROLE_TABLE 角色表名称
// RBAC_USER_TABLE 用户表名称
// RBAC_ACCESS_TABLE 权限表名称
// RBAC_NODE_TABLE 节点表名称
/*
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `think_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `think_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `think_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `think_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
class Rbac {

    public static $model_container = array();

    public static $current_model;

    // 认证方法
    static public function authenticate($map,$model='') {
        if(empty($model)) $model =  C('USER_AUTH_MODEL');
        //使用给定的Map进行认证
        $str_model = "\\Model\\{$model}";
        $User = new $str_model();
        return $User->find($map);
    }

    //用于检测用户权限的方法,并保存到Session中
    static function saveAccessList($authId=null) {
        if(null===$authId)   $authId = $_SESSION[C('USER_AUTH_KEY')]['id'];
        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开发所有权限
        if(C('USER_AUTH_TYPE') !=2 && !$_SESSION[C('ADMIN_AUTH_KEY')] )
            $_SESSION['_ACCESS_LIST']	=	self::getAccessList($authId);
        return ;
    }

	// 取得模块的所属记录访问权限列表 返回有权限的记录ID数组
	static function getRecordAccessList($authId=null,$module='') {
        if(null===$authId)   $authId = $_SESSION[C('USER_AUTH_KEY')]['id'];
        if(empty($module))  $module	=	CONTROLLER_NAME;
        //获取权限访问列表
        $accessList = self::getModuleAccessList($authId,$module);
        return $accessList;
	}

    //检查当前操作是否需要认证
    static function checkAccess() {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if( C('USER_AUTH_ON') ){
			$_module	=	array();
			$_action	=	array();
            if("" != C('REQUIRE_AUTH_MODULE')) {
                //需要认证的模块
                $_module['yes'] = explode(',',strtoupper(C('REQUIRE_AUTH_MODULE')));
            }else {
                //无需认证的模块
                $_module['no'] = explode(',',strtoupper(C('NOT_AUTH_MODULE')));
            }
            //检查当前模块是否需要认证
            if((!empty($_module['no']) && !in_array(strtoupper(CONTROLLER_NAME),$_module['no'])) || (!empty($_module['yes']) && in_array(strtoupper(CONTROLLER_NAME),$_module['yes']))) {
				if("" != C('REQUIRE_AUTH_ACTION')) {
					//需要认证的操作
					$_action['yes'] = explode(',',strtoupper(C('REQUIRE_AUTH_ACTION')));
				}else {
					//无需认证的操作
					$_action['no'] = explode(',',strtoupper(C('NOT_AUTH_ACTION')));
				}
				//检查当前操作是否需要认证
				if((!empty($_action['no']) && !in_array(strtoupper(ACTION_NAME),$_action['no'])) || (!empty($_action['yes']) && in_array(strtoupper(ACTION_NAME),$_action['yes']))) {
					return true;
				}else {
					return false;
				}
            }else {
                return false;
            }
        }
        return false;
    }

	// 登录检查
	static public function checkLogin() {
        //检查当前操作是否需要认证
        if(self::checkAccess()) {
            //检查认证识别号
            if(!$_SESSION[C('USER_AUTH_KEY')]) {
                if(C('GUEST_AUTH_ON')) {
                    // 开启游客授权访问
                    if(!isset($_SESSION['_ACCESS_LIST']))
                        // 保存游客权限
                        self::saveAccessList(C('GUEST_AUTH_ID'));
                }else{
                    // 禁止游客访问跳转到认证网关
                    redirect(PHP_FILE.C('USER_AUTH_GATEWAY'));
                }
            }
        }
        return true;
	}

    //权限认证的过滤器方法
    static public function AccessDecision($get_params = array(), $appName=MODULE_NAME) {
        //检查是否需要认证
        $action_name = in_array(ACTION_NAME, array('operate')) ? strtolower($get_params['op']) : ACTION_NAME;

        if(self::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid   =   md5($appName.CONTROLLER_NAME.$action_name);
            if($_SESSION[C('USER_AUTH_KEY')]['id'] != C('ADMIN_USER_ID') && empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
                if(C('USER_AUTH_TYPE')==2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = self::getAccessList($_SESSION[C('USER_AUTH_KEY')]['id']);
                }else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if( $_SESSION[$accessGuid]) {
                        return true;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                if(!isset($accessList[strtoupper($appName)][strtoupper(CONTROLLER_NAME)][strtoupper($action_name)])) {
                    $_SESSION[$accessGuid]  =   false;
                    return false;
                }
                else {
                    $_SESSION[$accessGuid]	=	true;
                }
            }else{
                //管理员无需认证
				return true;
			}
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 取得当前认证号的所有权限列表
     +----------------------------------------------------------
     * @param integer $authId 用户ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     */
    static public function getAccessList($authId) {
        $rbacCacheService = new \Service\Cache\RbacCacheService($authId);
        $access = $rbacCacheService->getCacheByMethod($authId, 'GetAccessList');
        if(empty($access)){
            self::checkAndSetCurrentModel();
            // Db方式权限数据
            $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
            $sql    =   "select node.id,node.name from ".
                $table['role']." as role,".
                $table['user']." as user,".
                $table['access']." as access ,".
                $table['node']." as node ".
                "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=1 and node.status=1";

            $apps = self::$current_model->selectBySql($sql);
            $access =  array();
            foreach($apps as $key=>$app) {
                $appId	=	$app['id'];
                $appName	 =	 $app['name'];
                // 读取项目的模块权限
                $access[strtoupper($appName)]   =  array();
                $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=2 and node.pid={$appId} and node.status=1";
                $modules =  self::$current_model->selectBySql($sql);
                // 判断是否存在公共模块的权限
                $publicAction  = array();
                foreach($modules as $key=>$module) {
                    $moduleId	 =	 $module['id'];
                    $moduleName = $module['name'];
                    if('PUBLIC'== strtoupper($moduleName)) {
                        $sql    =   "select node.id,node.name from ".
                            $table['role']." as role,".
                            $table['user']." as user,".
                            $table['access']." as access ,".
                            $table['node']." as node ".
                            "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                        $rs = self::$current_model->selectBySql($sql);
                        foreach ($rs as $a){
                            $publicAction[$a['name']]	 =	 $a['id'];
                        }
                        unset($modules[$key]);
                        break;
                    }
                }
                // 依次读取模块的操作权限
                foreach($modules as $key=>$module) {
                    $moduleId	 =	 $module['id'];
                    $moduleName = $module['name'];
                    $sql    =   "select node.id,node.name from ".
                        $table['role']." as role,".
                        $table['user']." as user,".
                        $table['access']." as access ,".
                        $table['node']." as node ".
                        "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                    $rs =  self::$current_model->selectBySql($sql);
                    $action = array();
                    foreach ($rs as $a){
                        $action[$a['name']]	 =	 $a['id'];
                    }
                    // 和公共模块的操作权限合并
                    $action += $publicAction;
                    $access[strtoupper($appName)][strtoupper($moduleName)]   =  array_change_key_case($action,CASE_UPPER);
                }
            }
            $rbacCacheService->addCacheByMethod($authId, $access, 'GetAccessList');
        }

        return $access;
    }

	// 读取模块所属的记录访问权限
	static public function getModuleAccessList($authId,$module) {
        $rbacCacheService = new \Service\Cache\RbacCacheService($authId);
        $access = $rbacCacheService->getCacheByMethod(array($authId, $module), 'GetModuleAccessList');
        if(empty($access)){
            self::checkAndSetCurrentModel();
            // Db方式
            $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'));
            $sql    =   "select access.node_id from ".
                $table['role']." as role,".
                $table['user']." as user,".
                $table['access']." as access ".
                "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and  access.module='{$module}' and access.status=1";
            $rs =  self::$current_model->selectBySql($sql);
            $access	=	array();
            foreach ($rs as $node){
                $access[]	=	$node['node_id'];
            }
            $rbacCacheService->addCacheByMethod(array($authId ,$module), $access, 'GetModuleAccessList');
        }

		return $access;
	}

    /**
     * 读取用户所有权限节点中，level为1的节点列表
     * @param $authId
     * @return bool
     */
    static public function getUserLevel1List($authId){
        $rbacCacheService = new \Service\Cache\RbacCacheService($authId);
        $level1 = $rbacCacheService->getCacheByMethod($authId, 'GetUserLevel1List');
        if(empty($level1)){
            self::checkAndSetCurrentModel();
            $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
            $sql    =   "SELECT * FROM {$table['node']}
             WHERE id IN (
                      SELECT node_id
                      FROM {$table['access']}
                      where role_id =
                          (SELECT role_id FROM {$table['user']} WHERE user_id = {$authId} limit 1)
                          AND `level` = 1
             )
             AND status = 1
             AND `level` = 1 ";
            $list =  self::$current_model->selectBySql($sql);
            if(is_array($list) && !empty($list)){
                foreach($list as $node){
                    $level1[$node['id']] = $node;
                }
            }
            $rbacCacheService->addCacheByMethod($authId, $level1, 'GetUserLevel1List');
        }

        return $level1;
    }

    static public function getUserLevel2List($authId, $pid = 0){
        $rbacCacheService = new \Service\Cache\RbacCacheService($authId);
        $level2 = $rbacCacheService->getCacheByMethod(array($authId, $pid), 'GetUserLevel2List');
        if(empty($level2)){
            self::checkAndSetCurrentModel();
            $user_id = intval($authId);
            $pid = intval($pid);
            $level2 = array();
            if($user_id && $pid){
                $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
                $sql    =   " SELECT * FROM {$table['access']}
                      where role_id = (SELECT role_id FROM {$table['user']} where user_id = {$user_id} limit 1)
                            and level = 2
                            and node_id in (SELECT id from {$table['node']} where `level` = 2 and pid = {$pid} and display = 1 )
            ";
                $list = self::$current_model->selectBySql($sql);
                if(is_array($list) && !empty($list)){
                    foreach($list as $node){
                        $res[$node['node_id']] = $node;
                    }
                    $level2 = self::getNodeListByIds(array_keys($res));
                }
            }
            $rbacCacheService->addCacheByMethod(array($authId, $pid), $level2, 'GetUserLevel2List');
        }


        return $level2;
    }

    static public function getNodeIdByName($node_name = ''){
        $node_id = 0;
        if($node_name){
            $rbacCacheService = new \Service\Cache\RbacCacheService();
            $node_id = $rbacCacheService->getCacheByMethod($node_name, 'GetNodeIdByName');
            if(empty($node_id)){
                self::checkAndSetCurrentModel();
                $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
                $sql = "SELECT id FROM {$table['node']} WHERE name = '{$node_name}'";
                $res = self::$current_model->selectBySql($sql);
                isset($res[0]['id']) && ($node_id = $res[0]['id']);
                $rbacCacheService->addCacheByMethod($node_name, $node_id, 'GetNodeIdByName');
            }
        }
        return $node_id;
    }

    static public function getNodeListByIds($node_ids = array()){
        $node_list = array();
        if(is_array($node_ids) && !empty($node_ids)){
            $rbacCacheService = new \Service\Cache\RbacCacheService();
            $node_list = $rbacCacheService->getCacheByMethod($node_ids, 'GetNodeListByIds');
            if(empty($node_list)){
                self::checkAndSetCurrentModel();
                $ids = implode(',' , $node_ids);
                $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
                $sql = "SELECT * FROM {$table['node']} WHERE id IN ( {$ids} ) ";
                $node_list = self::$current_model->selectBySql($sql);
            }
            $rbacCacheService->addCacheByMethod($node_ids, $node_list, 'GetNodeListByIds');
        }
        return $node_list;
    }

    /**
     * 获取用户所有权限节点列表
     * @param int $authId
     * @return array
     */
    static public function getUserAccessNodeList($authId = 0){
        $node_tree = array();
        $role_id = self::getUserRoleId($authId);
        if($role_id){
            $node_ids = self::getRoleAccessNodeIds($role_id);
            $node_list = self::getNodeListByIds($node_ids);
            self::build_node_tree($node_list, $node_tree);

        }
        return $node_tree;
    }

    /**
     * 获取用户角色ID
     * @param int $authId
     * @return int|null
     */
    static public function getUserRoleId($authId = 0){
        $authId = intval($authId);
        $role_id = 0;
        if($authId){
            $rbacCacheService = new \Service\Cache\RbacCacheService($authId);
            $role_id = $rbacCacheService->getCacheByMethod($authId, 'GetUserRoleId');
            if(empty($role_id)){
                self::checkAndSetCurrentModel();
                $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
                $sql = "SELECT role_id FROM {$table['user']} WHERE user_id = {$authId} ";
                $res = self::$current_model->selectBySql($sql);
                isset($res[0]['role_id']) && ($role_id = $res[0]['role_id']);
            }
            $rbacCacheService->addCacheByMethod($authId, $role_id, 'GetUserRoleId');
        }
        return $role_id;
    }

    /**
     * 获取角色拥有权限的节点ID
     * @param int $role_id
     * @return array
     */
    static public function getRoleAccessNodeIds($role_id = 0){
        $node_ids = array();
        $role_id = intval($role_id);
        if($role_id){
            $rbacCacheService = new \Service\Cache\RbacCacheService();
            $node_ids = $rbacCacheService->getCacheByMethod($role_id, 'GetRoleAccessNodeIds');
            if(empty($node_ids)){
                self::checkAndSetCurrentModel();
                $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
                $sql = "SELECT node_id FROM {$table['access']} WHERE role_id = {$role_id} ";
                $node_list = self::$current_model->selectBySql($sql);
                if(is_array($node_list) && !empty($node_list)){
                    foreach($node_list as $key => $node){
                        $node_ids[] = $node['node_id'];
                    }
                }
            }
            $rbacCacheService->addCacheByMethod($role_id, $node_ids, 'GetRoleAccessNodeIds');
        }
        return $node_ids;
    }

    /**
     * 格式化权限节点名称为树形结构
     * @param array $node_list
     * @param array $tree
     * @param int $pid
     * @param int $level
     */
    static public function build_node_tree($node_list = array(), &$tree = array(), $pid = 0, $level = 0){
        if(is_array($node_list) && !empty($node_list)){
            foreach($node_list as $key => $node){
                if($pid == $node['pid'] && $level == $node['level']){
                    $tree[strtolower($node['name'])] = array();
                    unset($node_list[$key]);
                    self::build_node_tree($node_list, $tree[strtolower($node['name'])], $node['id'], $node['level']+1);
                }
            }
        }
        return ;
    }

    /**
     * 初始化模型对象
     * @param array $models
     */
    static function setModelInstances($models = array()){
        if(is_array($models) && !empty($models)){
            foreach($models as $name => $obj){
                !empty($name) && ($obj instanceof \Model\Model) && self::$model_container[$name] = $obj;
            }
            self::$current_model = array_pop(self::$model_container);
        }
    }

    /**
     * 检测和设置当前查询的MODEL
     */
    static function checkAndSetCurrentModel(){
        if(!self::$current_model instanceof \Model\Model){
            self::$current_model = new \Model\Node();
        }
    }
}