<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\ServiceConfigs\Configuration;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;

abstract class GatewayConfig extends Configuration
{
    /** @var AcceptorConfig */
    public mixed $acceptorConfig = null;

    /** @var GatewayProvider */
    public mixed $gatewayProvider = null;

    /** @var string */
    public ?string $dataClientId = null;

    /** @var string */
    public ?string $dataClientSecret = null;

    /** @var string */
    public ?string $dataClientUserId = null;

    /** @var string */
    public ?string $dataClientSeviceUrl = null;

    public function __construct(GatewayProvider $provider)
    {
        $this->gatewayProvider = $provider;
    }

    // public function configureContainer($services)
    // {
    //     // need to implement dataServicesConnector
    // }

    public function validate()
    {
        parent::validate();

        // data client validations go here when enabled
    }

    public function getGatewayProvider()
    {
        return $this->gatewayProvider;
    }
}
