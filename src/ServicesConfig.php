<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;

class ServicesConfig
{
    // Portico
    public $siteId;
    public $licenseId;
    public $deviceId;
    public $username;
    public $password;
    public $developerId;
    public $versionNumber;
    public $secretApiKey;

    // Realex
    public $accountId;
    public $merchantId;
    public $sharedSecret;
    public $rebatePassword;
    public $refundPassword;
    public $channel;
    public $hostedPaymentConfig;

    /**
     * @var string
     */
    public $challengeNotificationUrl;

    /**
     * @var string
     */
    public $merchantContactUrl;

    /**
     * @var string
     */
    public $methodNotificationUrl;

    /**
     * @var Secure3dVersion
     */
    public $secure3dVersion;

    // Common
    public $curlOptions;

    /**
     * @var Environment
     */

    public $environment;
    public $serviceUrl;
    public $timeout;

    public function __construct()
    {
        $this->timeout = 65000;
        $this->environment = Environment::TEST;
    }

    public function validate()
    {
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

        // Realex
        if ((empty($this->secretApiKey)
            && (
                empty($this->siteId)
                && empty($this->licenseId)
                && empty($this->deviceId)
                && empty($this->username)
                && empty($this->password)
            ))
            && empty($this->merchantId)
        ) {
            throw new ConfigurationException(
                "MerchantId should not be empty for this configuration."
            );
        }

        // Service URL
        if (empty($this->serviceUrl) && $this->secure3dVersion == null) {
            throw new ConfigurationException(
                "Service URL could not be determined from the credentials provided. Please specify an endpoint."
            );
        }

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
