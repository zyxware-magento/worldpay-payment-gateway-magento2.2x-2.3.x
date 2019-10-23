<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Redirectresult;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
 
 /**
  * remove authorized order from card and Redirect to success page
  */
class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * Constructor
     *
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice 
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger     
     */
    public function __construct(Context $context, PageFactory $pageFactory, 
        \Zyxware\Worldpay\Model\Order\Service $orderservice,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
    ) { 
        $this->pageFactory = $pageFactory;
        $this->orderservice = $orderservice;
        $this->wplogger = $wplogger;
        return parent::__construct($context);
    }
 
    public function execute()
    {
        $this->wplogger->info('worldpay returned success url');
        $this->orderservice->redirectOrderSuccess();
        $this->orderservice->removeAuthorisedOrder();
        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success', ['_current' => true]);
    }    
}