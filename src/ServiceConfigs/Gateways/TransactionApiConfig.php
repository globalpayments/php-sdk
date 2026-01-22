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
    public ?string $apiKey = null;

    /**
     * trsansaction apiVersion
     * @var $apiVersion string
     */
    public ?string $apiVersion = null;

    /**
     * trsansaction apiPartnerName
     * @var $apiPartnerName string
     */
    public ?string $apiPartnerName = null;

    /**
     * trsansaction accountCredential
     * @var $accountCredential string
     */
    public ?string $accountCredential = null;

    /**
     * trsansaction apiSecret
     * @var $apiSecret string
     */
    public ?string $apiSecret = null;

    /**
     * Country from which the transaction is done from
     * @var $country string
     */
    public ?string $country = null;

    /**
     * The time left in seconds before the token expires
     * @var int
     */
    public ?int $secondsToExpire = null;

    /**
     * The time interval set for when the token will expire
     */
    public ?string $intervalToExpire = null;

    /**
     * @var string
     */
    public ?string $methodNotificationUrl = null;

    /**
     * @var string
     */
    public ?string $challengeNotificationUrl = null;

    /**
     * @var string
     */
    public ?string $merchantContactUrl = null;

    /**
     * @var array
     */
    public ?array $permissions = null;

    /**
     * @var string
     */
    public ?string $merchantId = null;

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
