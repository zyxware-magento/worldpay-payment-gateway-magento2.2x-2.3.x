<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment\Update;

class Refunded
    extends \Zyxware\Worldpay\Model\Payment\Update\Base
    implements \Zyxware\Worldpay\Model\Payment\Update
{
    /** @var \Zyxware\Worldpay\Helper\Data */
    private $_configHelper;
    const REFUND_COMMENT = 'Refund request PROCESSED by the bank.';
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
        $reference = $this->_paymentState->getJournalReference(
            \Zyxware\Worldpay\Model\Payment\State::STATUS_REFUNDED
        );
        $message = self::REFUND_COMMENT . ' Reference: ' . $reference;
        $order->refund($reference, $message);
        $this->_worldPayPayment->updateWorldPayPayment($this->_paymentState);
    }


}
