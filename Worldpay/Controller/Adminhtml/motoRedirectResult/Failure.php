<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Adminhtml\motoRedirectResult;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Exception;

/**
 * Redirect to the admin create order page  if order is failed
 */ 
class Failure extends \Magento\Backend\App\Action
{
  
    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Model\Adminhtml\Order\Service $adminorderservice
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice
     */
    public function __construct(Context $context,  JsonFactory $resultJsonFactory,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Adminhtml\Order\Service $adminorderservice,
        \Zyxware\Worldpay\Model\Order\Service $orderservice
    ) { 
       
        parent::__construct($context);
        $this->wplogger = $wplogger;
        $this->orderservice = $orderservice;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->adminorderservice = $adminorderservice;

    }
    
    /**
     * Execute if payment is failed redirect to admin create order page
     */
    public function execute()
    {
        $this->wplogger->info('worldpay returned admin failure url');
        $worldPayOrder = $this->_getWorldPayOrder();
        $notice = $this->_getFailureNoticeForOrder($worldPayOrder->getOrder());
        $this->messageManager->getMessages(true);
        $this->messageManager->addNotice($notice);
        $this->adminorderservice->reactivateAdminQuoteForOrder($worldPayOrder);
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
    private function _getFailureNoticeForOrder($order)
    {
        return __('Order #'.$order->getIncrementId().' failed');
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