<?php
date_default_timezone_set('Asia/Shanghai');

//error_reporting(E_ALL);
error_reporting(E_ALL ^ E_ERROR ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED);
ini_set("display_errors",   "On");
define('ERROR_DISPLAY_CLI', false);
define('ERROR_DISPLAY_HTML', true);
define('SYS_KEY',      'admin-v3');

ini_set('session.use_cookies', 1);
define("__APP__", dirname(dirname(__DIR__))); //Public
define("__VIEW__", __APP__ . '/View/'); //Public
define("__ROOT__", __APP__);

$application = getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "Conf";
define("__CONF__", __ROOT__ . '/' . $application . '/');
define("__LIBRARY__", __ROOT__ . '/Library');
define("__VIEW_COMMON__", __ROOT__ . '/View/Common');

ini_set('session.gc_maxlifetime', 86400 * 2);
if (!session_id()) {
	ini_set('session.use_only_cookies', 'On');
	ini_set('session.use_trans_sid', 'Off');
	ini_set('session.cookie_httponly', 'On');
	session_start();
}

$autoloadFile = __ROOT__.'/vendor/autoload.php';
require $autoloadFile;
require __ROOT__ . '/Ypf/Ypf.php';

$ypfSetting = array(
	'root' => __ROOT__,
);

$app = new \Ypf\Ypf($ypfSetting);

//config
$config = new \Ypf\Lib\Config();
$config->load(__CONF__);
$config->load(__CONF__.'/Web');
$conf = require __CONF__ . '/common.php';
\Ypf\Lib\Config::$config = array_merge(\Ypf\Lib\Config::$config, $conf); //合并.php配置文件
$load = new \Ypf\Lib\Load(__ROOT__);

$app->set('load', $load);
$app->set('config', $config);
$app->set('global_config', \Ypf\Lib\Config::$config);

//help
$load->helper(array('common', 'utf8', 'formattime'));

//是否使用缓存
$load->library('Cache/Cache.php', array('adapter' => 'memcached', 'backup' => 'dummy'), 'cache_memcached');

//db
$db = new \Ypf\Lib\DatabaseV5($config->get('db.cp'));
$app->set('db', $db);
$db->query('SET group_concat_max_len = 102400');

//request
$app->set('request', new \Ypf\Lib\Request());

//response
$response = new \Ypf\Lib\Response();
$app->set('response', $response);

//view
$view = new \View\View();
$view->setTemplateDir(__ROOT__ . '/View');
$app->set('view', $view);

//document
$load->library('Document', '', 'document');

//curl
$load->library('Curl', '', 'curl');

$app->addPreAction("\Controller\Admin\Common\Router\index");
$app->disPatch();

$response->setCompression(9);
$response->output();
