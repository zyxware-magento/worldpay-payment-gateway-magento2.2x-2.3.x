<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Hostedpaymentpage;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

/**
 * Redirect to payment hosted page
 */ 
class Pay extends \Magento\Framework\App\Action\Action
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
     * @param \Zyxware\Worldpay\Model\Checkout\Hpp\State
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger    
     */
    public function __construct(
        Context $context, 
        PageFactory $pageFactory,
        \Zyxware\Worldpay\Model\Checkout\Hpp\State $hppstate,
        \Zyxware\Worldpay\Helper\Data $worldpayhelper,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
    ) { 
        $this->pageFactory = $pageFactory;
        $this->wplogger = $wplogger;
        $this->hppstate = $hppstate;
        $this->worldpayhelper = $worldpayhelper;
        return parent::__construct($context);

    }
 
    public function execute()
    {

        if (!$this->_getStatus()->isInitialised() || !$this->worldpayhelper->isIframeIntegration()) { 
            return $this->resultRedirectFactory->create()->setPath('checkout/cart', ['_current' => true]);
        } 
        return $this->pageFactory->create(); 
    }

    protected function _getStatus()
    {
        if (is_null($this->_status)) {
            $this->_status = $this->hppstate;
        }

        return $this->_status;
    }

    
}