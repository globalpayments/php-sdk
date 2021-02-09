<?php


namespace GlobalPayments\Api\Entities\Enums\GpApi;


use GlobalPayments\Api\Entities\Enum;

class TransactionSortProperty extends Enum
{
    const TIME_CREATED = 'TIME_CREATED';
    const STATUS = 'STATUS';
    const TYPE = 'TYPE';
    const DEPOSIT_ID = 'DEPOSIT_ID';
}