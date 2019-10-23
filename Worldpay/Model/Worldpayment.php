<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model;

/**
 * Resource Model
 */
class Worldpayment extends \Magento\Framework\Model\AbstractModel 
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Zyxware\Worldpay\Model\ResourceModel\Worldpayment');
    }

    /**
     * Retrieve worldpay payment Details
     *
     * @return Zyxware\Worldpay\Model\Worldpayment
     */
    public function loadByPaymentId($orderId)
    {

        if (!$orderId) {
            return;         
        }
        $id = $this->getResource()->loadByPaymentId($orderId);
        return $this->load($id);
        
    }

    /**
     * Load worldpay payment Details
     *
     * @return Zyxware\Worldpay\Model\Worldpayment
     */
    public function loadByWorldpayOrderId($order_id)
    {
        if (!$order_id) {
            return;         
        }
        $id = $this->getResource()->loadByWorldpayOrderId($order_id);
        return $this->load($id);  
    }
 
   
}