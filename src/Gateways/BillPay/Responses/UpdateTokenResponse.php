<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\Transaction;

class UpdateTokenResponse extends BillPayResponseBase
{
    function map(): Transaction
    {
        $result = new Transaction();

        $result->responseCode = $this->response->getString("a:ResponseCode");
        $result->responseMessage = $this->getFirstResponseMessage($this->response);

        return $result;
    }
}