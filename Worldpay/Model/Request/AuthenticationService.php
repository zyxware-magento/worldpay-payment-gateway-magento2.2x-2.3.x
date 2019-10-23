<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Request;

use Exception;

class AuthenticationService  extends \Magento\Framework\DataObject {

    /**
     * Constructor
     *
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Helper\Data $worldpayhelper
     */
    public function __construct(
    	\Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Helper\Data $worldpayhelper
    ) {
	    $this->_wplogger = $wplogger;
        $this->worldpayhelper = $worldpayhelper;
    }

    /**
     * @return bool
     */
    public function requestAuthenticated($params, $type = 'ecom')
    {
       return true;
    }

}
