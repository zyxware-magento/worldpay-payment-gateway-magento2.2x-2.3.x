<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment;

class Service
{  
    /** @var \Zyxware\Worldpay\Model\Request\PaymentServiceRequest */
    protected $_paymentServiceRequest;    
    protected $_adminhtmlResponse;
    /** @var \Zyxware\Worldpay\Model\Payment\Update\Factory */
    protected $_paymentUpdateFactory;
    /** @var \Zyxware\Worldpay\Model\Request\PaymentServiceRequest */
    protected $_redirectResponse;
    protected $_paymentModel;
    protected $_helper;
    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\Payment\State $paymentState
     * @param \Zyxware\Worldpay\Model\Payment\WorldPayPayment $worldPayPayment
     * @param \Zyxware\Worldpay\Helper\Data $configHelper        
     */
    public function __construct(
         \Zyxware\Worldpay\Model\Payment\Update\Factory $paymentupdatefactory,
         \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
         \Zyxware\Worldpay\Model\Worldpayment $worldpayPayment
    ) {
        $this->paymentupdatefactory = $paymentupdatefactory;
        $this->paymentservicerequest = $paymentservicerequest;
        $this->worldpayPayment = $worldpayPayment;
    }

    public function createPaymentUpdateFromWorldPayXml($xml)
    {
        return $this->_getPaymentUpdateFactory()
            ->create(new \Zyxware\Worldpay\Model\Payment\StateXml($xml));
    }

    protected function _getPaymentUpdateFactory()
    {
        if ($this->_paymentUpdateFactory === null) {
            $this->_paymentUpdateFactory = $this->paymentupdatefactory;
        }

        return $this->_paymentUpdateFactory;
    }

    public function createPaymentUpdateFromWorldPayResponse(\Zyxware\Worldpay\Model\Payment\State $state)
    {
        return $this->_getPaymentUpdateFactory()
            ->create($state);
    }

    public function getPaymentUpdateXmlForOrder(\Zyxware\Worldpay\Model\Order $order)
    {
        $worldPayPayment = $order->getWorldPayPayment();

        if (!$worldPayPayment) {
            return false;
        }
        $rawXml = $this->paymentservicerequest->inquiry(
            $worldPayPayment->getMerchantId(),
            $worldPayPayment->getWorldpayOrderId(),
            $worldPayPayment->getStoreId(),
            $order->getPaymentMethodCode(),
            $worldPayPayment->getPaymentType()
        );

        return simplexml_load_string($rawXml);
    }

    public function setGlobalPaymentByPaymentUpdate($paymentUpdate)
    {
        $this->worldpayPayment->loadByWorldpayOrderId($paymentUpdate->getTargetOrderCode());
    }
}
