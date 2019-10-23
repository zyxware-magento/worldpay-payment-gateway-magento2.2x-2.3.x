<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Authorisation;

use Exception;

class HostedPaymentPageService extends \Magento\Framework\DataObject
{
   
    /** @var  \Zyxware\Worldpay\Model\Checkout\Hpp\State */
    protected $_status;
    /** @var  \Zyxware\Worldpay\Model\Response\RedirectResponse */
    protected $_redirectResponseModel;

    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\Mapping\Service $mappingservice
     * @param \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Model\Response\RedirectResponse $redirectresponse
     * @param \Zyxware\Worldpay\Helper\Registry $registryhelper
     * @param \Zyxware\Worldpay\Model\Checkout\Hpp\State $hppstate
     * @param \Magento\Checkout\Model\Session $checkoutsession
     * @param \Magento\Framework\UrlInterface $urlInterface     
     */
    public function __construct(
        \Zyxware\Worldpay\Model\Mapping\Service $mappingservice,
        \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Response\RedirectResponse $redirectresponse,
        \Zyxware\Worldpay\Helper\Registry $registryhelper,
        \Zyxware\Worldpay\Model\Checkout\Hpp\State $hppstate,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
       $this->mappingservice = $mappingservice;
       $this->paymentservicerequest = $paymentservicerequest;
       $this->wplogger = $wplogger;
       $this->redirectresponse = $redirectresponse;
       $this->registryhelper = $registryhelper;
       $this->checkoutsession = $checkoutsession;
       $this->hppstate = $hppstate;
       $this->_urlInterface = $urlInterface;
    }
    /**
     * handles provides authorization data for Hosted Payment Page integration
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

        $redirectOrderParams = $this->mappingservice->collectRedirectOrderParameters(
            $orderCode,
            $quote,
            $orderStoreId,
            $paymentDetails
        );

        $response = $this->paymentservicerequest->redirectOrder($redirectOrderParams);
       
        $this->_getStatus()
            ->reset()
            ->init($this->_getRedirectResponseModel()->getRedirectUrl($response));

        $payment->setIsTransactionPending(1);
        $this->registryhelper->setworldpayRedirectUrl($this->_urlInterface->getUrl('worldpay/hostedpaymentpage/pay'));

        $this->checkoutsession->setWpRedirecturl($this->_urlInterface->getUrl('worldpay/hostedpaymentpage/pay'));

    }

    /**
     * @return  \Zyxware\Worldpay\Model\Response\RedirectResponse
     */
    protected function _getRedirectResponseModel()
    {
        if ($this->_redirectResponseModel === null) {
            $this->_redirectResponseModel = $this->redirectresponse;
        }

        return $this->_redirectResponseModel;
    }

    /**
     * @return  \Zyxware\Worldpay\Model\Checkout\Hpp\State 
     */
    protected function _getStatus()
    {
        if (is_null($this->_status)) {
            $this->_status = $this->hppstate;
        }

        return $this->_status;
    }


}
