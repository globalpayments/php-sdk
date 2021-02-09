<?php


namespace GlobalPayments\Api\Entities\Enums\GpApi;


use GlobalPayments\Api\Entities\Enum;

class TransactionStatus extends Enum
{
    const INITIATED = 'INITIATED';
    const AUTHENTICATED = 'AUTHENTICATED';
    const PENDING = 'PENDING';
    const DECLINED = 'DECLINED';
    const PREAUTHORIZED = 'PREAUTHORIZED';
    const CAPTURED = 'CAPTURED';
    const BATCHED = 'BATCHED';
    const REVERSED = 'REVERSED';
    const FUNDED = 'FUNDED';
    const REJECTED = 'REJECTED';
}