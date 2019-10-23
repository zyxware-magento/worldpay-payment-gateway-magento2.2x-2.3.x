<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\ResourceModel\Collection;

/**
 * Worldpay payment collection	 
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Zyxware\Worldpay\Model\Worldpayment','Zyxware\Worldpay\Model\ResourceModel\Worldpayment');
    }
}