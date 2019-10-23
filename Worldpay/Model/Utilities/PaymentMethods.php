<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Utilities;
/**
 * Reading the xml
 */
class PaymentMethods
{
    /**
     * @var SimpleXMLElement
     */
    protected static $_xml;

    /**
     * @var string
     */
    protected $_xmlLocation;

    const PAYMENT_METHOD_PATH = '/paymentMethods/';
    const TYPE_PATH = '/types/';

    /**
     * Constructor
     *
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Magento\Checkout\Model\Session $checkoutsession
     * @param \Magento\Backend\Model\Session\Quote $adminsessionquote
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Backend\Model\Session\Quote $adminsessionquote,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $etcDir = $moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
            'Zyxware_Worldpay'
        );
        $this->_xmlLocation = $etcDir . '/paymentmethods.xml';
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->wplogger = $wplogger;
        $this->checkoutsession = $checkoutsession;
        $this->adminsessionquote = $adminsessionquote;
        $this->authSession = $authSession;
    }

    /**
     * Get Title of paymentMethod
     *
     * @param \SimpleXmlElement $methodNode
     * @return String
     */
    protected function _getMethod(\SimpleXmlElement $methodNode)
    {
        if ($methodNode) {
            $title = (array) $methodNode->title;
            return $title[0];
        }
    }

    /**
     * @param string $type
     * @return String
     */
    protected function _getConfigCode($type)
    {
        switch ($type) {
            case 'worldpay_cc':
                return 'cc_config';
            case 'worldpay_apm':
                return 'apm_config';
            default:
                return 'apm_config';
        }
    }

    /**
     *load enable payment type
     *
     * @param string $type
     * @return array $methods
     */
    public function loadEnabledByType($type, $paymentType)
    {
        $methods = array();
        if ($xml = $this->_readXML()) {
            $node = $xml->xpath(self::PAYMENT_METHOD_PATH . $type . self::TYPE_PATH . $paymentType);
            if ($this->_paymentMethodExists($node) && $this->_methodAllowedForCountry($type, $node[0])) {
                return true;
            }
            else{
                return false;
            }
        }
        return $methods;
    }

    /**
     * Check payment method exit or not
     *
     * @return Boolean
     */
    private function _paymentMethodExists($paymentMethodNode)
    {
        return $paymentMethodNode && sizeof($paymentMethodNode);
    }

    /**
     * @return SimpleXMLElement $methods
     */
    public function getAvailableMethods()
    {
        $methods = $this->_readXML();
        return $methods;
    }

    /**
     * @return SimpleXMLElement
     */
    protected function _readXML()
    {
        if (!self::$_xml && file_exists($this->_xmlLocation)) {
            self::$_xml = simplexml_load_file($this->_xmlLocation);
        }
        return self::$_xml;
    }

    /**
     * check if paymentmethod is allowed for country
     * @return bool
     */
    private function _methodAllowedForCountry($type, \SimpleXMLElement $paymentMethodNode)
    {
        if (!$this->_paymentMethodFiltersByCountry($type)) {
            return true;
        }
        return $this->_isCountryAllowed(
            $this->_getAllowedCountryIds(),
            $this->_getAvailableCountryIds($paymentMethodNode)
        );
    }

    /**
     * check if payment is placed through worldpay
     * @return bool
     */
    private function _paymentMethodFiltersByCountry($type)
    {
        return $type === 'worldpay_apm' || $type === 'worldpay_cc' || $type === 'worldpay_moto' || $type === 'worldpay_cc_vault';
    }

    /**
     * Get allowed country Id
     * @return array
     */
    private function _getAllowedCountryIds()
    {
        $quote = $this->checkoutsession->getQuote();
        if($this->authSession->isLoggedIn()) {
            $adminQuote = $this->adminsessionquote->getQuote();
            if (empty($quote->getReservedOrderId()) && !empty($adminQuote->getReservedOrderId())) {
                $quote = $adminQuote;
            }
        }
        $address = $quote->getBillingAddress();
        $countryid = $address->getCountryId();

        return array($countryid, 'GLOBAL');
    }

    /**
     * @param \SimpleXMLElement $paymentMethodNode
     * @return array
     */
    private function _getAvailableCountryIds(\SimpleXMLElement $paymentMethodNode)
    {
        $areas = (array) $paymentMethodNode->areas;

        return is_array($areas['area']) ? $areas['area'] : array($areas['area']);
    }

    private function _isCountryAllowed($allowedCountryIds, $availableCountryIds)
    {
        $matchingCountries = array_intersect($allowedCountryIds, $availableCountryIds);

        return !empty($matchingCountries);
    }

    /**
     * check capture request is enabled or not
     * @param string $type
     * @return bool
     */
    public function CheckCaptureRequest($type,$method)
    {

        if ($xml = $this->_readXML()) {
            $node = $xml->xpath(self::PAYMENT_METHOD_PATH . $type . self::TYPE_PATH . $method );
            if ($node) {
                $capture_request = $this->_getCaptureRequest($node[0]);
                if ($capture_request==1) {
                   return true;
                }
            }
        }
        return false;
    }

