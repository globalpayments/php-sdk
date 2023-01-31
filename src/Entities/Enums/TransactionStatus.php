<?php

namespace GlobalPayments\Api\Entities\Enums;

class TransactionStatus
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

    static public $mapTransactionStatusResponse = [
        self::INITIATED => self::INITIATED,
        self::AUTHENTICATED => 'SUCCESS_AUTHENTICATED',
        self::PENDING => self::PENDING,
        self::DECLINED => self::DECLINED,
        self::PREAUTHORIZED => self::PREAUTHORIZED,
        self::CAPTURED => self::CAPTURED,
        self::BATCHED => self::BATCHED,
        self::REVERSED => self::REVERSED,
        self::FUNDED => self::FUNDED,
        self::REJECTED => self::REJECTED,
    ];
}
