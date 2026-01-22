<?php

namespace GlobalPayments\Api\Entities\BillPay;

class BillingResponse
{
    /**
     * Indicates if the action was successful
     * @var bool 
     */
    protected ?bool $isSuccessful = null;

    /**
     * The response code from the Billing Gateway
     * @var string
     */
    protected ?string $responseCode = null;

    /**
     * The response message from the Billing Gateway
     * @var ?string
     */
    protected ?string $responseMessage = null;

    public function isSuccessful(): ?bool
    {
        return $this->isSuccessful;
    }

    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }

    public function getResponseMessage(): ?string
    {
        return $this->responseMessage;
    }

    public function setIsSuccessful(bool $isSuccessful): void
    {
        $this->isSuccessful = $isSuccessful;
    }

    public function setResponseCode(string $responseCode): void
    {
        $this->responseCode = $responseCode;
    }

    public function setResponseMessage(?string $responseMessage): void
    {
        $this->responseMessage = $responseMessage;
    }
}