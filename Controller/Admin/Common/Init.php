<?php

namespace Controller\Admin\Common;

class Init extends \Controller\Admin\Common\Common {

	public function config() {

		$conf  = require(__CONF__.'/Home/config.php');
		\Ypf\Lib\Config::$config = array_merge(\Ypf\Lib\Config::$config,$conf);
		
	}
}

?>