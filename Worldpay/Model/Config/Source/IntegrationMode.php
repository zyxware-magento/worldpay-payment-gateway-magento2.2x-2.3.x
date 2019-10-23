<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Config\Source;

class IntegrationMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {

        return [
            ['value' => 'direct', 'label' => __('Direct')],
            ['value' => 'redirect', 'label' => __('Redirect')],
        ];
    }
}