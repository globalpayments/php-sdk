<?php

namespace GlobalPayments\Api\Entities\BillPay;

class Credentials 
{
    /**
     * @var string|null
     */
    protected ?string $apiKey = null;

    /**
     * @var string
     */
    protected ?string $merchantName = null;

    /**
     * @var string
     */
    protected ?string $password = null;

    /**
     * @var string
     */
    protected ?string $userName = null;

    public function getApiKey(): ?string {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getMerchantName(): ?string {
        return $this->merchantName;
    }

    public function setMerchantName(string $merchantName): void
    {
        $this->merchantName = $merchantName;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getUsername(): ?string {
        return $this->userName;
    }

    public function setUsername(string $userName): void
    {
        $this->userName = $userName;
    }
}