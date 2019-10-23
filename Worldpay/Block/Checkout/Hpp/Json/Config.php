<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Block\Checkout\Hpp\Json;

class Config extends \Magento\Framework\View\Element\Template
{

	/**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Zyxware\Worldpay\Model\Checkout\Hpp\Json\Config\Factory $configfactory
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param array $data
     */	
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Zyxware\Worldpay\Model\Checkout\Hpp\Json\Config\Factory $configfactory,	
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger, 
        array $data = []               
    ) {    	
        $this->configfactory = $configfactory;
        $this->wplogger = $wplogger;
        parent::__construct($context, $data);
    }
    /**
    * @return json
    */
    protected function _toHtml()
    {
        return json_encode($this->_getConfig());

    }
    /**
    * Retrive config for checkout Iframe 
    *
    * @return array
    */
    private function _getConfig()
    {    

        $configFactory = $this->configfactory;
        $config = $configFactory->create('checkoutWorldPayLibraryObject', 'checkout-payment-worldpay-container');
        $jsConfig = array(
            'type' => $config->getType(),
            'iframeIntegrationId' => $config->getIframeIntegrationID(),
            'iframeHelperURL' => $config->getIframeHelperURL(),
            'iframeBaseURL' => $config->getIframeBaseURL(),
            'url' => $config->getUrl(),
            'target' => $config->getTarget(),
            'debug' => $config->isDebug(),
            'language' => $config->getLanguage(),
            'country' => $config->getCountry(),
            'preferredPaymentMethod' => (string)$config->getPreferredPaymentMethod(),
            'successURL' => $config->getUrlConfig()->getSuccessURL(),
            'cancelURL' => $config->getUrlConfig()->getCancelURL(),
            'failureURL' => $config->getUrlConfig()->getFailureURL(),
            'pendingURL' => $config->getUrlConfig()->getPendingURL(),
            'errorURL' => $config->getUrlConfig()->getErrorURL(),
        );
        return $jsConfig;
    }
}