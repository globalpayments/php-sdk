<?php

namespace GlobalPayments\Api\ServiceConfigs\Gateways;

use GlobalPayments\Api\ServiceConfigs\Configuration;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;

abstract class GatewayConfig extends Configuration
{
    /** @var AcceptorConfig */
    public $acceptorConfig;

    /** @var GatewayProvider */
    protected $gatewayProvider;

    /** @var string */
    public $dataClientId;

    /** @var string */
    public $dataClientSecret;

    /** @var string */
    public $dataClientUserId;

    /** @var string */
    public $dataClientSeviceUrl;

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
