<?php

namespace GlobalPayments\Api\Entities\BillPay;

class BillingResponse
{
    /**
     * Indicates if the action was successful
     * @var bool 
     */
    protected $isSuccessful;

    /**
     * The response code from the Billing Gateway
     * @var string
     */
    protected $responseCode;

    /**
     * The response message from the Billing Gateway
     * @var ?string
     */
    protected $responseMessage;

    public function isSuccessful()
    {
        return $this->isSuccessful;
    }

    public function getResponseCode()
    {
        return $this->responseCode;
    }

    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    public function setIsSuccessful(bool $isSuccessful)
    {
        $this->isSuccessful = $isSuccessful;
    }

    public function setResponseCode(string $responseCode)
    {
        $this->responseCode = $responseCode;
    }

    public function setResponseMessage(?string $responseMessage)
    {
        $this->responseMessage = $responseMessage;
    }
}