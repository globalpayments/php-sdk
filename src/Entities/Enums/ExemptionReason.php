<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ExemptionReason extends Enum
{
    const APPLY_EXEMPTION = 'APPLY_EXEMPTION';
    const EOS_CONTINUE = 'CONTINUE';
    const FORCE_SECURE = 'FORCE_SECURE';
    const BLOCK = 'BLOCK';
}