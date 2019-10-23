<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Resource Model
 */
class SavedToken extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Zyxware\Worldpay\Model\ResourceModel\SavedToken');
    }

    /**
     * Load worldpay token Details
     *
     * @return Zyxware\Worldpay\Model\SavedToken
     */
    public function loadByTokenCode($order_id)
    {
       if (!$order_id) {
           return;         
        }
        $id = $this->getResource()->loadByTokenCode($order_id);
        return $this->load($id);  
    }
}
