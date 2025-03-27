<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\BillPay\LoadSecurePayResponse;

class SecurePayResponse extends BillPayResponseBase
{
    function map(): LoadSecurePayResponse
    {
        $result = new LoadSecurePayResponse();
        $result->setPaymentIdentifier($this->response->getString("a:GUID"));
        $result->setIsSuccessful($this->response->getBool("a:isSuccessful"));
        $result->setResponseCode($this->response->getString("a:ResponseCode"));
        $result->setResponseMessage($this->getFirstResponseMessage($this->response));

        return $result;
    }
}