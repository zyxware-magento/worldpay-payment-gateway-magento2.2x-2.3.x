<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Authorisation;
use Exception;

class PaymentOptionsService extends \Magento\Framework\DataObject
{
   
    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\Mapping\Service $mappingservice
     * @param \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Helper\Data $worldpayhelper   
     */
    public function __construct(
        \Zyxware\Worldpay\Model\Mapping\Service $mappingservice,
        \Zyxware\Worldpay\Model\Request\PaymentServiceRequest $paymentservicerequest,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Helper\Data $worldpayhelper
    ) {
       $this->mappingservice = $mappingservice;
       $this->paymentservicerequest = $paymentservicerequest;
       $this->wplogger = $wplogger;
       $this->worldpayhelper = $worldpayhelper;
    }
    /**
     * handles provides authorization data for redirect
     * It initiates a  XML request to WorldPay and registers worldpayRedirectUrl 
     */
    public function collectPaymentOptions(
        $countryId,
        $paymenttype
    ) {      
        $paymentOptionParams = $this->mappingservice->collectPaymentOptionsParameters(
            $countryId,
            $paymenttype
        );

        $response = $this->paymentservicerequest->paymentOptionsByCountry($paymentOptionParams);
        $responsexml = simplexml_load_string($response);

        $paymentoptions =  $this->getPaymentOptions($responsexml);
        return $paymentoptions;
    }

    private function getPaymentOptions($xml)
    {
         if (isset($xml->reply->paymentOption)) {
            return (array) $xml->reply->paymentOption;
        }
        return null;

    }

    
}
