<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Model\Response;

class AdminhtmlResponse extends \Zyxware\Worldpay\Model\Response\ResponseAbstract
{
	
    public function parseRefundResponse($xml)
    {
        $document = new \SimpleXmlElement($xml);
        return $document;
    }

    public function parseInquiryResponse($xml)
    {
        $document = new \SimpleXmlElement($xml);
        return $document;
    }

}
 