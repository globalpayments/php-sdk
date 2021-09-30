<?php


namespace GlobalPayments\Api\Entities\Enums;


use GlobalPayments\Api\Entities\Enum;

class DepositStatus extends Enum
{
    const FUNDED = 'FUNDED';
    const SPLIT_FUNDING = 'SPLIT_FUNDING';
    const DELAYED = 'DELAYED';
    const RESERVED = 'RESERVED';
    const IRREG = 'IRREG';
    const RELEASED = 'RELEASED';
}