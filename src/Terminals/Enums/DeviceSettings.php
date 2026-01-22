<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class DeviceSettings extends Enum
{
    public ?string $terminalId = null;
    public ?string $applicationId = null;
    public ?string $downloadType = null;
    public ?string $downloadTime = null;
    public ?string $hudsUrl = null;
    public ?string $hudsPort = null;
}
