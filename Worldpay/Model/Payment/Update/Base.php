<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment\Update;

use Exception;

class Base
{
    /** @var \Zyxware\Worldpay\Model\Payment\State */
    protected $_paymentState;
    /** @var \Zyxware\Worldpay\Model\Payment\WorldPayPayment */
    protected $_worldPayPayment;

    const STATUS_PROCESSING = 'processing';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CLOSED = 'closed';

    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\Payment\State $paymentState
     * @param \Zyxware\Worldpay\Model\Payment\WorldPayPayment $worldPayPayment        
     */
    public function __construct(
        \Zyxware\Worldpay\Model\Payment\State $paymentState,
        \Zyxware\Worldpay\Model\Payment\WorldPayPayment $worldPayPayment
    ) {

        $this->_paymentState = $paymentState;
        $this->_worldPayPayment = $worldPayPayment;
    }
    /**
     * @return string ordercode 
     */
    public function getTargetOrderCode()
    {
        return $this->_paymentState->getOrderCode();
    }

    /** 
     * check payment Status
     * @param object $order
     * @param array $allowedPaymentStatuses
     * @return null
     * @throws Exception 
     */
    protected function _assertValidPaymentStatusTransition($order, $allowedPaymentStatuses)
    {
        $this->_assertPaymentExists($order);

        $existingPaymentStatus = $order->getPaymentStatus();
        $newPaymentStatus = $this->_paymentState->getPaymentStatus();

        if (in_array($existingPaymentStatus, $allowedPaymentStatuses)) {
            return;
        }

        if ($existingPaymentStatus == $newPaymentStatus) {
            throw new Exception('same state');
        }

        throw new Exception('invalid state transition');

    }

    /** 
     * check if order is not placed throgh worldpay payment    
     * @throws Exception 
     */
    private function _assertPaymentExists($order)
    {
        if (!$order->hasWorldPayPayment()) {
            throw new Exception('No payment');
        }
    }

    /*
    * convert worldpay amount to magento amount
    */
    protected function _amountAsInt($amount)
    {
        return round($amount, 2, PHP_ROUND_HALF_EVEN) / pow(10, 2);
    }


}
