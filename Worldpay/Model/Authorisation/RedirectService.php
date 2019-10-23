<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Authorisation;
use Exception;

class RedirectService extends \Magento\Framework\DataObject
{

    /** @var \Zyxware\Worldpay\Model\Response\RedirectResponse */
    protected $_redirectResponseModel;

    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\Mapping\Service $mappingservice
     * @param \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Model\Payment\Service $paymentservice
     * @param \Zyxware\Worldpay\Model\Response\RedirectResponse $redirectresponse
     * @param \Zyxware\Worldpay\Helper\Registry $registryhelper
     * @param \Magento\Checkout\Model\Session $checkoutsession
     * @param \Zyxware\Worldpay\Model\Utilities\PaymentMethods $paymentlist
     * @param \Zyxware\Worldpay\Helper\Data $worldpayhelper
     */
    public function __construct(
        \Zyxware\Worldpay\Model\Mapping\Service $mappingservice,
        \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
        \Zyxware\Worldpay\Model\Response\RedirectResponse $redirectresponse,
        \Zyxware\Worldpay\Helper\Registry $registryhelper,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Zyxware\Worldpay\Model\Utilities\PaymentMethods $paymentlist,
        \Zyxware\Worldpay\Helper\Data $worldpayhelper
    ) {
       $this->mappingservice = $mappingservice;
       $this->paymentservicerequest = $paymentservicerequest;
       $this->wplogger = $wplogger;
       $this->paymentservice = $paymentservice;
       $this->redirectresponse = $redirectresponse;
       $this->registryhelper = $registryhelper;
       $this->checkoutsession = $checkoutsession;
       $this->paymentlist = $paymentlist;
       $this->worldpayhelper = $worldpayhelper;
    }
    /**
     * handles provides authorization data for redirect
     * It initiates a  XML request to WorldPay and registers worldpayRedirectUrl
     */
    public function authorizePayment(
        $mageOrder,
        $quote,
        $orderCode,
        $orderStoreId,
        $paymentDetails,
        $payment
    ) {
        $this->checkoutsession->setauthenticatedOrderId($mageOrder->getIncrementId());
        if($paymentDetails['additional_data']['cc_type'] == 'KLARNA-SSL'){
             $redirectOrderParams = $this->mappingservice->collectKlarnaOrderParameters(
                $orderCode,
                $quote,
                $orderStoreId,
                $paymentDetails
            );

            $response = $this->paymentservicerequest->redirectKlarnaOrder($redirectOrderParams);
       }else if(!empty($paymentDetails['additional_data']['cc_bank']) && $paymentDetails['additional_data']['cc_type'] == 'IDEAL-SSL'){
                $callbackurl = $this->redirectresponse->getCallBackUrl();
                $redirectOrderParams = $this->mappingservice->collectRedirectOrderParameters(
                $orderCode,
                $quote,
                $orderStoreId,
                $paymentDetails
            );
                $redirectOrderParams['cc_bank'] = $paymentDetails['additional_data']['cc_bank'];
                $redirectOrderParams['callbackurl'] = $callbackurl;

            $response = $this->paymentservicerequest->DirectIdealOrder($redirectOrderParams);
       }
       else{
            $redirectOrderParams = $this->mappingservice->collectRedirectOrderParameters(
                $orderCode,
                $quote,
                $orderStoreId,
                $paymentDetails
            );

            $response = $this->paymentservicerequest->redirectOrder($redirectOrderParams);
        }
        $successUrl = $this->_buildRedirectUrl(
            $this->_getRedirectResponseModel()->getRedirectLocation($response),
            $redirectOrderParams['paymentType'],
            $this->_getCountryForQuote($quote),
            $this->_getLanguageForLocale()
        );

        $payment->setIsTransactionPending(1);

        $this->registryhelper->setworldpayRedirectUrl($successUrl);
        $this->checkoutsession->setWpRedirecturl($successUrl);

    }

    private function _buildRedirectUrl($redirect, $paymentType, $countryCode, $languageCode)
    {
        $redirect .= '&preferredPaymentMethod=' . $paymentType;
        $redirect .= '&country=' . $countryCode;
        $redirect .= '&language=' . $languageCode;

        return $redirect;
    }

    /**
     * Get billing Country
     * @return string
     */
    private function _getCountryForQuote($quote)
    {
        $address = $quote->getBillingAddress();
        if ($address->getId()) {
            return $address->getCountry();
        }
        return $this->worldpayhelper->getDefaultCountry();

    }

    /**
     * Get local language code
     * @return string
     */
    protected function _getLanguageForLocale()
    {
        $locale = $this->worldpayhelper->getLocaleDefault();
        if (substr($locale, 3, 2) == 'NO') {
            return 'no';
        }
        return substr($locale, 0, 2);
    }

    /**
     * @return \Zyxware\Worldpay\Model\Response\RedirectResponse
     */
    protected function _getRedirectResponseModel()
    {
        if ($this->_redirectResponseModel === null) {
            $this->_redirectResponseModel = $this->redirectresponse;
        }
        return $this->_redirectResponseModel;
    }


}
