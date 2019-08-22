<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class StoredCredentialType extends Enum
{
    const ONEOFF = 'oneoff';
    const INSTALLMENT = 'installment';
    const RECURRING = 'recurring';
}
