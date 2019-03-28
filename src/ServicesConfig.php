<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;

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

    // Common
    public $curlOptions;
    public $serviceUrl;
    public $timeout;

    public function __construct()
    {
        $this->timeout = 65000;
    }

    public function validate()
    {
        // Portico API key
        if (!empty($this->secretApiKey)
            && (!empty($this->siteId)
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
        if ((!empty($this->siteId)
                || !empty($this->licenseId)
                || !empty($this->deviceId)
                || !empty($this->username)
                || !empty($this->password)
            )
            && (empty($this->siteId)
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
            && (empty($this->siteId)
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
        if (empty($this->serviceUrl)) {
            throw new ConfigurationException(
                "Service URL could not be determined form the credentials provided. Please specify an endpoint."
            );
        }
    }
}
