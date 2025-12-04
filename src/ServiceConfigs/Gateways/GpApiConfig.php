<?php

declare(strict_types=1);

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\GpApi\GpApiSessionInfo;
use GlobalPayments\Api\Gateways\GpApiConnector;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Gateways\IAccessTokenProvider;

class GpApiConfig extends GatewayConfig
{
    public $appId;
    public $appKey;
    public $siteId;
    public $licenseId;
    public $deviceId;
    public $username;
    public $password;
    public $secretApiKey;
    
    /**
     * @var AccessTokenInfo
    */
    public $accessTokenInfo;
    
    /**
     * @var string
    */
    public $country = 'US';

    /**
     * @var string
    */
    public $channel;

    /**
     * @var int
    */
    public $secondsToExpire;

    /**
     * @var string
    */
    public $intervalToExpire;

    /**
     * @var string
    */
    public $methodNotificationUrl;

    /**
     * @var string
    */
    public $challengeNotificationUrl;

    /**
     * @var string
    */
    public $merchantContactUrl;

    /**
     * @var array
    */
    public $permissions;

    /**
     * @var string
    */
    public $gatewayProvider;

    /**
     * @var string
    */
    public $merchantId;

    /**
     * @var string
    */
    public $deviceCurrency;

    /**
     * @var string
    */
    public string $statusUrl;

    /**
     * @var string
    */
    public string $transactionAccountName;

    /**
     * @var IAccessTokenProvider
    */
    public IAccessTokenProvider $accessTokenProvider;

    public function __construct()
    {
        $this->gatewayProvider = GatewayProvider::GP_API;
    }

    public function configureContainer(ConfiguredServices $services)
    {
        if (empty($this->serviceUrl)) {
            $this->serviceUrl = ($this->environment == Environment::PRODUCTION) ?
                ServiceEndpoints::GP_API_PRODUCTION : ServiceEndpoints::GP_API_TEST;
        }
        if (!isset($accessTokenProvider)) {
            $this->accessTokenProvider = new GpApiSessionInfo();
        }

        $gateway = new GpApiConnector($this);
        $gateway->serviceUrl = $this->serviceUrl;
        $gateway->requestLogger = $this->requestLogger;
        $gateway->webProxy = $this->webProxy;
        $gateway->dynamicHeaders = $this->dynamicHeaders;
        $gateway->environment = $this->environment;

        $services->gatewayConnector = $gateway;
        $services->reportingService = $gateway;
        $services->fraudService = $gateway;
        $services->fileProcessingService = $gateway;
        $services->recurringConnector = $gateway;
        $services->installmentService = $gateway;

        $services->setOpenBankingProvider($gateway);
        $services->setPayFacProvider($gateway);
        $services->setSecure3dProvider(Secure3dVersion::ONE, $gateway);
        $services->setSecure3dProvider(Secure3dVersion::TWO, $gateway);
    }

    public function validate(): void
    {
        parent::validate();
        
        if (!empty($this->accessTokenInfo)) {
            return;
        }
        
        $hasGpApiCredentials = !empty($this->appId) && !empty($this->appKey);
        $hasPorticoCredentials = !empty($this->deviceId) && !empty($this->siteId) && 
            !empty($this->licenseId) && !empty($this->username) && !empty($this->password);
        $hasSecretApiKey = !empty($this->secretApiKey);
        
        if (!$hasGpApiCredentials && !$hasPorticoCredentials && !$hasSecretApiKey) {
            throw new ConfigurationException(
                'AccessTokenInfo or (AppId and AppKey) or (Portico 5-point credentials: deviceId, siteId, licenseId, username, password) or SecretApiKey must be provided'
            );
        }
        
        if ((!empty($this->deviceId) || !empty($this->siteId) || !empty($this->licenseId) || 
             !empty($this->username) || !empty($this->password)) && !$hasPorticoCredentials) {
            throw new ConfigurationException(
                'When using Portico credentials, all 5 fields must be provided: deviceId, siteId, licenseId, username, and password'
            );
        }
    }
}