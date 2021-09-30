<?php


namespace GlobalPayments\Api\Entities\Enums;


class EmvLastChipRead
{
    const SUCCESSFUL = 'Successful';
    const FAILED = 'Failed';
    const NOT_A_CHIP_TRANSACTION = 'NotAChipTransaction';
    const UNKNOWN = 'Unknown';
}