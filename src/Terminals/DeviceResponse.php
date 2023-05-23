<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Abstractions\IDeviceResponse;

abstract class DeviceResponse implements IDeviceResponse
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
    /** @var string */
    public $referenceNumber;
}