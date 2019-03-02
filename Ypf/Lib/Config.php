<?php
namespace Ypf\Lib;
/**
 * 
 * 配置
 *
 */
class Config
{
    /**
     * 配置数据
     * @var array
     */
    public static $config = array();
    
    public static $path = array();
    
    protected static $instances = null;
    
    /**
     * 构造函数
     * @param empty|string|array $config
     */
    public function __construct()
    {
     	$args = func_get_args();
     	if(!empty($args)) {
     		foreach($args as $path) {
     			$this->load($path);
     		}
     	}
     	self::$instances = &$this;
	}

	public function load($path) 
	{
		if(is_file($path)) return self::parseFile($path);
		
        foreach(glob($path. '/*.conf') as $config_file)
        {
        	self::$path[] = $path;
            $name = basename($config_file, '.conf');
            self::$config[$name] = self::parseFile($config_file);
        }
	}

    /**
     * {
     *      ["common_config.php"],              //如果没有第二项或为空，则合并到第一维
     *      ["meal_config.php", "meal"],        //如果有第二项或为空，则合并到第二维, 如：{'meal' => {...}}
     *      ["order_config.php", "order"],
     * }
     */
    public static function loadPhps($files) {
        foreach($files as $row) {
            $cfg = require $row[0];
            if(isset($row[1]) && (is_string($row[1]) || is_numeric($row[1]))) {
                self::$config[$row[1]] = array_merge(self::$config[$row[1]], $cfg);
            } else {
                self::$config = array_merge(self::$config, $cfg);
            }
        }
    }

    /**
     * 解析配置文件
     * @param string $config_file
     * @throws \Exception
     */
    protected static function parseFile($config_file)
    {
        $config = parse_ini_file($config_file, true);
        if (!is_array($config) || empty($config))
        {
            throw new \Exception('Invalid configuration format');
        }
        return $config;
    }

    public static function getInstance() {
        return self::$instances;
    }
    /**
     * 获取配置
     * @param string $uri
     * @return mixed
     */
    public function get($uri)
    {
        $node = self::$config;
        $paths = explode('.', $uri);
        while (!empty($paths)) {
            $path = array_shift($paths);
            if (!isset($node[$path])) {
                return null;
            }
            $node = $node[$path];
        }
        return $node;
    }
    
    /**
     * @return array
     */
    public static function getAll()
    {
         $copy = self::$config;
         return $copy;
    }
	
	public static function clear()
	{
		 self::$config = array();
	}
	
	public function set($node, $data)
	{
		$paths = str_replace('.', '\'][\'', $node);
		$t = '[\'' . $paths . '\']';
		//echo $t;
		//self::$config{$$t} = $data;
	}
    
}
