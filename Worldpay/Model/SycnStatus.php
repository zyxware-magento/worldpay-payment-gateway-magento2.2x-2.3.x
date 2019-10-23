<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model;

use Exception;

class SycnStatus
{
    private $_paymentUpdate;
    private $_tokenState;
    private $_orderCollectionFactory;

    /**
     * Constructor
     *
     * @param \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger
     * @param \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
     * @param \Zyxware\Worldpay\Model\Token\WorldpayToken $worldpaytoken,
     * @param \Zyxware\Worldpay\Model\Order\Service $orderservice
     */
    public function __construct(
        \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger,
        \Zyxware\Worldpay\Model\Payment\Service $paymentservice,
        \Zyxware\Worldpay\Model\Token\WorldpayToken $worldpaytoken,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Zyxware\Worldpay\Model\Order\Service $orderservice
    ) {
        $this->wplogger = $wplogger;
        $this->paymentservice = $paymentservice;
        $this->orderservice = $orderservice;
        $this->worldpaytoken = $worldpaytoken;
        $this->_orderCollectionFactory = $orderCollectionFactory;

    }

    public function sync()
    {
        try {
            $orders = $this->getOrdersToBeSync();
            foreach ($orders as $order) {
                $orderId = $this->_loadOrder($order->getId());
                $this->_fetchPaymentUpdate($orderId);
            }

        } catch (Exception $e) {
            $this->wplogger->error($e->getMessage());
            if ($e->getMessage() == 'same state') {
                $this->wplogger->error('Payment synchronized successfully!!');
            } else {
                $this->wplogger->error('Synchronising Payment Status failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * @return CollectionFactoryInterface
     */
    private function getOrderCollectionFactory()
    {
        if ($this->_orderCollectionFactory === null) {

            $this->_orderCollectionFactory = ObjectManager::getInstance()->create(CollectionFactoryInterface::class);
        }
        return $this->_orderCollectionFactory;
    }

    private function _loadOrder($orderId)
    {
        return $this->orderservice->getById($orderId);
    }

     /**
     * Get the list of orders to be sync
     *
     * @return array List of order IDs
     */
    public function getOrdersToBeSync()
    {
        $orders = $this->getOrderCollectionFactory()->create();
        $orders->distinct(true);
        $orders->join(array('wp' => 'worldpay_payment'), 'wp.order_id=main_table.increment_id', array('payment_type','payment_status'));
        $orders->getSelect()->where('payment_status IN (?) ', array(\Zyxware\Worldpay\Model\Payment\State::STATUS_SENT_FOR_AUTHORISATION, \Zyxware\Worldpay\Model\Payment\State::STATUS_AUTHORISED)) ;

        return $orders;
    }

    private function _fetchPaymentUpdate($order)
    {
        $xml = $this->paymentservice->getPaymentUpdateXmlForOrder($order);
        $paymentUpdate = $this->paymentservice->createPaymentUpdateFromWorldPayXml($xml);
        $tokenState = new \Zyxware\Worldpay\Model\Token\StateXml($xml);
        $this->_registerWorldPayModel($paymentUpdate);
        $this->_applyPaymentUpdate($paymentUpdate, $order);
        $this->_applyTokenUpdate($tokenState, $order);
    }

    private function _registerWorldPayModel($paymentUpdate)
    {
        $this->paymentservice->setGlobalPaymentByPaymentUpdate($paymentUpdate);
    }

    private function _applyPaymentUpdate($paymentUpdate, $order)
    {
        try {
            $paymentUpdate->apply($order->getPayment(), $order);
        } catch (Exception $e) {
            $this->wplogger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private function _applyTokenUpdate($tokenState, $order)
    {
        $this->worldpaytoken->updateOrInsertToken($tokenState, $order->getPayment());
    }
}
