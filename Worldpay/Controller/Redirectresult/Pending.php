<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Redirectresult;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Zyxware\Worldpay\Model\Payment\StateResponse as PaymentStateResponse;

/**
 * after deleting the card redirect to the pending page
 */
class Pending extends \Magento\Framework\App\Action\Action
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
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Zyxware\Worldpay\Model\Order\Service $orderservice,
        \Zyxware\Worldpay\Model\Checkout\Service $checkoutservice,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
    ) {
        $this->pageFactory = $pageFactory;
        $this->wplogger = $wplogger;
        $this->orderservice = $orderservice;
        $this->checkoutservice = $checkoutservice;
        $this->paymentservice = $paymentservice;
        return parent::__construct($context);

    }

    public function execute()
    {
        $paymentType = '';
        $this->wplogger->info('worldpay returned pending url');
        if (!$this->orderservice->getAuthorisedOrder()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart', ['_current' => true]);
        }
        $order = $this->orderservice->getAuthorisedOrder();
        $magentoorder = $order->getOrder();
        $params = $this->getRequest()->getParams();
        try {
            if ($params) {
                $worldPayPayment = $order->getWorldPayPayment();
                $paymentType = $worldPayPayment->getPaymentType();
                $this->_applyPaymentUpdate(PaymentStateResponse::createFromPendingResponse($params, $paymentType), $order);
            }
        } catch (\Exception $e) {
            $this->wplogger->error($e->getMessage());
            $this->checkoutservice->clearSession();
            $this->orderservice->removeAuthorisedOrder();
            $this->wplogger->error($e->getMessage());
            if ($e->getMessage() == 'invalid state transition') {
                 return $this->pageFactory->create();
            } else {
                 return $this->resultRedirectFactory->create()->setPath('checkout/cart', ['_current' => true]);
            }
        }
        $this->checkoutservice->clearSession();
        $this->orderservice->removeAuthorisedOrder();
        return $this->pageFactory->create();
    }

    private function _applyPaymentUpdate($paymentState, $order)
    {
        try {
            $this->_paymentUpdate = $this->paymentservice
                                    ->createPaymentUpdateFromWorldPayResponse($paymentState);
            $this->_paymentUpdate->apply($order->getPayment(), $order);
        } catch (\Exception $e) {
            $this->wplogger->error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
