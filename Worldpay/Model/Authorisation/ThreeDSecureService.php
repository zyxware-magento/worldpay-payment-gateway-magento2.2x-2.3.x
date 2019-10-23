<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Authorisation;
use Exception;

class ThreeDSecureService extends \Magento\Framework\DataObject
{
    /** @var \Zyxware\Worldpay\Model\Payment\UpdateWorldpaymentFactory */
    protected $updateWorldPayPayment;

    const CART_URL = 'checkout/cart';

    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
     * @param \Zyxware\Worldpay\Model\Response\DirectResponse $directResponse,
     * @param \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
     * @param \Magento\Checkout\Model\Session $checkoutSession,
     * @param \Magento\Framework\UrlInterface $urlBuilder,
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice,
     * @param \Magento\Framework\Message\ManagerInterface $messageManager,
     * @param \Zyxware\Worldpay\Model\Payment\UpdateWorldpaymentFactory $updateWorldPayPayment,
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Response\DirectResponse $directResponse,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Zyxware\Worldpay\Model\Order\Service $orderservice,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Zyxware\Worldpay\Model\Payment\UpdateWorldpaymentFactory $updateWorldPayPayment,
        \Magento\Customer\Model\Session $customerSession,
        \Zyxware\Worldpay\Model\Token\WorldpayToken $worldpaytoken
    ) {
        $this->paymentservicerequest = $paymentservicerequest;
        $this->wplogger = $wplogger;
        $this->directResponse = $directResponse;
        $this->paymentservice = $paymentservice;
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilders    = $urlBuilder;
        $this->orderservice = $orderservice;
        $this->_messageManager = $messageManager;
        $this->updateWorldPayPayment = $updateWorldPayPayment;
        $this->customerSession = $customerSession;
        $this->worldpaytoken = $worldpaytoken;
    }
    public function continuePost3dSecureAuthorizationProcess($paResponse, $directOrderParams, $threeDSecureParams)
    {
        $directOrderParams['paResponse'] = $paResponse;
        $directOrderParams['echoData'] = $threeDSecureParams->getEchoData();
        // @setIs3DSRequest flag set to ensure whether it is 3DS request or not.
        // To add cookie for 3DS second request.
        $this->checkoutSession->setIs3DSRequest(true);
        try {
            $response = $this->paymentservicerequest->order3DSecure($directOrderParams);
            $this->response = $this->directResponse->setResponse($response);
            // @setIs3DSRequest flag is unset from checkout session.
            $this->checkoutSession->unsIs3DSRequest();
            $orderIncrementId = current(explode('-', $directOrderParams['orderCode']));
            $this->_order = $this->orderservice->getByIncrementId($orderIncrementId);
            $this->_paymentUpdate = $this->paymentservice->createPaymentUpdateFromWorldPayXml($this->response->getXml());
            $this->_paymentUpdate->apply($this->_order->getPayment(), $this->_order);
            $this->_abortIfPaymentError($this->_paymentUpdate);
        } catch (Exception $e) {
            $this->wplogger->info($e->getMessage());
            $this->_messageManager->addError(__($e->getMessage()));
            $this->checkoutSession->setWpResponseForwardUrl(
                  $this->urlBuilders->getUrl(self::CART_URL, ['_secure' => true])
            );
            return;
        }

    }

    /**
     * help to build url if payment is success
     */
    private function _handleAuthoriseSuccess()
    {
        $this->checkoutSession->setWpResponseForwardUrl(
            $this->urlBuilders->getUrl('checkout/onepage/success',array('_secure' => true))
        );
    }

    /**
     * it handles if payment is refused or cancelled
     * @param  Object $paymentUpdate
     */
    private function _abortIfPaymentError($paymentUpdate)
    {
        if ($paymentUpdate instanceof \Zyxware\WorldPay\Model\Payment\Update\Refused) {
          $this->_messageManager->addError(__('Unfortunately the order could not be processed. Please contact us or try again later.'));
             $this->checkoutSession->setWpResponseForwardUrl(
              $this->urlBuilders->getUrl(self::CART_URL, ['_secure' => true])
            );
        } elseif ($paymentUpdate instanceof \Zyxware\WorldPay\Model\Payment\Update\Cancelled) {
            $this->_messageManager->addError(__('Unfortunately the order could not be processed. Please contact us or try again later.'));
            $this->checkoutSession->setWpResponseForwardUrl(
              $this->urlBuilders->getUrl(self::CART_URL, ['_secure' => true])
            );
        } else {
            $this->orderservice->removeAuthorisedOrder();
            $this->_handleAuthoriseSuccess();
            $this->_updateTokenData($this->response->getXml());
        }
    }

    /**
     * This will Save card
     * @param xml $xmlResponseData
     */
    private function _updateTokenData($xmlResponseData)
    {
        if ($this->customerSession->getIsSavedCardRequested()) {
            $tokenData = $xmlResponseData->reply->orderStatus->token;
            $paymentData = $xmlResponseData->reply->orderStatus->payment;
            $merchantCode = $xmlResponseData['merchantCode'];
            if ($tokenData) {
                $this->_applyTokenUpdate($xmlResponseData);
            }
            $this->customerSession->unsIsSavedCardRequested();
        }
    }

    private function _applyTokenUpdate($xmlRequest)
    {
        $tokenService = $this->worldpaytoken;
        $tokenService->updateOrInsertToken(
             new \Zyxware\Worldpay\Model\Token\StateXml($xmlRequest), $this->_order->getPayment()
        );
    }
}
