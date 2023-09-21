<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class DocumentCategory extends Enum
{
    const IDENTITY_VERIFICATION = 'IDENTITY_VERIFICATION';
    const RISK_REVIEW = 'RISK_REVIEW';
    const UNDERWRITING = 'UNDERWRITING';
}