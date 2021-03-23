<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Gateways\TransITConnector;
use GlobalPayments\Api\ConfiguredServices;

class TransitConfig extends GatewayConfig
{
    /** @var GatewayProvider */
    public $gatewayProvider;
    public $deviceId;
    public $developerId;
    public $merchantId;
    public $transactionKey;
    public $username;
    public $password;

    public function __construct()
    {
        $this->gatewayProvider = GatewayProvider::TRANSIT;
    }

    public function configureContainer(ConfiguredServices $services)
    {
        $gateway = new TransITConnector();
        $gateway->deviceId = $this->deviceId;
        $gateway->developerId = $this->developerId;
        $gateway->timeout = $this->timeout;
        $gateway->serviceUrl = $this->serviceUrl;
        $gateway->requestLogger = $this->requestLogger;
        $gateway->acceptorConfig = $this->acceptorConfig;
        $gateway->merchantId = $this->merchantId;
        $gateway->transactionKey = $this->transactionKey;
        $gateway->userId = $this->username;
        $gateway->password = $this->password;
        $gateway->webProxy = $this->webProxy;

        if (empty($this->serviceUrl)) {
            $gateway->serviceUrl = $this->environment == Environment::TEST ? ServiceEndpoints::TRANSIT_TEST : ServiceEndpoints::TRANSIT_PRODUCTION;
        }

        $services->gatewayConnector = $gateway;
    }

    public function validate()
    {
        parent::validate();
        
        if ($this->acceptorConfig == null) {
            throw new ConfigurationException("You must provide a valid AcceptorConfig.");
        } else {
            $this->acceptorConfig->validate();
        }

        if (empty($this->deviceId)) {
            throw new ConfigurationException("DeviceId cannot be null.");
        }

        if (empty($this->merchantId)) {
            throw new ConfigurationException("MerchantId cannot be null.");
        }
    }
}
