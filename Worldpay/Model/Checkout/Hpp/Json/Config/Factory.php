<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Checkout\Hpp\Json\Config;

use Zyxware\Worldpay\Model\Checkout\Hpp\Json\Config as Config;
use Zyxware\Worldpay\Model\Checkout\Hpp\Json\Url\Config as UrlConfig;
use \Zyxware\Worldpay\Model\Checkout\Hpp\State as HppState;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;
use Exception;

class Factory
{
   /**  @var \Magento\Store\Model\Store*/
    private $store; 

    /**
     * @param StoreManagerInterface $storeManager,
     * @param \Zyxware\Worldpay\Model\Checkout\Hpp\State $hppstate,
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     * @param Repository $assetrepo,
     * @param RequestInterface $request,
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
     * @param \Zyxware\Worldpay\Helper\Data $worldpayhelper
     */
    public function __construct(
         StoreManagerInterface $storeManager,
        \Zyxware\Worldpay\Model\Checkout\Hpp\State $hppstate,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Repository $assetrepo,
        RequestInterface $request,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Helper\Data $worldpayhelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\Order $mageOrder,
        $services = array()
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetrepo;
        $this->request = $request;
        $this->wplogger = $wplogger;
        $this->worldpayhelper = $worldpayhelper;
        $this->quoteRepository = $quoteRepository;
        $this->mageorder = $mageOrder;
        if (isset($services['store']) && $services['store'] instanceof StoreManagerInterface) {
            $this->store = $services['store'];
        } else {
            $this->store = $storeManager->getStore();
        }
        if (isset($services['state']) && $services['state'] instanceof \Zyxware\Worldpay\Model\Checkout\Hpp\State) {
            $this->state = $services['state'];
        } else {
            $this->state = $hppstate;
        } 
    }

    /**
     * @return Zyxware\Worldpay\Model\Checkout\Hpp\Json\Config
     */
    public function create($javascriptObjectVariable, $containerId)
    {
        $parts = parse_url($this->state->getRedirectUrl());
        parse_str($parts['query'], $orderparams);
        $orderkey = $orderparams['OrderKey'];
        $magentoincrementid = $this->_extractOrderId($orderkey);
        $mageOrder = $this->mageorder->loadByIncrementId($magentoincrementid);
        $quote = $this->quoteRepository->get($mageOrder->getQuoteId());
        
        $country = $this->_getCountryForQuote($quote);
        $language = $this->_getLanguageForLocale();

        $params = array('_secure' => $this->request->isSecure());
        $helperhtml = $this->assetRepo->getUrlWithParams('Zyxware_Worldpay::helper.html', $params);
        $iframeurl = 'worldpay/redirectresult/iframe';
        $urlConfig = new UrlConfig(
            $this->store->getUrl($iframeurl, array('status' => 'success')),
            $this->store->getUrl($iframeurl, array('status' => 'cancel')),
            $this->store->getUrl($iframeurl, array('status' => 'pending')),
            $this->store->getUrl($iframeurl, array('status' => 'error')),
            $this->store->getUrl($iframeurl, array('status' => 'failure'))
        );
      
        return new Config(
            $this->worldpayhelper->getRedirectIntegrationMode( $this->store->getId()),
            $javascriptObjectVariable,
            $helperhtml,
            $this->store->getBaseUrl(),
            $this->state->getRedirectUrl(),
            $containerId,
            $urlConfig,
            strtolower($language),
            strtolower($country)
        );
    }

    private function _getCountryForQuote($quote)
    {
        $address = $quote->getBillingAddress();
        if ($address->getId()) {
            return $address->getCountry();
        }

        return $this->worldpayhelper->getDefaultCountry();
    }

    private function _getLanguageForLocale()
    {
        $locale = $this->worldpayhelper->getLocaleDefault();
        if (substr($locale, 3, 2) == 'NO') {
            return 'no';
        }
        return substr($locale, 0, 2); 
    
    }

     private static function _extractOrderId($orderKey)
    {
        $array = explode('^', $orderKey);
        $ordercode = end($array);
        $ordercodearray = explode('-', $ordercode);
        return reset($ordercodearray);
    }

 

}
