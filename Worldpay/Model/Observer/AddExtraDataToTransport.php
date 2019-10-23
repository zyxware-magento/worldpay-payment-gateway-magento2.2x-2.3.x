<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddExtraDataToTransport implements ObserverInterface
{  
	protected $worldpaypayment;

	public function __construct(
		\Zyxware\Worldpay\Model\WorldpaymentFactory $worldpaypayment
	) {
        $this->worldpaypayment = $worldpaypayment;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getEvent()->getTransport();
		// Order info
		$order = $transport['order'];
		$paymentCode = $order->getPayment()->getMethod();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->get('Zyxware\Worldpay\Helper\Data');
		// Full payment method name
		$paymentMethod = $helper->getPaymentTitleForOrders($order, $paymentCode, $this->worldpaypayment);
		if($paymentMethod){
			$transport['payment_html'] = $paymentMethod;
		}        
    }
}