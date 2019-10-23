<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Token;
/**
 * Interface Zyxware_WorldPay_Model_Token_UpdateInterface
 *
 * Describe what can be read from WP's token update response
 */
interface UpdateInterface
{
    /**
     * @return string
     */
    public function getTokenCode();

    /**
     * @return boolean
     */
    public function isSuccess();

}
