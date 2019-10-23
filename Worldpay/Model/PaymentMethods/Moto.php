<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\PaymentMethods;
/**
 * WorldPay CreditCards class extended from WorldPay Abstract Payment Method.
 */
class Moto extends \Zyxware\Worldpay\Model\PaymentMethods\CreditCards
{
    protected $_code = 'worldpay_moto';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = false;

    protected $_formBlockType = 'Zyxware\Worldpay\Block\Form\Card';

    /**
     * @return string
     */
    public function getPaymentMethodsType()
    {
        return 'worldpay_cc';
    }

    /**
     * @return string
     */
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
            return $this->worlpayhelper->getMotoTitle();
        }
    }

    public function getAuthorisationService($storeId)
    {
        $checkoutpaymentdata = $this->paymentdetailsdata;
        if (($checkoutpaymentdata['additional_data']['cc_type'] == 'cc_type') && empty($checkoutpaymentdata['additional_data']['tokenCode'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                        __('Saved cards not found')
                );
        }
        if (!empty($checkoutpaymentdata['additional_data']['tokenCode'])) {
            return $this->tokenservice;
        }
        if ($this->_isRedirectIntegrationModeEnabled($storeId)) {
            return $this->motoredirectservice;
        }
        return $this->directservice;
    }
    /**
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null){

       if ($this->worlpayhelper->isWorldPayEnable() && $this->worlpayhelper->isMotoEnabled()) {
         return true;
       }
       return false;

    }

    /**
     * @return bool
     */
    private function _isRedirectIntegrationModeEnabled($storeId)
    {
        $integrationModel = $this->worlpayhelper->getCcIntegrationMode($storeId);

        return $integrationModel === 'redirect';
    }

}
