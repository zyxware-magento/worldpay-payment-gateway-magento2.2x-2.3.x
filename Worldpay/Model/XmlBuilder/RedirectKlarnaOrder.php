<?php
namespace Zyxware\Worldpay\Model\XmlBuilder;
use Zyxware\Worldpay\Model\XmlBuilder\Config\ThreeDSecureConfig;

class RedirectKlarnaOrder
{
    const EXPONENT = 2;
    const DYNAMIC3DS_DO3DS = 'do3DS';
    const DYNAMIC3DS_NO3DS = 'no3DS';
    const TOKEN_SCOPE = 'shopper';
    const ROOT_ELEMENT = <<<EOD
<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE paymentService PUBLIC '-//WorldPay/DTD WorldPay PaymentService v1//EN'
        'http://dtd.worldpay.com/paymentService_v1.dtd'> <paymentService/>
EOD;

    private $merchantCode;
    private $orderCode;
    private $orderDescription;
    private $currencyCode;
    private $amount;
    private $paymentType;
    private $shopperEmail;
    private $acceptHeader;
    private $userAgentHeader;
    private $shippingAddress;
    private $billingAddress;
    private $paymentPagesEnabled;
    private $installationId;
    private $hideAddress;


    private $threeDSecureConfig;

    private $tokenRequestConfig;

   public function __construct()
    {
         $this->threeDSecureConfig = new \Zyxware\Worldpay\Model\XmlBuilder\Config\ThreeDSecure();

        $this->tokenRequestConfig = false;

    }

    public function build(
        $merchantCode,
        $orderCode,
        $orderDescription,
        $currencyCode,
        $amount,
        $paymentType,
        $shopperEmail,
        $acceptHeader,
        $userAgentHeader,
        $shippingAddress,
        $billingAddress,
        $paymentPagesEnabled,
        $installationId,
        $hideAddress,
        $orderlineitems
    )
    {
        $this->merchantCode = $merchantCode;
        $this->orderCode = $orderCode;
        $this->orderDescription = $orderDescription;
        $this->currencyCode = $currencyCode;
        $this->amount = $amount;
        $this->paymentType = $paymentType;
        $this->shopperEmail = $shopperEmail;
        $this->acceptHeader = $acceptHeader;
        $this->userAgentHeader = $userAgentHeader;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->paymentPagesEnabled = $paymentPagesEnabled;
        $this->installationId = $installationId;
        $this->hideAddress = $hideAddress;
        $this->orderlineitems = $orderlineitems;

        $xml = new \SimpleXMLElement(self::ROOT_ELEMENT);
        $xml['merchantCode'] = $this->merchantCode;
        $xml['version'] = '1.4';

        $submit = $this->_addSubmitElement($xml);
        $this->_addOrderElement($submit);

        return $xml;
    }

    private function _addSubmitElement($xml)
    {
        return $xml->addChild('submit');
    }

    private function _addOrderElement($submit)
    {
        $order = $submit->addChild('order');
        $order['orderCode'] = $this->orderCode;

        if ($this->paymentPagesEnabled) {
            $order['installationId'] = $this->installationId;

            $order['fixContact'] = 'true';
            $order['hideContact'] = 'true';

            if ($this->hideAddress) {
                $order['fixContact'] = 'false';
                $order['hideContact'] = 'false';
            }
        }

        $this->_addDescriptionElement($order);
        $this->_addAmountElement($order);
        $this->_addPaymentMethodMaskElement($order);
        $this->_addShopperElement($order);
        $this->_addShippingElement($order);
        $this->_addBillingElement($order);
        $this->_addOrderLineItemElement($order);
        $this->_addDynamic3DSElement($order);

        return $order;
    }

    private function _addDescriptionElement($order)
    {
        $description = $order->addChild('description');
        $this->_addCDATA($description, $this->orderDescription);
    }

    private function _addAmountElement($order)
    {
        $amountElement = $order->addChild('amount');
        $amountElement['currencyCode'] = $this->currencyCode;
        $amountElement['exponent'] = self::EXPONENT;
        //$amountElement['value'] = $this->_amountAsInt($this->amount);
        $amountElement['value'] = $this->_amountAsInt($this->_roundOfTotal($order));
    }

    private function _addDynamic3DSElement($order)
    {
        if ($this->threeDSecureConfig->isDynamic3DEnabled() === false) {
            return;
        }

        $threeDSElement = $order->addChild('dynamic3DS');
        if ($this->threeDSecureConfig->is3DSecureCheckEnabled()) {
            $threeDSElement['overrideAdvice'] = self::DYNAMIC3DS_DO3DS;
        } else {
            $threeDSElement['overrideAdvice'] = self::DYNAMIC3DS_NO3DS;
        }
    }

    private function _addPaymentMethodMaskElement($order)
    {
        $paymentMethodMask = $order->addChild('paymentMethodMask');

        $include = $paymentMethodMask->addChild('include');
        $include['code'] = $this->paymentType;
    }

    private function _addShopperElement($order)
    {
        $shopper = $order->addChild('shopper');

        $shopper->addChild('shopperEmailAddress', $this->shopperEmail);

        $browser = $shopper->addChild('browser');

        $acceptHeader = $browser->addChild('acceptHeader');
        $this->_addCDATA($acceptHeader, $this->acceptHeader);

        $userAgentHeader = $browser->addChild('userAgentHeader');
        $this->_addCDATA($userAgentHeader, $this->userAgentHeader);
    }

