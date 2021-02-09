<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class StoredCredentialType extends Enum
{
    const ONEOFF = 'oneoff';
    const INSTALLMENT = 'installment';
    const RECURRING = 'recurring';
    const UNSCHEDULED = 'UNSCHEDULED';
    const SUBSCRIPTION = 'SUBSCRIPTION';
    const MAINTAIN_PAYMENT_METHOD = 'MAINTAIN_PAYMENT_METHOD';
    const MAINTAIN_PAYMENT_VERIFICATION = 'MAINTAIN_PAYMENT_VERIFICATION';
}
