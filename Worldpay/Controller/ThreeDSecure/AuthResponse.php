<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\ThreeDSecure;

class AuthResponse extends \Magento\Framework\App\Action\Action
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession     
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender     
     * @param \Zyxware\Worldpay\Model\Authorisation\ThreeDSecureService $threedsredirectresponse
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger     
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Zyxware\Worldpay\Model\Authorisation\ThreeDSecureService $threedsredirectresponse,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
    ) {
        $this->wplogger = $wplogger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlBuilder = $context->getUrl();
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->threedsredirectresponse = $threedsredirectresponse;
        parent::__construct($context);
    }

    /**
     * Accepts callback from worldpay's 3D Secure page. If payment has been
     * authorised, update order and redirect to the checkout success page.
     */
    public function execute()
    {
        $directOrderParams = $this->checkoutSession->getDirectOrderParams();
        $threeDSecureParams = $this->checkoutSession->get3DSecureParams();
        $this->checkoutSession->unsDirectOrderParams();
        $this->checkoutSession->uns3DSecureParams();
        try {
            $this->threedsredirectresponse->continuePost3dSecureAuthorizationProcess(
                $this->getRequest()->getParam('PaRes'), $directOrderParams, $threeDSecureParams
            );
        } catch (\Exception $e) {
            $this->wplogger->error($e->getMessage());
            $this->wplogger->error('3DS Failed');
            $this->_messageManager->addError(__('Unfortunately the order could not be processed. Please contact us or try again later.'));
            $this->getResponse()->setRedirect($this->urlBuilders->getUrl('checkout/cart', ['_secure' => true]));
        }
        $redirectUrl = $this->checkoutSession->getWpResponseForwardUrl();
        $this->checkoutSession->unsWpResponseForwardUrl();
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
