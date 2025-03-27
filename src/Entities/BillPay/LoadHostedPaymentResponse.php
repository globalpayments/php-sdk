<?php

namespace GlobalPayments\Api\Entities\BillPay;

class LoadHostedPaymentResponse extends BillingResponse
{
   
    /**
     * Unique identifier for the hosted payment page
     */
    protected string $paymentIdentifier;

    public function getPaymentIdentifier(): string
    {
        return $this->paymentIdentifier;
    }

    public function setPaymentIdentifier(string $paymentIdentifier)
    {
        $this->paymentIdentifier = $paymentIdentifier;
    }
}