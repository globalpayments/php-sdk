<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class DeviceSettings extends Enum
{
    public $terminalId;
    public $applicationId;
    public $downloadType;
    public $downloadTime;
    public $hudsUrl;
    public $hudsPort;
}
