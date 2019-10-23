<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Controller\Savedcard;

use Magento\Framework\App\Action\Context;
use \Zyxware\Worldpay\Model\SavedTokenFactory;
use \Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Exception;

/**
 *Controller for Updating Saved card
 */
class EditPost extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * Constructor
     *
     * @param Context $context
     * @param SavedTokenFactory $savecard
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     * @param StoreManagerInterface $storeManager
     * @param \Zyxware\Worldpay\Model\Token\Service $tokenService
     * @param \Zyxware\Worldpay\Model\Token\WorldpayToken $worldpayToken
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     */
    public function __construct(
        Context $context,
        SavedTokenFactory $savecard,
        Session $customerSession,
        Validator $formKeyValidator,
        StoreManagerInterface $storeManager,
        \Zyxware\Worldpay\Model\Token\Service $tokenService,
        \Zyxware\Worldpay\Model\Token\WorldpayToken $worldpayToken,
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        PaymentTokenRepositoryInterface $tokenRepository,
        PaymentTokenManagement $paymentTokenManagement
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->savecard = $savecard;
        $this->customerSession = $customerSession;
        $this->_tokenService = $tokenService;
        $this->_worldpayToken = $worldpayToken;
        $this->wplogger = $wplogger;
        $this->tokenRepository = $tokenRepository;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Retrive store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Receive http post request to update saved card details
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        if ($validFormKey && $this->getRequest()->isPost()) {
            try {
                $tokenUpdateResponse = $this->_tokenService->getTokenUpdate(
                    $this->_getTokenModel(),
                    $this->customerSession->getCustomer(),
                    $this->getStoreId());
            } catch (Exception $e) {
                $this->wplogger->error($e->getMessage());
                $this->messageManager->addException($e, __('Error: ').$e->getMessage());
                $this->_redirect('*/savedcard/edit', array('id' => $this->_getTokenModel()->getId()));
                return;
            }
            if ($tokenUpdateResponse->isSuccess()) {
                $this->_applyTokenUpdate();
                $this->_applyVaultTokenUpdate();
            } else {
                $this->messageManager->addError(__('Error: the card has not been updated.'));
                $this->_redirect('*/savedcard/edit', array('id' => $this->_getTokenModel()->getId()));
                return;
            }
            $this->messageManager->addSuccess(__('The card has been updated.'));
            $this->_redirect('*/savedcard');
            return;
        }
    }

    /**
     * Update Saved Card Detail
     */
    protected function _applyTokenUpdate()
    {
        $this->_worldpayToken->updateTokenByCustomer(
            $this->_getTokenModel(),
            $this->customerSession->getCustomer()
        );
    }

    /**
     * @return Zyxware/WorldPay/Model/Token
     */
    protected function _getTokenModel()
    {
        if (! $tokenId = $this->getRequest()->getParam('token_id')) {
            $tokenData = $this->getRequest()->getParam('token');
            $tokenId = $tokenData['id'];
        }
        $token = $this->savecard->create()->loadByTokenCode($tokenId);
        $tokenUpdateData = $this->getRequest()->getParam('token');
        if (! empty($tokenUpdateData)) {
            $token->setCardholderName(trim($tokenUpdateData['cardholder_name']));
            $token->setCardExpiryMonth(sprintf('%02d', $tokenUpdateData['card_expiry_month']));
            $token->setCardExpiryYear(sprintf('%d', $tokenUpdateData['card_expiry_year']));
        }
        return $token;
    }

    protected function _applyVaultTokenUpdate(){
        $existingVaultPaymentToken = $this->paymentTokenManagement->getByGatewayToken(
            $this->_getTokenModel()->getTokenCode(),
            'worldpay_cc',
            $this->customerSession->getCustomer()->getId());
        $this->_saveVaultToken($existingVaultPaymentToken);

    }

    protected function _saveVaultToken(PaymentTokenInterface $vaultToken)
    {
        $vaultToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $this->_getTokenModel()->getMethod(),
            'maskedCC' => $this->getLastFourNumbers($this->_getTokenModel()->getCardNumber()),
            'expirationDate'=> $this->getExpirationMonthAndYear($this->_getTokenModel())
        ]));
        try {
            $this->tokenRepository->save($vaultToken);
        } catch (Exception $e) {
            $this->wplogger->error($e->getMessage());
            $this->messageManager->addException($e, __('Error: ').$e->getMessage());
        }
        return;
    }

    public function getExpirationMonthAndYear($token)
    {
        return $token->getCardExpiryMonth().'/'.$token->getCardExpiryYear();
    }

    public function getLastFourNumbers($number)
    {
        return substr ($number, -4);
    }

    private function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }
}
