<?php

namespace GlobalPayments\Api\ServiceConfigs;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\IWebProxy;

abstract class Configuration
{
    /** @var int */
    public int $timeout = 65000;

    /** @var Environment */
    public mixed $environment = Environment::TEST;

    /** @var IRequestLogger */
    public mixed $requestLogger = null;

    /** @var string */
    public ?string $serviceUrl = null;

    /** @var bool */
    public ?bool $validated = null;

    /**
     * @var IWebProxy
     */
    public mixed $webProxy = null;

    /** @var bool */
    public ?bool $enableLogging = null;

    /** @var bool */
    public ?bool $forceGatewayTimeout = null;

    /** @var array */
    public array $dynamicHeaders = [];

    abstract public function configureContainer(ConfiguredServices $services);

    public function validate()
    {
        $this->validated = true;
    }
}
