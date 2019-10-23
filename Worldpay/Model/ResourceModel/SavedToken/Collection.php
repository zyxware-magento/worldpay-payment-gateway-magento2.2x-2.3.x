<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\ResourceModel\SavedToken;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * SavedToken collection   
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
            'Zyxware\Worldpay\Model\SavedToken',
            'Zyxware\Worldpay\Model\ResourceModel\SavedToken'
        );
    }
}
