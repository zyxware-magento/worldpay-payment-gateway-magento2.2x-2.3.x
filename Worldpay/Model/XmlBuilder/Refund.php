<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\XmlBuilder;

/**
 * Build xml for Refund request
 */
class Refund
{ 
    const EXPONENT = 2;
    const ROOT_ELEMENT = <<<EOD
<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE paymentService PUBLIC '-//WorldPay/DTD WorldPay PaymentService v1//EN'
        'http://dtd.worldpay.com/paymentService_v1.dtd'> <paymentService/>
EOD;

    private $merchantCode;
    private $orderCode;
    private $currencyCode;
    private $amount;
    private $refundReference;

    /**
     * Build xml for processing Request
     * @param string $merchantCode
     * @param string $orderCode
     * @param string $currencyCode
     * @param float $amount
     * @param string $refundReference
     * @return SimpleXMLElement $xml
     */
    public function build($merchantCode, $orderCode, $currencyCode, $amount, $refundReference)
    {
        $this->merchantCode = $merchantCode;
        $this->orderCode = $orderCode;
        $this->currencyCode = $currencyCode;
        $this->amount = $amount;
        $this->refundReference = $refundReference;

        $xml = new \SimpleXMLElement(self::ROOT_ELEMENT);
        $xml['merchantCode'] = $this->merchantCode;
        $xml['version'] = '1.4';

        $modify = $this->_addModifyElement($xml);
        $orderModification = $this->_addOrderModificationElement($modify);
        $this->_addRefundElement($orderModification);

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
     * @return SimpleXMLElement $orderModification
     */
    private function _addOrderModificationElement($modify)
    {
        $orderModification = $modify->addChild('orderModification');
        $orderModification['orderCode'] = $this->orderCode;

        return $orderModification;
    }

    /**
     * @param SimpleXMLElement $orderModification
     */
    private function _addRefundElement($orderModification)
    {
        $refund = $orderModification->addChild('refund');
        $refund["reference"] = $this->refundReference ;

        $amountElement = $refund->addChild('amount');
        $amountElement['value'] = $this->_amountAsInt($this->amount);
        $amountElement['currencyCode'] = $this->currencyCode;
        $amountElement['exponent'] = self::EXPONENT;
    }

    /**
     * @param float $amount
     * @return int
     */
    private function _amountAsInt($amount)
    {
        return round($amount, self::EXPONENT, PHP_ROUND_HALF_EVEN) * pow(10, self::EXPONENT);
    }
}
