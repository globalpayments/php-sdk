<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Abstractions\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalConfiguration;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\Abstractions\IRequestIdProvider;

abstract class DeviceController
{
    /** @var IDeviceInterface */
    public $deviceInterface;

    /** @var IRequestIdProvider */
    public $requestIdProvider;

    /** @var IDeviceCommInterface */
    public $connector;

    /** @var ITerminalConfiguration */
    public $settings;

    public function __construct(ITerminalConfiguration $settings)
    {
        $this->settings = $settings;
        $this->connector = $this->configureConnector();
    }

    public function send(DeviceMessage $message, $requestType = null)
    {
        $message->awaitResponse = true;
        if (!empty($this->connector)) {
            return $this->connector->send($message);
        }
    }

    abstract public function processTransaction(TerminalAuthBuilder $builder) : TerminalResponse;

    abstract public function manageTransaction(TerminalManageBuilder $builder) : TerminalResponse;

    abstract public function processReport(TerminalReportBuilder $builder) : TerminalResponse;

    abstract public function configureInterface() : IDeviceInterface;

    abstract public function configureConnector() : IDeviceCommInterface;
}
