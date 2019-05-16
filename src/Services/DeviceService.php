<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Terminals\ConnectionContainer;
use GlobalPayments\Api\Terminals\ConnectionConfig;

class DeviceService
{

    /**
     * Initiates a new object
     *
     * @param ServicesConfig $config Service config
     *
     * @return void
     */
    public static function create(ConnectionConfig $config)
    {
        ConnectionContainer::configure($config);
        return ConnectionContainer::instance()->device;
    }
}
