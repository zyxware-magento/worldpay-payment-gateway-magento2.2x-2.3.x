<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment\Update;

class Factory
{
    /** @var \Zyxware\Worldpay\Helper\Data */
    private $_configHelper;
    /**
     * Constructor 
     * @param \Zyxware\Worldpay\Helper\Data $configHelper    
     * @param \Zyxware\Worldpay\Model\Payment\WorldPayPayment $worldPayPayment             
     */
    public function __construct(
        \Zyxware\Worldpay\Helper\Data $configHelper,
        \Zyxware\Worldpay\Model\Payment\WorldPayPayment $worldpaymentmodel
    ) {
            $this->_configHelper = $configHelper;
            $this->worldpaymentmodel = $worldpaymentmodel;
        
    }

    /**
     * @param \Zyxware\Worldpay\Model\Payment\State $paymentState
     * @return object
     */
    public function create(\Zyxware\Worldpay\Model\Payment\State $paymentState)
    {
        switch ($paymentState->getPaymentStatus()) {
            case \Zyxware\Worldpay\Model\Payment\State::STATUS_AUTHORISED:
                return new \Zyxware\Worldpay\Model\Payment\Update\Authorised(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_CAPTURED:
                return new \Zyxware\Worldpay\Model\Payment\Update\Captured(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_SENT_FOR_REFUND:
                return new \Zyxware\Worldpay\Model\Payment\Update\SentForRefund(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_REFUNDED:
                return new \Zyxware\Worldpay\Model\Payment\Update\Refunded(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_REFUND_FAILED:
                return new \Zyxware\Worldpay\Model\Payment\Update\RefundFailed(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );
            
            case \Zyxware\Worldpay\Model\Payment\State::STATUS_REFUND_EXPIRED:
                return new \Zyxware\Worldpay\Model\Payment\Update\RefundFailed(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_CANCELLED:
                return new \Zyxware\Worldpay\Model\Payment\Update\Cancelled(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_REFUSED:
                return new \Zyxware\Worldpay\Model\Payment\Update\Refused(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_ERROR:
                return new \Zyxware\Worldpay\Model\Payment\Update\Error(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            case \Zyxware\Worldpay\Model\Payment\State::STATUS_PENDING_PAYMENT:
                return new \Zyxware\Worldpay\Model\Payment\Update\PendingPayment(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );

            default :
                return new \Zyxware\Worldpay\Model\Payment\Update\Defaultupdate(
                    $paymentState,
                    $this->worldpaymentmodel,
                    $this->_configHelper
                );
        }
    }

}
