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
 * Redirect to the admin view order page if order is failed
 */ 
class Success extends \Magento\Backend\App\Action
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger    
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Order\Service $orderservice
    ) { 
       
        parent::__construct($context);
        $this->wplogger = $wplogger;
        $this->orderservice = $orderservice;
        $this->resultJsonFactory = $resultJsonFactory;
    }
    
    /**
     * Execute if payment is success redirect to admin view order page
     */
    public function execute()
    {
         $this->wplogger->info('worldpay returned admin success url');
        $worldPayOrder = $this->_getWorldPayOrder();
        return $this->_redirectToOrderViewPage($worldPayOrder);
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
    private function _getOrderIncrementId()
    {
        $params = $this->getRequest()->getParams();
        preg_match('/\^(\d+)-/', $params['orderKey'], $matches);

        return $matches[1];
    }

    /**
     * @return string
     */
    private function _redirectToOrderViewPage($worldPayOrder)
    {
        $order = $worldPayOrder->getOrder();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
        return $resultRedirect; 
    }
 
}