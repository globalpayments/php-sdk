<?php

namespace GlobalPayments\Api\Terminals;

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

    abstract public function send($message, $requestType = null);

    abstract public function processTransaction(TerminalAuthBuilder $builder) : TerminalResponse;

    abstract public function manageTransaction(TerminalManageBuilder $builder) : TerminalResponse;

    abstract public function processReport(TerminalReportBuilder $builder) : TerminalResponse;

    abstract public function configureInterface() : IDeviceInterface;
}
