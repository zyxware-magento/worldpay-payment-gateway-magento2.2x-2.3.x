<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Exception;

class Cart implements ObserverInterface 
{
	/**
     * Constructor
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice
     * @param \Zyxware\Worldpay\Model\Checkout\Service $checkoutservice
     * @param \Magento\Checkout\Model\Session $checkoutsession        
     */
	public function __construct (
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Order\Service $orderservice,
        \Zyxware\Worldpay\Model\Checkout\Service $checkoutservice,
        \Magento\Checkout\Model\Session $checkoutsession
    ) {
        $this->orderservice = $orderservice;
        $this->wplogger = $wplogger;
        $this->checkoutservice = $checkoutservice;
        $this->checkoutsession = $checkoutsession;
    }

   /**
     * Load the shopping cart from the latest authorized, but not completed order
     */
	public function execute(\Magento\Framework\Event\Observer $observer) 
    {
        if ($this->checkoutsession->getauthenticatedOrderId()) {
            $order = $this->orderservice->getAuthorisedOrder();
            $this->checkoutservice->reactivateQuoteForOrder($order);
            $this->orderservice->removeAuthorisedOrder();
        }
	}
}