<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class BlockCardType extends Enum
{
    const CONSUMER_CREDIT = 'consumercredit';
    const CONSUMER_DEBIT = 'consumerdebit';
    const COMMERCIAL_DEBIT = 'commercialdebit';
    const COMMERCIAL_CREDIT = 'commercialcredit';
}