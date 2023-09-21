<?php

namespace GlobalPayments\Api\Terminals\Genius\ServiceConfigs;

use GlobalPayments\Api\Entities\Enums\Environment;

/**
 * Note that there are 6 unique values that are required for processing via
 * "Meet In The Cloud". These are made requirements by way of Constructor 
 * Arguments
 * 
 */
class MitcConfig
{
    // 6 unique values provided during onboarding:
    public $xWebId;
    public $terminalId;
    public $authKey;
    public $apiSecret;
    public $apiKey;
    public $targetDevice;

    /**
     * 
     * @var Environment|string
     */
    public $environment = Environment::PRODUCTION;

    /**
     * Required
     * 
     * Name given to the integration by the integrator.
     * Will default to 'PHP-SDK' if none is provided.
     * 
     * @var string
     */
    public $appName = "PHP SDK";

    /**
     * Optional
     * 
     * Version number given to the integration by the integrator
     * 
     * @var string
     */
    public $appVersion = "";

    /**
     * Optional
     * 
     * Version-4 UUID you generate to identify each request you send. 
     * Add a prefix of MER- to your ID. For example: 
     * MER-ba96b9c5-828c-434c-be74-d73c8e853526
     * 
     * Note: If you don’t send a value for this parameter, we generate a value with a prefix of API- and return it in the header of the response.
     * 
     * @var string
     */
    public $requestId = "";

    /** 
     * Required
     * 
     * Currently supported regions:
     * US - United States
     * CA - Canada
     * AU - Australia
     * NZ - New Zealand
     * 
     * @var string
     */
    public $region = "US";

    /**
     * Optional
     * 
     * 'true' will allow card number entry on device
     * 
     * @var bool
     */
    public $allowKeyEntry;

    public function __construct(
        string $xWebId,
        string $terminalId,
        string $authKey,
        string $apiSecret,
        string $apiKey,
        string $targetDevice
    )
    {
        $this->xWebId = $xWebId;
        $this->terminalId = $terminalId;
        $this->authKey = $authKey;
        $this->apiSecret = $apiSecret;
        $this->apiKey = $apiKey;
        $this->targetDevice = $targetDevice;
    }
}
