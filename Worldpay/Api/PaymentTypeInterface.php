<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Api;
 
interface PaymentTypeInterface
{
    /**
     * Retrive Payment Types
     *
     * @api
     * @param string $countryId.
     * @return null|string 
     */
    public function getPaymentType($countryId);

    /**
     * Retrive Payment Types
     *
     * @api
     * @param string $countryId.
     * @return null|string 
     */
    public function getCCTypes();
}