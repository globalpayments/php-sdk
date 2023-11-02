<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Region;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;

class DiamondCloudConfig extends ConnectionConfig
{
    public string $statusUrl;
    public string $isvID;
    public string $secretKey;
    /** @var Region $country */
    public string $region = Region::US;
    public string $posID;
    private array $supportedRegions = [
        Region::EU,
        Region::US,
        Region::MX,
        Region::CL
    ];

    public function configureContainer(ConfiguredServices $services)
    {
        if (empty($this->serviceUrl)) {
            $this->serviceUrl = (($this->environment === Environment::PRODUCTION) ?
                ($this->region == Region::EU ?
                    ServiceEndpoints::DIAMOND_CLOUD_PROD_EU : ServiceEndpoints::DIAMOND_CLOUD_PROD
                ) : ServiceEndpoints::DIAMOND_CLOUD_TEST);
        }
        parent::configureContainer($services);
    }

    public function validate()
    {
        parent::validate();
        if (empty($this->isvID) || empty($this->secretKey)) {
            throw new ConfigurationException('ISV ID and secretKey is required for ' . ConnectionModes::DIAMOND_CLOUD);
        }
        if (!in_array($this->region, $this->supportedRegions)) {
            throw new ConfigurationException(sprintf('Region %s is not supported!'), $this->region);
        }
    }
}