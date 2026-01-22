<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Entities\Enums\ShaHashType;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Gateways\Gp3DSProvider;
use GlobalPayments\Api\Gateways\GpEcomConnector;
use GlobalPayments\Api\Gateways\OpenBankingProvider;
use GlobalPayments\Api\ConfiguredServices;

class GpEcomConfig extends GatewayConfig
{
    public ?string $accountId = null;
    public ?string $merchantId = null;
    public ?string $rebatePassword = null;
    public ?string $refundPassword = null;
    public ?string $sharedSecret = null;
    public ?string $channel = null;
    public mixed $hostedPaymentConfig = null;
    /** @var ShaHashType */
    public mixed $shaHashType = ShaHashType::SHA1;

    // Secure 3D
    public ?string $challengeNotificationUrl = null;
    public ?string $methodNotificationUrl = null;
    public ?string $merchantContactUrl = null;
    public ?string $merchantNotificationUrl = null;
    public mixed $secure3dVersion = null;

    /**
     * @deprecated  Property not used and it will be removed
     */
    //Open banking service
    public bool $enableBankPayment = false;

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

        $gateway = new GpEcomConnector($this);
        $gateway->timeout = $this->timeout;
        $gateway->hostedPaymentConfig = $this->hostedPaymentConfig;

        $gateway->serviceUrl = $this->serviceUrl;
        $gateway->requestLogger = $this->requestLogger;
        $gateway->webProxy = $this->webProxy;
        $gateway->environment = $this->environment;

        $services->gatewayConnector = $gateway;
        $services->recurringConnector = $gateway;

        if (empty($this->secure3dVersion)) {
            $this->secure3dVersion = Secure3dVersion::TWO;
        }

        if ($this->secure3dVersion == Secure3dVersion::ONE ||
            $this->secure3dVersion == Secure3dVersion::ANY) {
            $services->setSecure3dProvider(Secure3dVersion::ONE, $gateway);
        }

        if ($this->secure3dVersion == Secure3dVersion::TWO || $this->secure3dVersion == Secure3dVersion::ANY) {
            $secure3d2 = new Gp3DSProvider();
            $secure3d2->setMerchantId($this->merchantId);
            $secure3d2->setAccountId($this->accountId);
            $secure3d2->setSharedSecret($this->sharedSecret);
            $secure3d2->serviceUrl = $this->environment == Environment::TEST ? ServiceEndpoints::THREE_DS_AUTH_TEST : ServiceEndpoints::THREE_DS_AUTH_PRODUCTION;
            $secure3d2->setMerchantContactUrl($this->merchantContactUrl);
            $secure3d2->setMethodNotificationUrl($this->methodNotificationUrl);
            $secure3d2->setChallengeNotificationUrl($this->challengeNotificationUrl);
            $secure3d2->timeout = $gateway->timeout;
            $secure3d2->requestLogger = $this->requestLogger;
            $secure3d2->webProxy = $this->webProxy;
            $secure3d2->environment = $this->environment;

            $services->setSecure3dProvider(Secure3dVersion::TWO, $secure3d2);
        }
        if ($gateway->supportsOpenBanking()) {
            $openBanking = new OpenBankingProvider();
            $openBanking->merchantId = $this->merchantId;
            $openBanking->accountId = $this->accountId;
            $openBanking->sharedSecret = $this->sharedSecret;
            $openBanking->shaHashType = $this->shaHashType;
            $openBanking->serviceUrl = $this->environment === Environment::PRODUCTION ?
                ServiceEndpoints::OPEN_BANKING_PRODUCTION : ServiceEndpoints::OPEN_BANKING_TEST;
            $openBanking->timeout = $gateway->timeout;
            $openBanking->requestLogger = $this->requestLogger;
            $openBanking->webProxy = $this->webProxy;
            $openBanking->environment = $this->environment;
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
