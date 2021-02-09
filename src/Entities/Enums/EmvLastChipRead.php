<?php


namespace GlobalPayments\Api\Entities\Enums;


class EmvLastChipRead
{
    const SUCCESSFUL = 'Successful';
    const FAILED = 'Failed';
    const NOT_A_CHIP_TRANSACTION = 'NotAChipTransaction';
    const UNKNOWN = 'Unknown';

    public static $emvLastChipRead = [
        self::SUCCESSFUL => [
            Target::GP_API => 'PREV_SUCCESS',
            Target::Portico => 'CHIP_FAILED_PREV_SUCCESS',
            Target::Transit => 'SUCCESSFUL'
        ],
        self::FAILED => [
            Target::GP_API => 'PREV_FAILED',
            Target::Portico => 'CHIP_FAILED_PREV_FAILED',
            Target::Transit => 'FAILED'
        ],
        self::NOT_A_CHIP_TRANSACTION => [
            Target::Transit => 'NOT_A_CHIP_TRANSACTION'
        ],
        self::UNKNOWN => [
            Target::Transit => 'UNKNOWN'
        ]
    ];
}