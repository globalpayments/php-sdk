<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class InitialPaymentMethod extends Enum
{
    const UNASSIGNED = 'Unassigned';
    const CARD = "Card";
    const OTHER = "Other";
}