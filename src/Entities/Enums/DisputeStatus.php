<?php


namespace GlobalPayments\Api\Entities\Enums;


use GlobalPayments\Api\Entities\Enum;

class DisputeStatus extends Enum
{
    const UNDER_REVIEW = 'UNDER_REVIEW';
    const WITH_MERCHANT = 'WITH_MERCHANT';
    const CLOSED = 'CLOSED';
    const SETTLE_DISPUTE_FUNDED = 'FUNDED';
    const SETTLE_DISPUTE_DELAYED = 'DELAYED';
}
