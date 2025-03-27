<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\Transaction;

class TokenRequestResponse extends BillPayResponseBase
{
    function map(): Transaction
    {
        $result = new Transaction();

        $result->responseCode = $this->response->getString("a:ResponseCode");
        $result->responseMessage = $this->getFirstResponseMessage($this->response);
        $result->token = $this->response->getString("a:Token");

        return $result;
    }
}