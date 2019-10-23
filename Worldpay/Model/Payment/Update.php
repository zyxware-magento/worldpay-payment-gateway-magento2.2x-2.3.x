<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Payment;

interface Update
{
    public function apply($payment);
    public function getTargetOrderCode();
}
