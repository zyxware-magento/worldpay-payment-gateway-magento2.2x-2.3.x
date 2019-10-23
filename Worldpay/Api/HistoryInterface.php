<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Api;
 
interface HistoryInterface
{
    /**
     * Retrive order Notification
     *
     * @api
     * @param integer $order OrderId.
     * @return string
     */
    public function getHistory($order);
}