<?php
/**
 * Desc: log interface
 * Created by PhpStorm.
 * User: <gaolu@yundun.com>
 * Date: 2017/3/12 16:38
 */

namespace Service\Psr3Log;
require __ROOT__ . '/vendor/autoload.php';

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Monolog
{

    private $logger;

    public function __construct()
    {
//        parent::__construct();
    }


    public function getLoggerInstance($name = 'default', $stream_log = '/tmp/home-v4-cli.log', $level = Logger::DEBUG)
    {
        // Create the logger
        $this->logger = new Logger($name);
        // Now add some handlers
        $streamHandler = new StreamHandler($stream_log, $level);
        $streamHandler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra% \n", '', true));
        $this->logger->pushHandler($streamHandler);
        $error_handler = new ErrorLogHandler(4, $level, true, true);
        $error_handler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra% \n", '', true));
        $this->logger->pushHandler($error_handler);

        return $this->logger;
    }


}