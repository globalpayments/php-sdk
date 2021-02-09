<?php


namespace GlobalPayments\Api\Entities\Enums\GpApi;


use GlobalPayments\Api\Entities\Enum;

class DisputeStatus extends Enum
{
    const UNDER_REVIEW = 'UNDER_REVIEW';
    const WITH_MERCHANT = 'WITH_MERCHANT';
    const CLOSED = 'CLOSED';
}
