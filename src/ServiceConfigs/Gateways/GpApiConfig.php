<?php


namespace GlobalPayments\Api\ServiceConfigs\Gateways;


use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Gateways\GpApiConnector;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;

class GpApiConfig extends GatewayConfig
{
    //GP-API
    public $appId;
    public $appKey;

    /**
     * @var $accessTokenInfo AccessTokenInfo
     */
    public $accessTokenInfo;
    /**
     * Country from which the transaction is done from
     * @var $country string
     */
    public $country = 'US';

    /**
     * Transaction channel for GP-API
     * Can be CP (Card Present) or CNP (Card Not Present)
     *
     * @var $channel string
     */
    public $channel;

    /**
     * The time left in seconds before the token expires
     * @var int
     */
    public $secondsToExpire;

    /**
     * The time interval set for when the token will expire
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

    /** @var string */
    public $merchantId;

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
        $gateway = new GpApiConnector($this);
        $gateway->serviceUrl = $this->serviceUrl;
        $gateway->requestLogger = $this->requestLogger;
        $gateway->webProxy = $this->webProxy;
        $gateway->dynamicHeaders = $this->dynamicHeaders;

        $services->gatewayConnector = $gateway;
        $services->reportingService = $gateway;
        $services->fraudService = $gateway;

        $services->setOpenBankingProvider($gateway);
        $services->setPayFacProvider($gateway);
        $services->setSecure3dProvider(Secure3dVersion::ONE, $gateway);
        $services->setSecure3dProvider(Secure3dVersion::TWO, $gateway);
    }

    public function validate()
    {
        parent::validate();
        if (
            empty($this->accessTokenInfo) &&
            (empty($this->appId) || empty($this->appKey))
        ) {
            throw new ConfigurationException('AccessTokenInfo or AppId and AppKey cannot be null');
        }
    }
}