    private function _addShippingElement($order)
    {
        $shippingAddress = $order->addChild('shippingAddress');
        $this->_addAddressElement(
            $shippingAddress,
            $this->shippingAddress['firstName'],
            $this->shippingAddress['lastName'],
            $this->shippingAddress['street'],
            $this->shippingAddress['postalCode'],
            $this->shippingAddress['city'],
            $this->shippingAddress['countryCode']
        );
    }

    private function _addBillingElement($order)
    {
        $billingAddress = $order->addChild('billingAddress');
        $this->_addAddressElement(
            $billingAddress,
            $this->billingAddress['firstName'],
            $this->billingAddress['lastName'],
            $this->billingAddress['street'],
            $this->billingAddress['postalCode'],
            $this->billingAddress['city'],
            $this->billingAddress['countryCode']
        );
    }

    private function _addAddressElement($parentElement, $firstName, $lastName, $street, $postalCode, $city, $countryCode)
    {
        $address = $parentElement->addChild('address');

        $firstNameElement = $address->addChild('firstName');
        $this->_addCDATA($firstNameElement, $firstName);

        $lastNameElement = $address->addChild('lastName');
        $this->_addCDATA($lastNameElement, $lastName);

        $streetElement = $address->addChild('street');
        $this->_addCDATA($streetElement, $street);

        $postalCodeElement = $address->addChild('postalCode');
        $this->_addCDATA($postalCodeElement, $postalCode);

        $cityElement = $address->addChild('city');
        $this->_addCDATA($cityElement, $city);

        $countryCodeElement = $address->addChild('countryCode');
        $this->_addCDATA($countryCodeElement, $countryCode);
    }

    private function _addOrderLineItemElement($order)
    {
        $orderLinesElement = $order->addChild('orderLines');

        $orderlineitems = $this->orderlineitems;

        $orderTaxAmountElement = $orderLinesElement->addChild('orderTaxAmount');
        $this->_addCDATA($orderTaxAmountElement, $this->_amountAsInt($orderlineitems['orderTaxAmount']));

         $termsURLElement = $orderLinesElement->addChild('termsURL');
        $this->_addCDATA($termsURLElement, $orderlineitems['termsURL']);

        foreach($orderlineitems['lineItem'] as $lineitem){
            $totaldiscountamount = (isset($lineitem['totalDiscountAmount'])) ? $lineitem['totalDiscountAmount'] : 0;
            $this->_addLineItemElement($orderLinesElement, $lineitem['reference'], $lineitem['name'], $lineitem['quantity'], $lineitem['quantityUnit'], $lineitem['unitPrice'], $lineitem['taxRate'], $lineitem['totalAmount'], $lineitem['totalTaxAmount'], $totaldiscountamount);
        }
    }

    private function _addLineItemElement($parentElement, $reference, $name, $quantity, $quantityUnit, $unitPrice,
        $taxRate, $totalAmount, $totalTaxAmount, $totalDiscountAmount = 0)
    {
        $unitPrice = sprintf('%0.2f', $unitPrice);
        $totalAmount = $quantity * $unitPrice;

        $lineitem = $parentElement->addChild('lineItem');

        $lineitem->addChild('physical');

        $referenceElement = $lineitem->addChild('reference');
        $this->_addCDATA($referenceElement, $reference);

          $nameElement = $lineitem->addChild('name');
        $this->_addCDATA($nameElement, $name);

          $quantityElement = $lineitem->addChild('quantity');
        $this->_addCDATA($quantityElement, $quantity);

          $quantityUnitElement = $lineitem->addChild('quantityUnit');
        $this->_addCDATA($quantityUnitElement, $quantityUnit);

          $unitPriceElement = $lineitem->addChild('unitPrice');
        $this->_addCDATA($unitPriceElement, $this->_amountAsInt($unitPrice));

          $taxRateElement = $lineitem->addChild('taxRate');
        $this->_addCDATA($taxRateElement, $this->_amountAsInt($taxRate));

          $totalAmountElement = $lineitem->addChild('totalAmount');
        $this->_addCDATA($totalAmountElement, $this->_amountAsInt($totalAmount));

          $totalTaxAmountElement = $lineitem->addChild('totalTaxAmount');
        $this->_addCDATA($totalTaxAmountElement, $this->_amountAsInt($totalTaxAmount));

         if($totalDiscountAmount > 0){
          $totalDiscountAmountElement = $lineitem->addChild('totalDiscountAmount');
          $this->_addCDATA($totalDiscountAmountElement, $this->_amountAsInt($totalDiscountAmount));
        }
    }

    private function _addCDATA($element, $content)
    {
        $node = dom_import_simplexml($element);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($content));
    }

    private function _amountAsInt($amount)
    {
        return round($amount, self::EXPONENT, PHP_ROUND_HALF_EVEN) * pow(10, self::EXPONENT);
    }

    private function _roundOfTotal($order){
        $accTotalAmt = 0;

        $orderlineitems = $this->orderlineitems;
        foreach($orderlineitems['lineItem'] as $lineitem){
            $totaldiscountamount = (isset($lineitem['totalDiscountAmount'])) ? sprintf('%0.2f',$lineitem['totalDiscountAmount']) : 0;
            $unitPrice = sprintf('%0.2f', $lineitem['unitPrice']);
            $accTotalAmt = $accTotalAmt + ($lineitem['quantity'] * $unitPrice) - $totaldiscountamount;
        }
        return $accTotalAmt;
    }
}
