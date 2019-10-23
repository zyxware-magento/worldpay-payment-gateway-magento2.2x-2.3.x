<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Authorisation;

use Exception;

class VaultService extends \Magento\Framework\DataObject
{
    public function __construct(
        \Zyxware\Worldpay\Model\Mapping\Service $mappingservice,
        \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
        \Zyxware\Worldpay\Model\Response\DirectResponse $directResponse,
        \Zyxware\Worldpay\Model\Payment\UpdateWorldpaymentFactory $updateWorldPayPayment,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice
    ) {
        $this->mappingservice = $mappingservice;
        $this->paymentservicerequest = $paymentservicerequest;
        $this->directResponse = $directResponse;
        $this->paymentservice = $paymentservice;
        $this->updateWorldPayPayment = $updateWorldPayPayment;
    }
    public function authorizePayment(
        $mageOrder,
        $quote,
        $orderCode,
        $orderStoreId,
        $paymentDetails,
        $payment
    ) {
        $directOrderParams = $this->mappingservice->collectVaultOrderParameters(
            $orderCode,
            $quote,
            $orderStoreId,
            $paymentDetails
        );

        $response = $this->paymentservicerequest->order($directOrderParams);
        $directResponse = $this->directResponse->setResponse($response);
        $this->updateWorldPayPayment->create()->updateWorldpayPayment($directResponse, $payment);
        $this->_applyPaymentUpdate($directResponse, $payment);
    }
    private function _applyPaymentUpdate(
        \Zyxware\Worldpay\Model\Response\DirectResponse $directResponse,
        $payment
    ) {
        $paymentUpdate = $this->paymentservice->createPaymentUpdateFromWorldPayXml($directResponse->getXml());
        $paymentUpdate->apply($payment);
        $this->_abortIfPaymentError($paymentUpdate);
    }
    private function _abortIfPaymentError($paymentUpdate)
    {
        if ($paymentUpdate instanceof \Zyxware\WorldPay\Model\Payment\Update\Refused) {
             throw new Exception(sprintf('Payment REFUSED'));
         }

        if ($paymentUpdate instanceof \Zyxware\WorldPay\Model\Payment\Update\Cancelled) {
            throw new Exception(sprintf('Payment CANCELLED'));
        }

        if ($paymentUpdate instanceof \Zyxware\WorldPay\Model\Payment\Update\Error) {
            throw new Exception(sprintf('Payment ERROR'));
        }
    }
}
