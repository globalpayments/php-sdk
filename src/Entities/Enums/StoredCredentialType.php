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
    const ADD_PAYMENT_METHOD = 'ADD_PAYMENT_METHOD';
    const SPLIT_OR_DELAYED_SHIPMENT = 'SPLIT_OR_DELAYED_SHIPMENT';
    const TOP_UP = 'TOP_UP';
    const MAIL_ORDER = 'MAIL_ORDER';
    const TELEPHONE_ORDER = 'TELEPHONE_ORDER';
    const WHITELIST_STATUS_CHECK = 'WHITELIST_STATUS_CHECK';
    const OTHER_PAYMENT = 'OTHER_PAYMENT';
    const BILLING_AGREEMENT = 'BILLING_AGREEMENT';
}
