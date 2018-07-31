<?php
namespace GetResponse\GetResponseIntegration\Logger;

use Monolog\Logger;

/**
 * Class Handler
 * @package GetResponse\GetResponseIntegration\Logger
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/getresponse.log';
}