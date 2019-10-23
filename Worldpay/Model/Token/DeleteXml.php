<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Token;

use Zyxware\Worldpay\Model\SavedToken;
/**
 * Represents the token delete xml response from WP server
 */
class DeleteXml implements UpdateInterface
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
        return (string)$this->_xml->reply->ok->deleteTokenReceived['paymentTokenID'];
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return isset($this->_xml->reply->ok);
    }
}
