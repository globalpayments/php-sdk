<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class AgeIndicator extends Enum
{
    const NO_ACCOUNT = 'NO_ACCOUNT';
    const NO_CHANGE = 'NO_CHANGE';
    const THIS_TRANSACTION = 'THIS_TRANSACTION';
    const LESS_THAN_THIRTY_DAYS = 'LESS_THAN_THIRTY_DAYS';
    const THIRTY_TO_SIXTY_DAYS = 'THIRTY_TO_SIXTY_DAYS';
    const MORE_THAN_SIXTY_DAYS = 'MORE_THAN_SIXTY_DAYS';
}
