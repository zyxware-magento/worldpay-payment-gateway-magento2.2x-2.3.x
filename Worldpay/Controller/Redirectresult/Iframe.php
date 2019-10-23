<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Redirectresult;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

/**
 * Display page in iframe
 */
class Iframe extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Zyxware\Worldpay\Model\Checkout\Hpp\State
     */
    protected $_status;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param \Zyxware\Worldpay\Model\Checkout\Hpp\State $hppstate
     * @param \Magento\Framework\UrlInterface $urlInterface     
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Zyxware\Worldpay\Model\Checkout\Hpp\State $hppstate,        
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
    ) { 
        $this->pageFactory = $pageFactory;
        $this->wplogger = $wplogger;
        $this->hppstate = $hppstate;       
        return parent::__construct($context);
    }
 
    public function execute()
    {
        $this->_getStatus()->reset();

        $params = $this->getRequest()->getParams();

        $redirecturl = $this->_url->getBaseUrl();

        if (isset($params['status'])) {
            $currenturl = $this->_url->getCurrentUrl();
            $redirecturl = str_replace("iframe/status/", "", $currenturl);
        }

        print_r('<script>window.top.location.href = "'.$redirecturl.'";</script>');
    }

    protected function _getStatus()
    {
        if (is_null($this->_status)) {
            $this->_status = $this->hppstate;
        }

        return $this->_status;
    }

    
}