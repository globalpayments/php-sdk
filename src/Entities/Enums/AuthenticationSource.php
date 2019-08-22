<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class AuthenticationSource extends Enum
{
    const BROWSER = "BROWSER";
    const STORED_RECURRING = "STORED_RECURRING";
    const MOBILE_SDK = "MOBILE_SDK";
}
