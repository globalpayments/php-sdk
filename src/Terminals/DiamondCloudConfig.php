<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Region;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;

class DiamondCloudConfig extends ConnectionConfig
{
    public string $statusUrl;
    public string $isvID;
    public string $secretKey;
    /** @var Region $country */
    public string $region = Region::US;
    public string $posID;

    public function configureContainer(ConfiguredServices $services)
    {
        if (empty($this->serviceUrl)) {
            $this->serviceUrl = (($this->environment === Environment::PRODUCTION) ?
                ServiceEndpoints::DIAMOND_CLOUD_PROD : ServiceEndpoints::DIAMOND_CLOUD_TEST);
        }
        parent::configureContainer($services);
    }
}