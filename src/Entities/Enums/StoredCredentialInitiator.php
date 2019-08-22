<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class StoredCredentialInitiator extends Enum
{
    const CARDHOLDER = 'cardholder';
    const MERCHANT = 'merchant';
    const SCHEDULED = 'scheduled';
}
