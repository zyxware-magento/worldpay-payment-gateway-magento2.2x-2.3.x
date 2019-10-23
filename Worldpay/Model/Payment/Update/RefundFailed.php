<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment\Update;

class RefundFailed
    extends \Zyxware\Worldpay\Model\Payment\Update\Base
    implements \Zyxware\Worldpay\Model\Payment\Update
{
    /** @var \Zyxware\Worldpay\Helper\Data */
    private $_configHelper;
    const REFUND_FAILED_COMMENT  = 'The attempted refund request FAILED.';
    const REFUND_EXPIRED_COMMENT = 'The attempted refund request EXPIRED.';
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
        $paymentStatus = $this->_paymentState->getPaymentStatus();
        $this->_reference = $this->_paymentState->getJournalReference(
            $this->_paymentState->getPaymentStatus()
        );

         if ($paymentStatus == \Zyxware\Worldpay\Model\Payment\State::STATUS_REFUND_EXPIRED) {
            $this->_message = self::REFUND_EXPIRED_COMMENT;
        } else {
            $this->_message = self::REFUND_FAILED_COMMENT;
        }
        $this->_message .= ' Reference:' . $this->_reference;

        $order->cancelRefund($this->_reference, $this->_message);
        $this->_worldPayPayment->updateWorldPayPayment($this->_paymentState);

    }

}
