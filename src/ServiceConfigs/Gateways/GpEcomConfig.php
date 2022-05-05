<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Entities\Enums\ShaHashType;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Gateways\Gp3DSProvider;
use GlobalPayments\Api\Gateways\OpenBankingProvider;
use GlobalPayments\Api\Gateways\RealexConnector;
use GlobalPayments\Api\ConfiguredServices;

class GpEcomConfig extends GatewayConfig
{
    /** @var GatewayProvider */
    public $gatewayProvider;

    public $accountId;
    public $merchantId;
    public $rebatePassword;
    public $refundPassword;
    public $sharedSecret;
    public $channel;
    public $hostedPaymentConfig;
    /** @var ShaHashType */
    public $shaHashType = ShaHashType::SHA1;

    // Secure 3D
    public $challengeNotificationUrl;
    public $methodNotificationUrl;
    public $merchantContactUrl;
    public $merchantNotificationUrl;
    public $secure3dVersion;

    //Open banking service
    public $enableBankPayment = false;

    public function __construct()
    {
        $this->gatewayProvider = GatewayProvider::GP_ECOM;
    }

    public function configureContainer(ConfiguredServices $services)
    {
        // parent::configureContainer($services); // must implement data services first
        
        if (empty($this->serviceUrl)) {
            $this->serviceUrl = $this->environment == Environment::TEST ? ServiceEndpoints::GLOBAL_ECOM_TEST : ServiceEndpoints::GLOBAL_ECOM_PRODUCTION;
        }

        $gateway = new RealexConnector();
        $gateway->accountId = $this->accountId;
        $gateway->channel = $this->channel;
        $gateway->merchantId = $this->merchantId;
        $gateway->rebatePassword = $this->rebatePassword;
        $gateway->sharedSecret = $this->sharedSecret;
        $gateway->shaHashType = $this->shaHashType;
        $gateway->timeout = $this->timeout;
        $gateway->serviceUrl = $this->serviceUrl;
        $gateway->refundPassword = $this->refundPassword;
        $gateway->hostedPaymentConfig = $this->hostedPaymentConfig;
        $gateway->requestLogger = $this->requestLogger;
        $gateway->webProxy = $this->webProxy;

        $services->gatewayConnector = $gateway;
        $services->recurringConnector = $gateway;

        if (empty($this->secure3dVersion)) {
            $services->secure3dVersion = Secure3dVersion::ONE;
        }

        if ($this->secure3dVersion == Secure3dVersion::ONE || $this->secure3dVersion == Secure3dVersion::ANY) {
            $services->setSecure3dProvider(Secure3dVersion::ONE, $gateway);
        }

        if ($this->secure3dVersion == Secure3dVersion::TWO || $this->secure3dVersion == Secure3dVersion::ANY) {
            $secure3d2 = new Gp3DSProvider();
            $secure3d2->setMerchantId($gateway->merchantId);
            $secure3d2->setAccountId($gateway->accountId);
            $secure3d2->setSharedSecret($gateway->sharedSecret);
            $secure3d2->serviceUrl = $this->environment == Environment::TEST ? ServiceEndpoints::THREE_DS_AUTH_TEST : ServiceEndpoints::THREE_DS_AUTH_PRODUCTION;
            $secure3d2->setMerchantContactUrl($this->merchantContactUrl);
            $secure3d2->setMethodNotificationUrl($this->methodNotificationUrl);
            $secure3d2->setChallengeNotificationUrl($this->challengeNotificationUrl);
            $secure3d2->timeout = $gateway->timeout;
            $secure3d2->requestLogger = $this->requestLogger;
            $secure3d2->webProxy = $this->webProxy;

            $services->setSecure3dProvider(Secure3dVersion::TWO, $secure3d2);
        }
        if ($this->enableBankPayment === true) {
            $openBanking = new OpenBankingProvider();
            $openBanking->merchantId = $gateway->merchantId;
            $openBanking->accountId = $gateway->accountId;
            $openBanking->sharedSecret = $gateway->sharedSecret;
            $openBanking->shaHashType = $this->shaHashType;
            $openBanking->serviceUrl = $this->environment === Environment::PRODUCTION ?
                ServiceEndpoints::OPEN_BANKING_PRODUCTION : ServiceEndpoints::OPEN_BANKING_TEST;
            $openBanking->timeout = $gateway->timeout;
            $openBanking->requestLogger = $this->requestLogger;
            $openBanking->webProxy = $this->webProxy;

            $services->setOpenBankingProvider($openBanking);
        }
    }

    public function validate()
    {
        parent::validate();
        
        if (empty($this->merchantId)) {
            throw new ConfigurationException("MerchantId is required for this gateway.");
        }

        if (empty($this->sharedSecret)) {
            throw new ConfigurationException("SharedSecret is required for this gateway.");
        }
    }
}
