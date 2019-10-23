<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment\Update;

class SentForRefund
    extends \Zyxware\Worldpay\Model\Payment\Update\Base
    implements \Zyxware\Worldpay\Model\Payment\Update
{
    /** @var \Zyxware\Worldpay\Helper\Data */
    private $_configHelper;
    const REFUND_COMMENT = 'Refund has been requested';
    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\Payment\State $paymentState
     * @param \Zyxware\Worldpay\Model\Payment\WorldPayPayment $worldPayPayment
     * @param \Zyxware\Worldpay\Helper\Data $configHelper        
     */
    public function __construct(
        \Zyxware\Worldpay\Model\Payment\State $paymentState,
        \Zyxware\Worldpay\Model\Payment\WorldPayPayment $worldPayPayment,
        \Zyxware\Worldpay\Helper\Data $configHelper
    ) {
        $this->_paymentState = $paymentState;
        $this->_worldPayPayment = $worldPayPayment;
        $this->_configHelper = $configHelper;
    }

    public function apply($payment,$order = null)
    {
        $reference = $this->_paymentState->getJournalReference($this->_paymentState->getPaymentStatus());
        if ($reference) {
            $order->refund($reference, self::REFUND_COMMENT);
        } else { 
            $amount = $this->_paymentState->getFullRefundAmount();
            $order->refundFull($amount, self::REFUND_COMMENT);
         }
        $this->_worldPayPayment->updateWorldPayPayment($this->_paymentState);
    }


}
