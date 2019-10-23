<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Token;

use Zyxware\Worldpay\Model\SavedToken;
/**
 * read from WP's token update response
 */
class UpdateXml implements UpdateInterface
{
    /**
     * @var SimpleXMLElement
     */
    private $_xml;

    /**
     * @param SimpleXMLElement $xml
     */
    public function __construct(\SimpleXMLElement $xml)
    {
        $this->_xml = $xml;
    }

    /**
     * @return string
     */
    public function getTokenCode()
    {
        return (string)$this->_xml->reply->ok->updateTokenReceived['paymentTokenID'];
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return isset($this->_xml->reply->ok);
    }
}
