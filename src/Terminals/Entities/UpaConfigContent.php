<?php

namespace GlobalPayments\Api\Terminals\Entities;

use GlobalPayments\Api\Terminals\Enums\{
    DeviceConfigType,
    Reinitialize
};

class UpaConfigContent
{
    /** @var DeviceConfigType|null */
    public ?string $configType;

    public string $fileContents;

    /** @var int */
    public int $length;

    /** @var Reinitialize|null */
    public ?string $reinitialize;
}