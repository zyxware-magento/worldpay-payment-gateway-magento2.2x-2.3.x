<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model;
/**
 * Resource Model
 */
class HistoryNotification extends \Magento\Framework\Model\AbstractModel
{
	/**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Zyxware\Worldpay\Model\ResourceModel\HistoryNotification');
    }
}
