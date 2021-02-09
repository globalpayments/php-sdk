<?php


namespace GlobalPayments\Api\Entities\Enums;


class EmvFallbackCondition
{
    const CHIP_READ_FAILURE = 'ChipReadFailure';
    const NO_CANDIDATE_LIST = 'NoCandidateList';

    public static $emvFallbackCondition = [
        self::CHIP_READ_FAILURE => [
            Target::Transit => 'ICC_TERMINAL_ERROR'
        ],
        self::NO_CANDIDATE_LIST => [
            Target::Transit => 'NO_CANDIDATE_LIST'
        ]
    ];
}