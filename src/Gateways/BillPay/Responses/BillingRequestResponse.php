<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\BillPay\BillingResponse;

class BillingRequestResponse extends BillPayResponseBase
{
    function map()
    {
        $result = new BillingResponse();

        $result->setIsSuccessful($this->response->getBool("a:isSuccessful"));
        $result->setResponseCode($this->response->getString("a:ResponseCode"));
        $result->setResponseMessage($this->getFirstResponseMessage($this->response));

        return $result;
    }
}