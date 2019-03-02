<?php
/**
 * smarty 单例
 * SingSmarty::getInstance();
 */
class SingSmarty{
	private static $smarty;
	//防止直接创建对象
	private function __construct(){}
	
	public static function getInstance($smarty_dir,$path){
		if (! self::$smarty) {
			//load smarty
			require_once ($smarty_dir. 'Smarty.class.php');
			$smarty = new Smarty ();
			//set dir
			self::setDir($smarty, $path);
			//conf
			self::config($smarty);
			self::$smarty = $smarty;
		}
		return self::$smarty;
	}
	//阻止复制对象
	public function __clone(){
		trigger_error('clone is not allowed in singleton',E_USER_ERROR);
	}
	
	
	//smarty obj set templatedir compiledir confdir chachedir
	private static function setDir($smarty, $path){
		if(is_object($smarty)){
			$smarty->setTemplateDir($path.'templates/');
			$smarty->setCompileDir($path.'templates_c/');
			$smarty->setConfigDir($path.'configs/');
			$smarty->setCacheDir($path.'cache/');
		}
	}
	
	//smarty obj conf
	private static function config($smarty, $conf = array()){
		if(is_object($smarty)){
			$smarty->force_compile = true;
			$smarty->caching = true;
			$smarty->cache_lifetime = 120;
			$smarty->debugging = true;
			if($conf){
				$smarty->force_compile = $conf['force_compile'];
				$smarty->caching = $conf['caching'];
				$smarty->cache_lifetime = $conf['cache_lifetime'];
				$smarty->debugging = $conf['debugging'];
			}
		}
	}
	
}