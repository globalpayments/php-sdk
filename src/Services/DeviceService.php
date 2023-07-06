<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;

class DeviceService
{
    /**
     * Initiates a new object
     *
     * @param ConnectionConfig $config
     * @param string $configName
     *
     * @return IDeviceInterface
     * @throws \GlobalPayments\Api\Entities\Exceptions\ApiException
     */
    public static function create(ConnectionConfig $config, string $configName = "default") : IDeviceInterface
    {
        ServicesContainer::configureService($config, $configName);
        if (!empty($config->gatewayConfig)) {
            $config->setConfigName($configName);
            ServicesContainer::configureService($config->gatewayConfig, $configName);
        }
        return ServicesContainer::instance()->getDeviceInterface($configName);
    }
}
