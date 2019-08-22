<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class SdkInterface extends Enum
{
    const NATIVE = 'NATIVE';
    const BROWSER = 'BROWSER';
    const BOTH = 'BOTH';
}
