<?php

namespace GlobalPayments\Api\Entities\BillPay;

class TokenResponse extends BillingResponse
{
    /**
     * @var string
     */
    protected $token;

    public function getToken(): string
    {
        return $this->token;
    } 

    public function setToken(string $token)
    {
        $this->token = $token;
    }
}
