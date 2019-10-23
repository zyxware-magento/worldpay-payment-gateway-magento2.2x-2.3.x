<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Logger;

class WorldpayLogger extends \Monolog\Logger
{
    public function addRecord($level, $message, array $context = [])
    {
        $ObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logEnabled = (bool) $ObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
                                ->getValue('worldpay/general_config/enable_logging');
        if ($logEnabled) {
            return parent::addRecord($level, $message, $context);
        }
    }

}
