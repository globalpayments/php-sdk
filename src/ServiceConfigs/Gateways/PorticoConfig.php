<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Gateways\PayPlanConnector;
use GlobalPayments\Api\Gateways\PorticoConnector;
use GlobalPayments\Api\Gateways\ProPayConnector;

class PorticoConfig extends GatewayConfig
{
    /** @var GatewayProvider */
    public $gatewayProvider = GatewayProvider::PORTICO;

    // Portico
    public $siteId;
    public $licenseId;
    public $deviceId;
    public $username;
    public $password;
    public $developerId;
    public $versionNumber;
    public $secretApiKey;
    public $uniqueDeviceId;
    
    //ProPay
    public $certificationStr;
    public $selfSignedCertLocation;
    public $proPayUS = true;

    public function getPayPlanEndpoint()
    {
        if (strpos(strtolower($this->secretApiKey), 'cert') !== false  || (empty($this->secretApiKey) && $this->environment = Environment::TEST)) {
            return '/Portico.PayPlan.v2/';
        }
        return '/PayPlan.v2/';
    }

    // Common
    public $curlOptions;

    /** @var Environment */
    public $environment;
    public $serviceUrl;
    public $timeout;

    public function __construct()
    {
        $this->gatewayProvider = GatewayProvider::PORTICO;
    }

    public function configureContainer(ConfiguredServices $services)
    {
        if (!empty($this->secretApiKey)) {
            if (strpos($this->secretApiKey, '_prod_') !== false) {
                $this->serviceUrl = ServiceEndpoints::PORTICO_PRODUCTION;
            } else {
                $this->serviceUrl = ServiceEndpoints::PORTICO_TEST;
            }
        }

        if (empty($this->serviceUrl)) {
            $this->serviceUrl = $this->environment == Environment::TEST ? ServiceEndpoints::PORTICO_TEST : ServiceEndpoints::PORTICO_PRODUCTION; // check this
        }

        $gateway = new PorticoConnector();
        $gateway->siteId = $this->siteId;
        $gateway->licenseId = $this->licenseId;
        $gateway->deviceId = $this->deviceId;
        $gateway->username = $this->username;
        $gateway->password = $this->password;
        $gateway->secretApiKey = $this->secretApiKey;
        $gateway->developerId = $this->developerId;
        $gateway->versionNumber = $this->versionNumber;
        $gateway->timeout = $this->timeout;
        $gateway->serviceUrl = $this->serviceUrl . '/Hps.Exchange.PosGateway/PosGatewayService.asmx';
        $gateway->uniqueDeviceId = $this->uniqueDeviceId;
        $gateway->requestLogger = $this->requestLogger;
        $gateway->webProxy = $this->webProxy;
        
        $services->gatewayConnector = $gateway;

        if (empty($this->dataClientId)) {
            $services->reportingService = $gateway;
        }
        
        $payplan = new PayPlanConnector();
        $payplan->secretApiKey = $this->secretApiKey;
        $payplan->developerId = $this->developerId;
        $payplan->versionNumber = $this->versionNumber;
        $payplan->timeout = $this->timeout;
        $payplan->serviceUrl = $this->serviceUrl . $this->getPayPlanEndpoint();

        $services->recurringConnector = $payplan;
        
        //propay connector
        if (!empty($this->certificationStr)) {
            if ($this->environment === Environment::TEST) {
                $this->serviceUrl = ($this->proPayUS) ? ServiceEndpoints::PROPAY_TEST : ServiceEndpoints::PROPAY_TEST_CANADIAN;
            } else {
                $this->serviceUrl = ($this->proPayUS) ? ServiceEndpoints::PROPAY_PRODUCTION : ServiceEndpoints::PROPAY_PRODUCTION_CANADIAN;
            }
            
            $payFac = new ProPayConnector();
            $payFac->certStr = $this->certificationStr;
            $payFac->termId = $this->terminalId;
            $payFac->timeout = $this->timeout;
            $payFac->serviceUrl = $this->serviceUrl;
            $payFac->selfSignedCert = $this->selfSignedCertLocation;
            
            $services->setPayFacProvider($payFac);
        }
    }

    public function validate()
    {
        parent::validate();
        
        // Portico API key
        if (!empty($this->secretApiKey)
            && (
                !empty($this->siteId)
                || !empty($this->licenseId)
                || !empty($this->deviceId)
                || !empty($this->username)
                || !empty($this->password)
            )
        ) {
            throw new ConfigurationException(
                "Configuration contains both secret API key and legacy credentials. These are mutually exclusive."
            );
        }

        // Portico legacy
        if ((
            !empty($this->siteId)
                || !empty($this->licenseId)
                || !empty($this->deviceId)
                || !empty($this->username)
                || !empty($this->password)
            )
            && (
                empty($this->siteId)
                || empty($this->licenseId)
                || empty($this->deviceId)
                || empty($this->username)
                || empty($this->password)
            )
        ) {
            throw new ConfigurationException(
                "Site, License, Device, Username, and Password should all have values for this configuration."
            );
        }
    }
}
