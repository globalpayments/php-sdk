<?php

namespace GlobalPayments\Api\ServiceConfigs;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Environment;

abstract class Configuration
{
    /** @var int */
    public $timeout = 65000;

    /** @var Environment */
    public $environment = Environment::TEST;

    /** @var IRequestLogger */
    public $requestLogger;

    /** @var string */
    public $serviceUrl;

    /** @var bool */
    public $validated;

    abstract function configureContainer(ConfiguredServices $services);

    /** @var bool */
    public $enableLogging;

    /** @var bool */
    public $forceGatewayTimeout;

    public function validate() {
        $this->validated = true;
    }
}
