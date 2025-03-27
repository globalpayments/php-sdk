<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\BillPay\TokenResponse;

class CreateCustomerAccountResponse extends BillPayResponseBase 
{
    function map()
    {
        $result = new TokenResponse();

        $result->setIsSuccessful($this->response->getBool("a:isSuccessful"));
        $result->setResponseCode($this->response->getString("a:ResponseCode"));
        $result->setResponseMessage($this->getFirstResponseMessage($this->response));
        $result->setToken($this->response->getString("a:Token"));

        return $result;
    }
}