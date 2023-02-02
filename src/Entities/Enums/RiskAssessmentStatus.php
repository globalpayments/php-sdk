<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class RiskAssessmentStatus extends Enum
{
    const ACCEPTED = "ACCEPTED";
    const REJECTED = "REJECTED";
    const CHALLENGE = "CHALLENGE";
    const PENDING_REVIEW = "PENDING_REVIEW";
}