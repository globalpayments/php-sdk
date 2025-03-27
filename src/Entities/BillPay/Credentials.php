<?php

namespace GlobalPayments\Api\Entities\BillPay;

class Credentials 
{
    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $merchantName;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $userName;

    public function getApiKey() {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getMerchantName() {
        return $this->merchantName;
    }

    public function setMerchantName(string $merchantName)
    {
        $this->merchantName = $merchantName;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getUsername() {
        return $this->userName;
    }

    public function setUsername(string $userName)
    {
        $this->userName = $userName;
    }
}