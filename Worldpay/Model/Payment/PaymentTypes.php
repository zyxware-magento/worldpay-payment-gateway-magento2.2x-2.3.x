<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment;

use Zyxware\Worldpay\Api\PaymentTypeInterface; 

class PaymentTypes implements PaymentTypeInterface
{

	 public function __construct(
        \Zyxware\Worldpay\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Zyxware\Worldpay\Model\Authorisation\PaymentOptionsService $paymentoptionsservice
    ) {
        $this->paymentoptionsservice = $paymentoptionsservice; 
        $this->worldpayHelper = $helper;      
        $this->storeManager = $storeManager;
    }
   
    public function getPaymentType($countryId) 
    {
        $responsearray = array();
        $result = $this->paymentoptionsservice->collectPaymentOptions($countryId,$paymenttype = null);
        if(!empty($result)){
        	$responsearray = $result;
        }
        return json_encode($responsearray);
    }
    

    public function getCCTypes() 
    {
        $ccTypes = [];
        $types = $this->worldpayHelper->getCcTypes();
        $allTypePayments = $types;
        foreach (array_keys($allTypePayments) as $code) {
            $mediaUrl = $this->getMediaPath().'/paymentmethod/cc/' . strtolower($code) . '.png';
            $ccTypes[] = [
                    'code' => $code,
                    'url' => $mediaUrl
                ];
        }
        return $ccTypes;   
    }

    private function getMediaPath()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}