<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\ServiceConfigs\Configuration;
use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\{Environment, ServiceEndpoints};
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Gateways\BillPayProvider;

class BillPayConfig extends Configuration 
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $merchantName;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var bool */
    private $useBillRecordLookup = false;

    public function getApiKey() : string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey) : void
    {
        $this->apiKey = $apiKey;
    }

    public function getMerchantName() : string
    {
        return $this->merchantName;
    }

    public function setMerchantName(string $merchantName) : void
    {
        $this->merchantName = $merchantName;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function setUsername(string $username) : void
    {
        $this->username = $username;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function setPassword(string $password) : void
    {
        $this->password = $password;
    }

    public function getUseBillRecordLookup() : bool
    {
        return $this->useBillRecordLookup;
    }

    public function setUseBillRecordLookup(bool $useBillRecordLookup) : void
    {
        $this->useBillRecordLookup = $useBillRecordLookup;
    }

    public function configureContainer(ConfiguredServices $services) : void
    {
        if (empty($this->serviceUrl)) {
            $this->serviceUrl = $this->environment == Environment::TEST ? ServiceEndpoints::BILLPAY_CERTIFICATION : ServiceEndpoints::BILLPAY_PRODUCTION;
        }

        $credentials = new Credentials();
        $credentials->setUsername($this->username);
        $credentials->setPassword($this->password);
        $credentials->setApiKey($this->apiKey);
        $credentials->setMerchantName($this->merchantName);

        $gateway = new BillPayProvider();
        $gateway->setCredentials($credentials);
        $gateway->setServiceUrl($this->serviceUrl . "/BillingDataManagement/v3/BillingDataManagementService.svc/BillingDataManagementService");
        $gateway->requestLogger = $this->requestLogger;
        $gateway->setTimeout($this->timeout);
        $gateway->setIsBillDataHosted($this->useBillRecordLookup);
        
        $services->gatewayConnector = $gateway;
        $services->setBillingProvider($gateway);
        $services->recurringConnector = $gateway;
    }

    public function validate()
    {
        parent::validate();

        if ($this->isNullOrEmpty($this->username) || $this->isNullOrEmpty($this->password) && $this->isNullOrEmpty($this->apiKey)) {
            throw new ConfigurationException("Login credentials or an API key is required.");
        }

        if ((!$this->isNullOrEmpty($this->username) || !$this->isNullOrEmpty($this->password)) && !$this->isNullOrEmpty($this->apiKey)) {
        //if ((!StringUtils.isNullOrEmpty(username) || !StringUtils.isNullOrEmpty(password)) && !StringUtils.isNullOrEmpty(apiKey)) {
            throw new ConfigurationException("Cannot provide both login credentials and an API key.");
        }

        if ($this->isNullOrEmpty($this->apiKey)) {
            if ($this->isNullOrEmpty($this->username)) {
                throw new ConfigurationException("Username is missing.");
            }

            if (strlen(trim($this->username)) > 50) {
                throw new ConfigurationException("Username must be 50 characters or less.");
            }

            if ($this->isNullOrEmpty($this->password)) {
                throw new ConfigurationException("Password is missing.");
            }

            if (strlen(trim($this->password)) > 50) {
                throw new ConfigurationException("Password must be 50 characters or less.");
            }
        }

        if ($this->isNullOrEmpty($this->merchantName)) {
            throw new ConfigurationException("Merchant name is required");
        }

        /** @var array<string> */
        $acceptedEndpoints = array();
        array_push($acceptedEndpoints, ServiceEndpoints::BILLPAY_CERTIFICATION);
        array_push($acceptedEndpoints, ServiceEndpoints::BILLPAY_PRODUCTION);
        array_push($acceptedEndpoints, ServiceEndpoints::BILLPAY_TEST);

        if (!in_array($this->serviceUrl, $acceptedEndpoints) && !in_array("localhost", $acceptedEndpoints)) {
            throw new ConfigurationException("Please use one of the pre-defined BillPay service URLs.");
        }
    }

    private function isNullOrEmpty($value): bool
    {
        return $value === null || trim($value) == "";
    }
}