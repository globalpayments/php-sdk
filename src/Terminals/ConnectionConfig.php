<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\ConfiguredServices;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\ServiceConfigs\Configuration;
use GlobalPayments\Api\ServiceConfigs\Gateways\GatewayConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalConfiguration;
use GlobalPayments\Api\Terminals\Diamond\DiamondController;
use GlobalPayments\Api\Terminals\Enums\BaudRate;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DataBits;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\Parity;
use GlobalPayments\Api\Terminals\Enums\StopBits;
use GlobalPayments\Api\Terminals\Genius\GeniusController;
use GlobalPayments\Api\Terminals\HPA\HpaController;
use GlobalPayments\Api\Terminals\Abstractions\IRequestIdProvider;
use GlobalPayments\Api\Terminals\PAX\PaxController;
use GlobalPayments\Api\Terminals\UPA\UpaController;

class ConnectionConfig extends Configuration implements ITerminalConfiguration
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

    /**
     * Used only for Genius devices that connect via "Meet In The Cloud"
     * 
     * @var Genius\ServiceConfigs\MitcConfig
     */
    public $meetInTheCloudConfig;
    
    /*
     * Implementation of IRequestIdProvider to generate request id for each transaction
     */
    /** @var IRequestIdProvider */
    public $requestIdProvider;
    
    /*
     * Implementation of ILogManagement to generate logs for each transaction
     */
    public $logManagementProvider;

    /** @var GatewayConfig */
    public $gatewayConfig;

    private $configName;

    public function setConfigName(string $configName) : void
    {
        $this->configName = $configName;
    }

    public function getConfigName() : string
    {
        return $this->configName;
    }

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
                break;
            case DeviceType::PAX_ARIES8:
            case DeviceType::PAX_A80:
            case DeviceType::PAX_A35:
            case DeviceType::PAX_A920:
            case DeviceType::PAX_A77:
            case DeviceType::NEXGO_N5:
                $services->setDeviceController(new DiamondController($this));
                break;
            case DeviceType::GENIUS_VERIFONE_P400:
                $services->setDeviceController(new GeniusController($this));
                break;
            default:
                break;
        }
    }

    public function validate()
    {
        if ($this->connectionMode === ConnectionModes::MEET_IN_THE_CLOUD) {
            if (empty($this->meetInTheCloudConfig) && empty($this->gatewayConfig)) {
                throw new ConfigurationException(
                    "meetInTheCloudConfig or gatewayConfig object is required for this connection method"
                );
            }

            return;
        }

        if ($this->connectionMode == ConnectionModes::HTTP ||
                $this->connectionMode == ConnectionModes::TCP_IP) {
            if (empty($this->ipAddress)) {
                throw new ConfigurationException(
                    "IpAddress is required for TCP or HTTP communication modes."
                );
            }

            if (empty($this->port)) {
                throw new ConfigurationException(
                    "Port is required for TCP or HTTP communication modes."
                );
            }
        }

        if ($this->deviceType == DeviceType::HPA_ISC250 &&
                empty($this->requestIdProvider)
        ) {
            throw new ConfigurationException(
                "Request id is mandatory for this transaction. IRequestIdProvider is not implemented"
            );
        }

        if ($this->connectionMode == ConnectionModes::DIAMOND_CLOUD) {
            if (empty($this->isvID) || empty($this->secretKey)) {
                throw new ConfigurationException('ISV ID and secretKey is required for ' . ConnectionModes::DIAMOND_CLOUD);
            }
        }
    }

    public function getConnectionMode()
    {
       return $this->connectionMode;
    }

    public function setConnectionMode(ConnectionModes $connectionModes): void
    {
        // TODO: Implement setConnectionMode() method.
    }

    public function getDeviceType(): DeviceType
    {
        // TODO: Implement getDeviceType() method.
    }

    public function setDeviceType(DeviceType $deviceType): void
    {
        // TODO: Implement setDeviceType() method.
    }

    public function getRequestIdProvider(): IRequestIdProvider
    {
        return $this->requestIdProvider;
    }

    public function setRequestIdProvider(IRequestIdProvider $requestIdProvider): void
    {
        // TODO: Implement setRequestIdProvider() method.
    }

    public function getIpAddress(): string
    {
        // TODO: Implement getIpAddress() method.
    }

    public function setIpAddress(string $ipAddress): void
    {
        // TODO: Implement setIpAddress() method.
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    public function getBaudRate(): BaudRate
    {
        // TODO: Implement getBaudRate() method.
    }

    public function setBaudRate(BaudRate $baudRate): void
    {
        // TODO: Implement setBaudRate() method.
    }

    public function getParity(): Parity
    {
        // TODO: Implement getParity() method.
    }

    public function setParity(Parity $parity): void
    {
        // TODO: Implement setParity() method.
    }

    public function getStopBits(): StopBits
    {
        // TODO: Implement getStopBits() method.
    }

    public function setStopBits(StopBits $stopBits): void
    {
        // TODO: Implement setStopBits() method.
    }

    public function getDataBits(): DataBits
    {
        // TODO: Implement getDataBits() method.
    }

    public function setDataBits(DataBits $dataBits): void
    {
        // TODO: Implement setDataBits() method.
    }

    public function getTimeout(): int
    {
        // TODO: Implement getTimeout() method.
    }

    public function getGatewayConfig(): GatewayConfig
    {
        return $this->gatewayConfig;
    }

    public function setGatewayConfig(GatewayConfig $gatewayConfig): void
    {
        $this->gatewayConfig = $gatewayConfig;
    }
}
