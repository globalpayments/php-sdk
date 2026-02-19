<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;
/**
 * Enum for HPP installments funding modes
 * goes in installment.funding_mode
 */
class InstallmentsFundingMode extends Enum
{
    const MERCHANT_FUNDED = 'MERCHANT_FUNDED';
    const CONSUMER_FUNDED = 'CONSUMER_FUNDED';
    const HYBRID_FUNDED = 'HYBRID_FUNDED';
    const BILATERAL = 'BILATERAL';
    const ANY = 'ANY';
}