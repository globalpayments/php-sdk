<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Utils\Element;

class ReversalResponse extends BillPayResponseBase
{
    function map(): Transaction
    {
        /** @var Element */
        $authorizationElement = $this->response->get(
            "a:ReversalTransactionWithReversalAuthorizations"
        );

        /** @var Transaction */
        $result = new Transaction();

        $result->responseCode = $this->response->getString("a:ResponseCode");
        $result->responseMessage = $this->getFirstResponseMessage($this->response);
        $result->clientTransactionId = $authorizationElement->getString("a:MerchantTransactionID");
        $result->timestamp = $authorizationElement->getString("a:TransactionDate");
        $result->transactionId = $authorizationElement->getString("a:TransactionID");
        $result->referenceNumber = $authorizationElement->getString("a:ReferenceTransactionID");

        return $result;
    }
}