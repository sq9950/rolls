<?php

/**
 * The yundun admin v3 project.
 *
 * @author Qingshan Luo <shanshan.lqs@gmail.com>
 */

namespace Service;

use stdClass;
use Ypf\Core\Service as BaseService;
use Ypf\Lib\Config;

abstract class Service extends BaseService
{
    const PAGE_SIZE = 20;

    public $models;
    public $ret = ['status' => 0, 'info' => '操作失败'];

    /**
     * The service constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->models    = new stdClass();
    }

    /**
     * 初始化服务层需要用的模型抽象方法
     *
     * @return void
     */
    abstract public function initModels();

    public function __get($key) {
        return $key == 'configall' ? Config::$config : parent::__get($key);
    }

}
