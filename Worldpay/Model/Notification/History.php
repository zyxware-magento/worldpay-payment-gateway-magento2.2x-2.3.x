<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Notification;

use Zyxware\Worldpay\Api\HistoryInterface; 

class History implements HistoryInterface
{

    /**
     * Constructor
     * @param \Zyxware\Worldpay\Model\HistoryNotification $historyNotification          
     */
    public function __construct(
       \Zyxware\Worldpay\Model\HistoryNotification $historyNotification
    ) {
        $this->historyNotification = $historyNotification;        
    }
    /**
     * Returns Order Notification     
     * @api
     * @param Integer $order
     * @return json $result.
     */
    public function getHistory($order) 
    {
        $result="";
        if (isset($order)) {
                $result = $this->historyNotification->getCollection()
                        ->addFieldToFilter('order_id', array('eq' => trim($order)))->getData();
        } else {
                $result = 'Order Id is null';
        }
        return $result;
    }
    
}