    /**
     * @param \SimpleXMLElement $paymentMethodNode
     * @return string|bool
     */
    private function _getCaptureRequest(\SimpleXMLElement $paymentMethodNode)
    {
        $capturerequest = ($paymentMethodNode->capture_request) ? (string) $paymentMethodNode->capture_request : false;

        return $capturerequest;
    }

    public function CheckCurrency($code, $type)
    {
        if ($xml = $this->_readXML()) {
             $node = $xml->xpath(self::PAYMENT_METHOD_PATH . $code . self::TYPE_PATH . $type .'/currencies');
             if(!$this->_currencyNodeExists($node) || $this->_typeAllowedForCurrency($node[0])){
                return true;
             }else{
                return false;
             }

        }
        return true;
    }

     private function _currencyNodeExists($node)
    {
        return $node && sizeof($node);
    }

    private function _typeAllowedForCurrency(\SimpleXMLElement $node)
    {
        return $this->_isCurrencyAllowed(
            $this->_getAllowedCurrencies(),
            $this->_getAvailableCurrencyCodes($node)
        );
    }


    private function _getAllowedCurrencies()
    {
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return array($currencyCode);
    }

    private function _getAvailableCurrencyCodes(\SimpleXMLElement $node)
    {
        $currencies = (array) $node;

        return is_array($currencies['currency']) ? $currencies['currency'] : array($currencies['currency']);
    }

    private function _isCurrencyAllowed($allowedCurrencyCodes, $availableCurrencyCodes)
    {
        $matchingCurrencies = array_intersect($allowedCurrencyCodes, $availableCurrencyCodes);

        return !empty($matchingCurrencies);
    }


    public function CheckShipping($code, $type)
    {
        if ($xml = $this->_readXML()) {
             $node = $xml->xpath(self::PAYMENT_METHOD_PATH . $code . self::TYPE_PATH . $type .'/shippingareas');
             if(!$this->_shippingNodeExists($node) || $this->_typeAllowedForShipping($node[0])){
                return true;
             }else{
                return false;
             }

        }
        return true;
    }

     private function _shippingNodeExists($node)
    {
        return $node && sizeof($node);
    }

    private function _typeAllowedForShipping(\SimpleXMLElement $node)
    {
        return $this->_isShippingAllowed(
            $this->_getAllowedShippingCountries(),
            $this->_getAvailableShippingCountries($node)
        );
    }


    private function _getAllowedShippingCountries()
    {
        $quote = $this->checkoutsession->getQuote();
        $address = $quote->getShippingAddress();
        $countryid = $address->getCountryId();

        return array($countryid,'GLOBAL');
    }

    private function _getAvailableShippingCountries(\SimpleXMLElement $node)
    {
        $areas = (array) $node;

        return is_array($areas['ship']) ? $areas['ship'] : array($areas['ship']);
    }

    private function _isShippingAllowed($allowedShippingCountries, $availableShippingCountries)
    {
        $matchingCountries = array_intersect($allowedShippingCountries, $availableShippingCountries);

        return !empty($matchingCountries);
    }


     public function CheckStopAutoInvoice($code, $type)
    {
        if ($xml = $this->_readXML()) {
             $node = $xml->xpath(self::PAYMENT_METHOD_PATH . $code . self::TYPE_PATH . $type .'/stop_auto_invoice');
             if($this->_autoInvoiceNodeExists($node) && $this->_getStopAutoInvoice($node[0]) == 1){
                return true;
             }else{
                return false;
             }

        }
        return false;
    }

     private function _autoInvoiceNodeExists($node)
    {
        return $node && sizeof($node);
    }

    private function _getStopAutoInvoice(\SimpleXMLElement $node)
    {
        $stopautoinvoice = (string) $node;
        return $stopautoinvoice;
    }
    public function idealBanks(){
        $banks = array();
        if ($xml = $this->_readXML()) {
            $node = $xml->xpath('/paymentMethods/' . 'worldpay_apm' . '/types/' . 'IDEAL-SSL'. '/banks');
            if ($this->_paymentMethodExists($node)) {
                 $bankinfos = $node[0];
                $bankdetails = array();
                foreach($bankinfos->bank as $bankinfo){
                    $bankcode = (string) $bankinfo->code;
                    $bankvalue = (string) $bankinfo->value;
                    $bankdetails[$bankcode] = $bankvalue;
                }
                return $bankdetails;
            }
        }
    }

    public function getPaymentTypeCountries()
    {
        $codes = array('worldpay_cc','worldpay_apm','worldpay_moto', 'worldpay_cc_vault');
        $paymenttypecountries = array();
        foreach($codes as $code){
            if ($xml = $this->_readXML()) {
                 $nodes = $xml->xpath('/paymentMethods/' . $code . '/types');
             }
             $typearray =  array();
             foreach($nodes[0] as $key => $value){
                $key = (string) $key;
                $area =  (array) $value->areas[0]->area;
                $typearray[$key] = $area;
             }
             $paymenttypecountries[$code] = $typearray;
        }
        return json_encode($paymenttypecountries);
    }

}
