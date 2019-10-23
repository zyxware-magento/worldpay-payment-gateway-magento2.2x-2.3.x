<?php
namespace Zyxware\Worldpay\Model\InstantPurchase\CreditCard;

class AvailabilityChecker implements \Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface
{
    /**
     * @var Config
     */
    private $config;

    private $wplogger;
    /**
     * AvailabilityChecker constructor.
     * @param Config $config
     */
    public function __construct(\Zyxware\Worldpay\Helper\Data $worldpayHelper, \Zyxware\Worldpay\Logger\WorldpayLogger $wplogger)
    {
        $this->config = $worldpayHelper;
        $this->wplogger = $wplogger;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        if ($this->config->isWorldPayEnable() &&
            $this->config->isCreditCardEnabled() &&
            $this->config->instantPurchaseEnabled() ) {
            return true;
         }
         $this->wplogger->info("Instant Purchase is disabled:");
         return false;
    }
}
