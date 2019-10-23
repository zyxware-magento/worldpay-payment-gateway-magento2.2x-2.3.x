<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Block;

class Savedcard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Zyxware\Worldpay\Model\SavedTokenFactory
     */
    protected $_savecard;
    /**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;
    /**
     * constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Zyxware\Worldpay\Model\SavedTokenFactory $savecard
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
  	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Zyxware\Worldpay\Model\SavedTokenFactory $savecard,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_savecard = $savecard;
        $this->_customerSession = $customerSession;
       	parent::__construct($context, $data);

    }

    /**
     * @return bool|\Zyxware\Worldpay\Model\ResourceModel\SavedToken\Collection
     */
    public function getSavedCard()
    {

  		if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
   		return $savedCardCollection = $this->_savecard->create()->getCollection()
   	 							      ->addFieldToSelect(array('card_brand','card_number','cardholder_name','card_expiry_month','card_expiry_year'))
   	 						  	      ->addFieldToFilter('customer_id', array('eq' => $customerId)); ;
   }

   /**
     * @param \Zyxware\Worldpay\Model\SavedToken $saveCard
     * @return string
     */
    public function getDeleteUrl($saveCard)
    {
        return $this->getUrl('worldpay/savedcard/delete', ['id' => $saveCard->getId()]);
    }

    /**
     * @param \Zyxware\Worldpay\Model\SavedToken $saveCard
     * @return string
     */
    public function getEditUrl($saveCard)
    {
        return $this->getUrl('worldpay/savedcard/edit', ['id' => $saveCard->getId()]);
    }
}
