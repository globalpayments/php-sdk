<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Gateways\GeniusConnector;
use GlobalPayments\Api\Gateways\Gp3DSProvider;
use GlobalPayments\Api\Gateways\MerchantwareConnector;
use GlobalPayments\Api\Gateways\RealexConnector;

class GeniusConfig extends GatewayConfig
{
    /** @var GatewayProvider */
    public $gatewayProvider;

    public $clearkId;
    public $merchantName;
    public $merchantSiteId;
    public $merchantKey;
    public $registerNumber;
    public $dba;
    public $terminalId;

    public function __construct()
    {
        $this->gatewayProvider = GatewayProvider::GENIUS;
    }

    public function configureContainer(ConfiguredServices $services)
    {
        if (empty($this->serviceUrl)) {
            $this->serviceUrl = $this->environment == Environment::TEST ? ServiceEndpoints::MERCHANTWARE_TEST : ServiceEndpoints::MERCHANTWARE_PRODUCTION;
        }

        $gateway = new GeniusConnector();
        $gateway->merchantName = $this->merchantName;
        $gateway->merchantSiteId = $this->merchantSiteId;
        $gateway->merchantKey = $this->merchantKey;
        $gateway->registerNumber = $this->registerNumber;
        $gateway->terminalId = $this->terminalId;
        $gateway->timeout = $this->timeout;
        $gateway->serviceUrl = $this->serviceUrl;
        $gateway->webProxy = $this->webProxy;
        
        $services->gatewayConnector = $gateway;
    }

    public function validate()
    {
        parent::validate();

        if (empty($this->merchantSiteId)) {
            throw new ConfigurationException('MerchantSiteId is required for this configuration.');
        }

        if (empty($this->merchantName)) {
            throw new ConfigurationException('Merchantname is required for this configuration.');
        }

        if (empty($this->merchantKey)) {
            throw new ConfigurationException('MerchantKey is required for this configuration.');
        }
    }
}
