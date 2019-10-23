<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\PaymentMethods;
/**
 * WorldPay CreditCards class extended from WorldPay Abstract Payment Method.
 */
class AlternativePaymentMethods extends \Zyxware\Worldpay\Model\PaymentMethods\AbstractMethod
{
    /**
     * Payment code
     * @var string
     */
    protected $_code = 'worldpay_apm';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_wplogger->info('WorldPay Alternative Payment Method Executed:');
        parent::authorize($payment, $amount);
        return $this;
    }

    public function getAuthorisationService($storeId)
    {
        return $this->redirectservice;
    }

    /**
     * check if apm is enabled
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
       if ($this->worlpayhelper->isWorldPayEnable() && $this->worlpayhelper->isApmEnabled()) {
         return true;
       }
       return false;
    }

    public function getTitle()
    {
        if($order = $this->registry->registry('current_order')) {
            return $this->worlpayhelper->getPaymentTitleForOrders($order, $this->_code, $this->worldpaypayment);
        }else if($invoice = $this->registry->registry('current_invoice')){
            $order = $this->worlpayhelper->getOrderByOrderId($invoice->getOrderId());
            return $this->worlpayhelper->getPaymentTitleForOrders($order, $this->_code, $this->worldpaypayment);
        }else if($creditMemo = $this->registry->registry('current_creditmemo')){
            $order = $this->worlpayhelper->getOrderByOrderId($creditMemo->getOrderId());
            return $this->worlpayhelper->getPaymentTitleForOrders($order, $this->_code, $this->worldpaypayment);
        }else{
            return $this->worlpayhelper->getApmTitle();
        }
    }
}
