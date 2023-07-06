<?php

namespace GlobalPayments\Api\Terminals\Abstractions;

use GlobalPayments\Api\ServiceConfigs\Gateways\GatewayConfig;
use GlobalPayments\Api\Terminals\Enums\BaudRate;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DataBits;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\Parity;
use GlobalPayments\Api\Terminals\Enums\StopBits;

interface ITerminalConfiguration
{
    public function getConnectionMode();
    public function setConnectionMode(ConnectionModes $connectionModes) : void;
    public function getDeviceType() : DeviceType;
    public function setDeviceType(DeviceType $deviceType) : void;
    public function getRequestIdProvider() : IRequestIdProvider;
    public function setRequestIdProvider(IRequestIdProvider $requestIdProvider) : void;

    public function getIpAddress() : string;
    public function setIpAddress(string $ipAddress) : void;
    public function getPort() : string;
    public function setPort(string $port) : void;

    public function getBaudRate() : BaudRate;
    public function setBaudRate(BaudRate $baudRate) : void;
    public function getParity() : Parity;
    public function setParity(Parity $parity) : void;
    public function getStopBits() : StopBits;
    public function setStopBits(StopBits $stopBits) : void;
    public function getDataBits() : DataBits;
    public function setDataBits(DataBits $dataBits) : void;

    public function getTimeout() : int;

    public function getGatewayConfig() : GatewayConfig;
    public function setGatewayConfig(GatewayConfig $gatewayConfig) : void;

    public function getConfigName() : string;
    public function setConfigName(string $configName) : void;
}