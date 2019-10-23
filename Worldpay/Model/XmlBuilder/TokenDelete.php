<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\XmlBuilder;

use Zyxware\Worldpay\Model\XmlBuilder\Config\ThreeDSecureConfig;
use \Zyxware\Worldpay\Logger\WorldpayLogger;

/**
 * Build xml for delete token request
 */
class TokenDelete
{
    const TOKEN_SCOPE = 'shopper';
    const ROOT_ELEMENT = <<<EOD
<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE paymentService PUBLIC '-//WorldPay/DTD WorldPay PaymentService v1//EN'
        'http://dtd.worldpay.com/paymentService_v1.dtd'> <paymentService/>
EOD;

    /**
     * @var Mage_Customer_Model_Customer
     */
    private $customer;

    /**
     * @var Sapient_WorldPay_Model_Token
     */
    private $tokenModel;

    /**
     * @var string
     */
    protected $merchantCode;

    public function __construct(array $args = array())
    {
        if (isset($args['tokenModel']) && $args['tokenModel'] instanceof \Sapient\WorldPay\Model\SavedToken) {
            $this->tokenModel = $args['tokenModel'];
        }

        if (isset($args['customer']) && $args['customer'] instanceof \Magento\Customer\Model\Customer) {
            $this->customer = $args['customer'];
        }

        if (isset($args['merchantCode'])) {
            $this->merchantCode = $args['merchantCode'];
        }
    }

    /**
     * Build xml for processing Request
     * @return SimpleXMLElement $xml
     */
    public function build()
    {
        $xml = new \SimpleXMLElement(self::ROOT_ELEMENT);
        $xml['version'] = '1.4';
        $xml['merchantCode'] = $this->merchantCode;

        $modify = $this->_addModifyElement($xml);
        $this->_addTokenUpdateElement($modify);

        return $xml;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    private function _addModifyElement($xml)
    {
        return $xml->addChild('modify');
    }

    /**
     * @param SimpleXMLElement $modify
     * @return SimpleXMLElement
     */
    private function _addTokenUpdateElement($modify)
    {
        $tokenDelete = $modify->addChild('paymentTokenDelete');
        $tokenDelete['tokenScope'] = self::TOKEN_SCOPE;
        $tokenDelete->addChild('paymentTokenID', $this->tokenModel->getTokenCode());
        $tokenDelete->addChild('authenticatedShopperID', $this->customer->getId());
        return $tokenDelete;
    }
}
