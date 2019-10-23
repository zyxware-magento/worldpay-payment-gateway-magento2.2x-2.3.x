<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Token;

use Zyxware\Worldpay\Model\SavedToken;
/** 
 * Communicate with WP server and gives back meaningful answer object
 */
class Service 
{

    /**
     * @var Zyxware\WorldPay\Model\Request\PaymentServiceRequest
     */
    protected $_paymentServiceRequest;

    /**
     * Constructor
     *
     * @param \Zyxware\Worldpay\Model\Payment\Update\Factory $paymentupdatefactory
     * @param \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest
     * @param \Zyxware\Worldpay\Model\Worldpayment $worldpayPayment
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     */
    public function __construct(
        \Zyxware\Worldpay\Model\Payment\Update\Factory $paymentupdatefactory,
        \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
        \Zyxware\Worldpay\Model\Worldpayment $worldpayPayment,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
    ) {
        $this->_wplogger = $wplogger;
        $this->paymentupdatefactory = $paymentupdatefactory;
        $this->_paymentServiceRequest = $paymentservicerequest;
        $this->worldpayPayment = $worldpayPayment;
    }

    /**
     * Send token update request to WP server and gives back the answer
     *
     * @param Zyxware\Worldpay\Model\Token $tokenModel
     * @param \Magento\Customer\Model\Customer $customer
     * @param $storeId
     * @return Zyxware\Worldpay\Model\Token\UpdateXml
     */
    public function getTokenUpdate(
        SavedToken $tokenModel,
        \Magento\Customer\Model\Customer $customer,
        $storeId
    ) {
        $rawXml = $this->_paymentServiceRequest->tokenUpdate($tokenModel, $customer, $storeId);
        $xml = simplexml_load_string($rawXml);
        return new UpdateXml($xml);
    }

    /**
     * Send token delete request to WP server and gives back the answer
     *
     * @param Zyxware\Worldpay\Model\Token $tokenModel
     * @param \Magento\Customer\Model\Customer $customer
     * @param $storeId
     * @return Zyxware\Worldpay\Model\Token\DeleteXml
     */
    public function getTokenDelete(
        SavedToken $tokenModel,
        \Magento\Customer\Model\Customer $customer,
        $storeId
    ) {
        $rawXml = $this->_paymentServiceRequest->tokenDelete($tokenModel, $customer, $storeId);
        $xml = simplexml_load_string($rawXml);
        return new DeleteXml($xml);
    }
}
