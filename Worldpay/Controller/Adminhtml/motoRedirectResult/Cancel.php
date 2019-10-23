<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Adminhtml\motoRedirectResult;

use Zyxware\Worldpay\Model\Payment\StateResponse as PaymentStateResponse;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Exception;

/**
 * if payment is canceled redirect to admin create order page
 */ 
class Cancel extends \Magento\Backend\App\Action
{
    protected $pageFactory;
    protected $_rawBody;

    private $_paymentUpdate;


    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Model\Payment\Service $paymentservice
     * @param \Zyxware\Worldpay\Model\Request\AuthenticationService $authenticatinservice
     * @param \Zyxware\Worldpay\Model\Adminhtml\Order\Service $adminorderservice
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice
     */
    public function __construct(Context $context,  JsonFactory $resultJsonFactory,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
        \Zyxware\Worldpay\Model\Request\AuthenticationService $authenticatinservice,
        \Zyxware\Worldpay\Model\Adminhtml\Order\Service $adminorderservice,
        \Zyxware\Worldpay\Model\Order\Service $orderservice
    ) { 
       
        parent::__construct($context);
        $this->wplogger = $wplogger;
        $this->paymentservice = $paymentservice;
        $this->orderservice = $orderservice;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->adminorderservice = $adminorderservice;
        $this->authenticatinservice = $authenticatinservice;

    }
    /**
     * Execute if payment is canceled
     */
    public function execute()
    {
        $this->wplogger->info('worldpay returned admin cancel url');
        $worldPayOrder = $this->_getWorldPayOrder();

        $notice = $this->_getCancellationNoticeForOrder($worldPayOrder->getOrder());
        $this->messageManager->getMessages(true);
        $this->messageManager->addNotice($notice);
        $this->adminorderservice->reactivateAdminQuoteForOrder($worldPayOrder);

        $params = $this->getRequest()->getParams();
        if ($this->authenticatinservice->requestAuthenticated($params)) {
            $this->_applyPaymentUpdate(PaymentStateResponse::createFromCancelledResponse($params), $worldPayOrder);
        }

        return $this->_redirectToCreateOrderPage();
    }

    /**
     * @return \Zyxware\Worldpay\Model\Order
     */
    private function _getWorldPayOrder()
    {
        return $this->orderservice->getByIncrementId($this->_getOrderIncrementId());
    }

    /**
     * @return string
     */
    private function _getCancellationNoticeForOrder($order)
    {

        $incrementId = $order->getIncrementId();

        $message = is_null($incrementId)
            ? __('Order Cancelled')
            : __('Order #'. $incrementId.' Cancelled');

        return $message;
    }

    /**
     * @return string
     */
    private function _getOrderIncrementId()
    {
        $params = $this->getRequest()->getParams();
        preg_match('/\^(\d+)-/', $params['orderKey'], $matches);
        return $matches[1];
    }

    private function _applyPaymentUpdate($paymentState, $order)
    {
        try {
            $this->_paymentUpdate = $this->paymentservice
                        ->createPaymentUpdateFromWorldPayResponse($paymentState);
            $this->_paymentUpdate->apply($order->getPayment(), $order);
        } catch (Exception $e) {
             $this->wplogger->error($e->getMessage());
        }
    }


    /**
     * @return string
     */
    private function _redirectToCreateOrderPage()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_create/index');
        return $resultRedirect; 
    }
 
}