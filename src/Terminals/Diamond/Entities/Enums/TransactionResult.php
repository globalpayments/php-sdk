<?php

namespace GlobalPayments\Api\Terminals\Diamond\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TransactionResult extends Enum
{
    const ACCEPTED = '0';
    const REFUSED = '1';
    const NO_CONNECTION = '2';
    const CANCELED = '7';
}