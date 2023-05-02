<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class MerchantAccountType extends Enum
{
    const TRANSACTION_PROCESSING = 'TRANSACTION_PROCESSING';
    const DATA_SERVICES = 'DATA_SERVICES';
    const DISPUTE_MANAGEMENT = 'DISPUTE_MANAGEMENT';
    const MERCHANT_MANAGEMENT = 'MERCHANT_MANAGEMENT';
    const TOKENIZATION = 'TOKENIZATION';
    const FUND_MANAGEMENT = 'FUND_MANAGEMENT';
}