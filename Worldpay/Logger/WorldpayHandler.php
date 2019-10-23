<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Logger;

use Monolog\Logger;

class WorldpayHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/worldpay.log';
}
