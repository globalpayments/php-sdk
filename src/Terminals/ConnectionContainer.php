<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\HPA\HpaController;
use GlobalPayments\Api\Terminals\PAX\PaxController;

class ConnectionContainer
{

    /** @var IPaymentGateway */
    private $deviceController;

    /** @var ConnectionContainer */
    private static $instance;

    /**
     * ConnectionContainer constructor.
     *
     * @param object $deviceController
     *
     * @return
     */
    public function __construct($deviceController)
    {
        $this->deviceController = $deviceController;
    }

    /**
     * Gets the current `ConnectionContainer` instance
     *
     * @return ConnectionContainer
     */
    public static function instance()
    {
        if (static::$instance == null) {
            static::$instance = new static(null);
        }
        return static::$instance;
    }

    /**
     * Configures the `ConnectionContainer` singleton
     *
     * @param ServicesConfig $config
     *
     * @return void
     */
    public static function configure(ConnectionConfig $config)
    {
        $config->validate();

        $deviceController = null;
        switch ($config->deviceType) {
            case DeviceType::HPA_ISC250:
                static::$instance = new HpaController($config);
                break;
            case DeviceType::PAX_S300:
            case DeviceType::PAX_D200:
            case DeviceType::PAX_D210:
            case DeviceType::PAX_PX5:
            case DeviceType::PAX_PX7:
                static::$instance = new PaxController($config);
                break;
        }
    }
}
