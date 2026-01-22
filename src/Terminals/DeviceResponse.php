<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceResponse;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;

abstract class DeviceResponse implements IDeviceResponse, IBatchCloseResponse, ITerminalReport
{
    /** @var string */
    public ?string $status = null;
    /** @var string */
    public ?string $command = null;
    /** @var string */
    public ?string $version = null;
    /** @var string */
    public ?string $deviceResponseCode = null;
    /** @var string */
    public ?string $deviceResponseText = null;
    public ?string $referenceNumber = null;
}