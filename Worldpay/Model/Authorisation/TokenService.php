<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Authorisation;
use Exception;

class TokenService extends \Magento\Framework\DataObject
{
    protected $_session;
    protected $updateWorldPayPayment;

    public function __construct(
        \Zyxware\Worldpay\Model\Mapping\Service $mappingservice,
        \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Response\DirectResponse $directResponse,
        \Zyxware\Worldpay\Model\Payment\UpdateWorldpaymentFactory $updateWorldPayPayment,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Zyxware\Worldpay\Helper\Data $worldpayHelper,
        \Zyxware\Worldpay\Helper\Registry $registryhelper
        )
    {
       $this->mappingservice = $mappingservice;
       $this->paymentservicerequest = $paymentservicerequest;
       $this->wplogger = $wplogger;
       $this->directResponse = $directResponse;
       $this->paymentservice = $paymentservice;
       $this->updateWorldPayPayment = $updateWorldPayPayment;
       $this->checkoutSession = $checkoutSession;
       $this->worldpayHelper = $worldpayHelper;
       $this->registryhelper = $registryhelper;
    }

    public function authorizePayment(
        $mageOrder,
        $quote,
        $orderCode,
        $orderStoreId,
        $paymentDetails,
        $payment
    ) {
        $tokenOrderParams = $this->mappingservice->collectTokenOrderParameters(
            $orderCode,
            $quote,
            $orderStoreId,
            $paymentDetails
        );

        $response = $this->paymentservicerequest->orderToken($tokenOrderParams);
        $directResponse = $this->directResponse->setResponse($response);

        $threeDSecureParams = $directResponse->get3dSecureParams();
        $threeDsEnabled = $this->worldpayHelper->is3DSecureEnabled();
        if ($threeDSecureParams) {
            // Handles success response with 3DS & redirect for varification.
            $this->checkoutSession->setauthenticatedOrderId($mageOrder->getIncrementId());
            $payment->setIsTransactionPending(1);
            $this->_handle3DSecure($threeDSecureParams, $tokenOrderParams, $orderCode);
        }else{
            // Normal order goes here.(without 3DS).
            $this->updateWorldPayPayment->create()->updateWorldpayPayment($directResponse, $payment);
            $this->_applyPaymentUpdate($directResponse, $payment);
        }
        $quote->setActive(false);
    }
    private function _handle3DSecure($threeDSecureParams, $directOrderParams, $mageOrderId)
    {
        $this->wplogger->info('HANDLING 3DS');
        $this->registryhelper->setworldpayRedirectUrl($threeDSecureParams);
        $this->checkoutSession->set3DSecureParams($threeDSecureParams);
        $this->checkoutSession->setDirectOrderParams($directOrderParams);
        $this->checkoutSession->setAuthOrderId($mageOrderId);
    }
    private function _applyPaymentUpdate(
        \Zyxware\Worldpay\Model\Response\DirectResponse $directResponse,
        $payment)
    {
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
    }
}
