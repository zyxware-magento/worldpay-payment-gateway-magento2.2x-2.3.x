<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Helper;

class Registry extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_registry;
    public function __construct(\Magento\Framework\App\Helper\Context $context,
         \Magento\Framework\Registry $registry
    ) {

         $this->_registry = $registry;
         parent::__construct($context);
    }

    public function removeAllData()
    {
        $keys = array(
            'worldpayRedirectUrl',
        );

        foreach ($keys as $key) {
            $this->removeDataFromRegistry($key);
        }

        return $this;
    }

    public function getworldpayRedirectUrl()
    {
        return $this->getDataFromRegistry('worldpayRedirectUrl');
    }

    public function setworldpayRedirectUrl($data)
    {
        return $this->addDataToRegistry('worldpayRedirectUrl', $data);
    }

    public function addDataToRegistry($name, $data)
    {
        $this->removeDataFromRegistry($name);

        $this->_registry->register($name, $data);

        return $this;

    }

    public function getDataFromRegistry($name)
    {
        if ($data = $this->_registry->registry($name)) {
            return $data;
        }

        return false;
    }

    public function removeDataFromRegistry($name)
    {
        $this->_registry->unregister($name);

        return $this;
    }
}
