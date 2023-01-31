<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Gateways\TransactionApiConnector;

class TransactionApiConfig extends GatewayConfig
{
    /**
     * trsansaction apiKey
     * @var $apiKey string
     */
    public $apiKey;

    /**
     * trsansaction apiVersion
     * @var $apiVersion string
     */
    public $apiVersion;

    /**
     * trsansaction apiPartnerName
     * @var $apiPartnerName string
     */
    public $apiPartnerName;

    /**
     * trsansaction accountCredential
     * @var $accountCredential string
     */
    public $accountCredential;

    /**
     * trsansaction apiSecret
     * @var $apiSecret string
     */
    public $apiSecret;

    /**
     * Country from which the transaction is done from
     * @var $country string
     */
    public $country;

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

    /**
     * @var string
     */
    public $merchantId;

    public function __construct()
    {
        $this->gatewayProvider = GatewayProvider::TRANSACTION_API;
    }

    public function configureContainer(ConfiguredServices $services)
    {
        if (empty($this->serviceUrl)) {
            $this->serviceUrl = ($this->environment == Environment::PRODUCTION) ?
                ServiceEndpoints::TRANSACTION_API_PROD : ServiceEndpoints::TRANSACTION_API_TEST;
        }
        $gateway = new TransactionApiConnector($this);
        $gateway->serviceUrl = $this->serviceUrl;
        $gateway->requestLogger = $this->requestLogger;
        $gateway->webProxy = $this->webProxy;
        $gateway->dynamicHeaders = $this->dynamicHeaders;

        $services->gatewayConnector = $gateway;
        $services->reportingService = $gateway;
    }

    public function validate()
    {
        parent::validate();
        if ((empty($this->apiKey) || empty($this->apiSecret)) && empty($this->accountCredential)) {
            throw new ConfigurationException('ApiKey or ApiSecret and AccountCredential cannot be null');
        }
    }
}
