<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceResponse;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;

abstract class DeviceResponse implements IDeviceResponse, IBatchCloseResponse, ITerminalReport
{
    /** @var string */
    public $status;
    /** @var string */
    public $command;
    /** @var string */
    public $version;
    /** @var string */
    public $deviceResponseCode;
    /** @var string */
    public $deviceResponseText;
    public ?string $referenceNumber;
}