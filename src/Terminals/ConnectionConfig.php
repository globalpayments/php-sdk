<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\ServiceConfigs\Configuration;
use GlobalPayments\Api\Terminals\Enums\BaudRate;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DataBits;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\Parity;
use GlobalPayments\Api\Terminals\Enums\StopBits;
use GlobalPayments\Api\Terminals\HPA\HpaController;
use GlobalPayments\Api\Terminals\Abstractions\IRequestIdProvider;
use GlobalPayments\Api\Terminals\PAX\PaxController;
use GlobalPayments\Api\Terminals\UPA\UpaController;

class ConnectionConfig extends Configuration
{
    /** @var DeviceType */
    public $deviceType;

    /** @var ConnectionModes */
    public $connectionMode;

    /** @var BaudRate */
    public $baudRate;

    /** @var Parity */
    public $parity;

    /** @var StopBits */
    public $stopBits;

    /** @var DataBits */
    public $dataBits;

    /** @var string */
    public $ipAddress;

    /** @var string */
    public $port;
    
    public $timeout;
    
    /*
     * Implementation of IRequestIdProvider to generate request id for each transaction
     */
    /** @var IRequestIdProvider */
    public $requestIdProvider;
    
    /*
     * Implementation of ILogManagement to generate logs for each transaction
     */
    public $logManagementProvider;

    public function configureContainer(ConfiguredServices $services)
    {
        switch ($this->deviceType) {
            case DeviceType::PAX_DEVICE:
            case DeviceType::PAX_S300:
            case DeviceType::PAX_D200:
            case DeviceType::PAX_D210:
            case DeviceType::PAX_PX5:
            case DeviceType::PAX_PX7:
                $services->setDeviceController(new PaxController($this));
                break;
            case DeviceType::HPA_ISC250:
                $services->setDeviceController(new HpaController($this));
                break;
            case DeviceType::UPA_DEVICE:
            case DeviceType::UPA_SATURN_1000:
            case DeviceType::UPA_VERIFONE_T650P:
                $services->setDeviceController(new UpaController($this));
            default:
                break;
        }
    }

    public function validate()
    {
        if ($this->connectionMode == ConnectionModes::HTTP ||
                $this->connectionMode == ConnectionModes::TCP_IP) {
            if (empty($this->ipAddress)) {
                throw new ConfigurationException(
                    "IpAddress is required for TCP or HTTP communication modes."
                );
            }
        }

        if (empty($this->port)) {
            throw new ConfigurationException(
                "Port is required for TCP or HTTP communication modes."
            );
        }
        
        if ($this->deviceType == DeviceType::HPA_ISC250 &&
                empty($this->requestIdProvider)
        ) {
            throw new ConfigurationException(
                "Request id is mandatory for this transaction. IRequestIdProvider is not implemented"
            );
        }
    }
}
