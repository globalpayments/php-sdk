<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways;

class AcsResponse
{
    /**
     * @var string
     */
    private $authResponse;

    /**
     * @var string
     */
    private $merchantData;

    /**
     * @var string
     */
    private $status;

    /**
     * @return string
     */
    public function getAuthResponse()
    {
        return $this->authResponse;
    }

    /**
     * @return void
     */
    public function setAuthResponse($authResponse)
    {
        $this->authResponse = $authResponse;
    }

    /**
     * @return string
     */
    public function getMerchantData()
    {
        return $this->merchantData;
    }

    /**
     * @return void
     */
    public function setMerchantData($merchantData)
    {
        $this->merchantData = $merchantData;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }
}
