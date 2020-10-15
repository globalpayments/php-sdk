<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Gateways\Gateway;

class ServicesConfig
{
    /** @var GatewayProvider */
    public $gatewayProvider;

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

    public function getPayPlanEndpoint()
    {
        if (empty($this->secretApiKey) || strpos(strtolower($this->secretApiKey), 'cert') !== false) {
            return '/Portico.PayPlan.v2/';
        }
        return '/PayPlan.v2/';
    }

    // Realex
    public $accountId;
    public $merchantId;
    public $sharedSecret;
    public $rebatePassword;
    public $refundPassword;
    public $channel;
    public $hostedPaymentConfig;

    // GP Data Services
    public $dataClientId;
    public $dataClientSecret;
    public $dataClientUserId;
    public $dataClientServiceUrl;

    // Secure 3d
    /** @var string */
    public $challengeNotificationUrl;

    /** @var string */
    public $merchantContactUrl;

    /** @var string */
    public $methodNotificationUrl;

    /** @var Secure3dVersion */
    public $secure3dVersion;

    // Genius
    public $merchantName;
    public $merchantSiteId;
    public $merchantKey;
    public $registerNumber;
    public $terminalId;
    
    //TransIT
    public $transactionKey;
    public $manifest;
    public $acceptorConfig;

    // Common
    public $curlOptions;

    /** @var Environment */
    public $environment;
    public $serviceUrl;
    public $timeout;

    public function __construct()
    {
        $this->timeout = 65000;
        $this->environment = Environment::TEST;
        $this->gatewayProvider = GatewayProvider::PORTICO;
    }

    public function validate()
    {
        switch ($this->gatewayProvider) {
            case GatewayProvider::GP_ECOM:
                // realex
                if (empty($this->merchantId)) {
                    throw new ConfigurationException('merchantId is required for this configuration.');
                } elseif (empty($this->sharedSecret)) {
                    throw new ConfigurationException('sharedSecret is required for this configuration.');
                }
                break;
            case GatewayProvider::GENIUS:
                // Genius
                if (empty($this->merchantSiteId)) {
                    throw new ConfigurationException('merchantSiteId is required for this configuration.');
                } elseif (empty($this->merchantName)) {
                    throw new ConfigurationException('merchantName is required for this configuration.');
                } elseif (empty($this->merchantKey)) {
                    throw new ConfigurationException('merchantKey is required for this configuration.');
                }
                break;
            case GatewayProvider::PORTICO:
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
                break;
            case GatewayProvider::TRANSIT:
                // TransIT
                if (empty($this->deviceId)) {
                    throw new ConfigurationException('deviceID is required for this configuration.');
                }
                if (empty($this->acceptorConfig)) {
                    throw new ConfigurationException('You must provide a valid AcceptorConfig.');
                }
                break;
        }

        // // Service URL
        // if (empty($this->serviceUrl) && $this->secure3dVersion != null) {
        //     throw new ConfigurationException(
        //         "Service URL could not be determined from the credentials provided. Please specify an endpoint."
        //     );
        // }

        // secure 3d
        if ($this->secure3dVersion != null) {
            if ($this->secure3dVersion === Secure3dVersion::TWO || $this->secure3dVersion === Secure3dVersion::ANY) {
                if (empty($this->challengeNotificationUrl)) {
                    throw new ConfigurationException("The challenge notification URL is required for 3DS v2 processing.");
                }

                if (empty($this->methodNotificationUrl)) {
                    throw new ConfigurationException("The method notification URL is required for 3DS v2 processing.");
                }
            }
        }
    }
}
