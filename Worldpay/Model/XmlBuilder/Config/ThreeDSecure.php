<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\XmlBuilder\Config;

/**
 * Get ThreeDSecure Configuration
 */
class ThreeDSecure
{
    private $isDynamic3D = false;
    private $is3DSecure = false;

    /**
     * @param bool $isDynamic3D
     * @param bool $is3DSecure
     */
    public function __construct($isDynamic3D = false, $is3DSecure = false)
    {
        $this->isDynamic3D = (bool)$isDynamic3D;
        $this->is3DSecure = (bool)$is3DSecure;
    }

    /**
     * @return bool
     */
    public function isDynamic3DEnabled()
    {
        return $this->isDynamic3D;
    }

    /**
     * @return bool
     */
    public function is3DSecureCheckEnabled()
    {
        return $this->is3DSecure;
    }
}
