<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\BillPay\BillingResponse;

class CommitPreloadedBillsResponse extends BillPayResponseBase
{
    function map()
    {
        $result = new BillingResponse();

        $result->setIsSuccessful($this->response->getBool("a:isSuccessful"));
        $result->setResponseCode($this->getFirstResponseCode($this->response));
        $result->setResponseMessage($this->getFirstResponseMessage($this->response));

        return $result;
    }
}