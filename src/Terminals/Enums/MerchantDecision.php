<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class MerchantDecision extends Enum
{
    const APPROVED = 'Approved';
    const FORCE_ONLINE = 'Force Online';
    const FORCE_DECLINE = 'Force Decline - AAC';
}