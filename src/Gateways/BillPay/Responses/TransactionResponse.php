<?php

namespace GlobalPayments\Api\Gateways\BillPay\Responses;

use GlobalPayments\Api\Entities\Transaction;

class TransactionResponse extends BillPayResponseBase
{
    function map(): Transaction
    {
        $result = new Transaction();
        $result->responseCode = $this->response->getString("a:ResponseCode");
        $result->responseMessage = $this->getFirstResponseMessage($this->response);
        $result->avsResponseCode = $this->response->getString("a:AvsResponseCode");
        $result->avsResponseMessage = $this->response->getString("a:AvsResponseText");
        $result->cvnResponseCode = $this->response->getString("a:CvvResponseCode");
        $result->cvnResponseMessage = $this->response->getString("a:CvvResponseText");
        $result->clientTransactionId = $this->response->getString("a:MerchantTransactionID");
        $result->timestamp = $this->response->getString("a:TransactionDate");
        $result->transactionId = $this->response->getString("a:Transaction_ID");
        $result->referenceNumber = $this->response->getString("a:ReferenceTransactionID");
        $result->token = $this->response->getString("a:Token");
        $result->convenienceFee = $this->response->getFloat("a:ConvenienceFee");

        return $result;
    }
}