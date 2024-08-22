<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class UpaSAFType extends Enum
{
    const APPROVED = "AUTHORIZED TRANSACTIONS";
    const PENDING = "PENDING TRANSACTIONS";
    const DECLINED = "FAILED TRANSACTIONS";
}