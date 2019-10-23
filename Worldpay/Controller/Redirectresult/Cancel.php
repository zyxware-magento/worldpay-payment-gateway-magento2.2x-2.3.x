<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Redirectresult;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Zyxware\Worldpay\Model\Payment\StateResponse as PaymentStateResponse;

/**
 * if got notification to get cancel order from worldpay then redirect to  cart page and display the notice
 */

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice
     * @param \Zyxware\Worldpay\Model\Checkout\Service $checkoutservice
     * @param \Zyxware\Worldpay\Model\Payment\Service $paymentservice
     * @param \Zyxware\Worldpay\Model\Request\AuthenticationService $authenticatinservice
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Zyxware\Worldpay\Model\Order\Service $orderservice,
        \Zyxware\Worldpay\Model\Checkout\Service $checkoutservice,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
        \Zyxware\Worldpay\Model\Request\AuthenticationService $authenticatinservice,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
    ) {
        $this->pageFactory = $pageFactory;
        $this->orderservice = $orderservice;
        $this->wplogger = $wplogger;
        $this->checkoutservice = $checkoutservice;
        $this->paymentservice = $paymentservice;
        $this->authenticatinservice = $authenticatinservice;
        return parent::__construct($context);

    }

    public function execute()
    {

        $this->wplogger->info('worldpay returned cancel url');
        if (!$this->orderservice->getAuthorisedOrder()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart', ['_current' => true]);
        }
        $order = $this->orderservice->getAuthorisedOrder();
        $magentoorder = $order->getOrder();
        $notice = $this->_getCancellationNoticeForOrder($magentoorder);
        $this->messageManager->addNotice($notice);
        $params = $this->getRequest()->getParams();
        if ($this->authenticatinservice->requestAuthenticated($params)) {
            if (isset($params['orderKey'])) {
                $this->_applyPaymentUpdate(PaymentStateResponse::createFromCancelledResponse($params), $order);
            }
        }
        return $this->resultRedirectFactory->create()->setPath('checkout/cart', ['_current' => true]);
    }

    private function _getCancellationNoticeForOrder($order)
    {

        $incrementId = $order->getIncrementId();

        $message = is_null($incrementId)
            ? __('Order Cancelled')
            : __('Order #'. $incrementId.' Cancelled');

        return $message;
    }

    private function _applyPaymentUpdate($paymentState, $order)
    {
        try {
            $this->_paymentUpdate = $this->paymentservice
                        ->createPaymentUpdateFromWorldPayResponse($paymentState);
            $this->_paymentUpdate->apply($order->getPayment(), $order);
        } catch (\Exception $e) {
            $this->wplogger->error($e->getMessage());
        }
    }

}
