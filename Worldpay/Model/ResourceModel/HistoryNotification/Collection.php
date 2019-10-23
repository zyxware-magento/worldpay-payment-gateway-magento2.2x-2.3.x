<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\ResourceModel\HistoryNotification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * HistoryNotification collection   
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Zyxware\Worldpay\Model\HistoryNotification',
            'Zyxware\Worldpay\Model\ResourceModel\HistoryNotification'
        );
    }
}
