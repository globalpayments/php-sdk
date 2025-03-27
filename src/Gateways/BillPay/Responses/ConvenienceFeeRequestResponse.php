<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\BillPay\ConvenienceFeeResponse;

class ConvenienceFeeRequestResponse extends BillPayResponseBase
{
    function map() 
    {
        $result = new ConvenienceFeeResponse();

        $result->setIsSuccessful($this->response->getBool("a:isSuccessful"));
        $result->setResponseCode($this->response->getString("a:ResponseCode"));
        $result->setResponseMessage($this->getFirstResponseMessage($this->response));
        $result->convenienceFee = $this->response->getFloat("a:ConvenienceFee");

        return $result;
    }
}