<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class DisplayOption extends Enum
{
    const NO_SCREEN_CHANGE = 0;
    const RETURN_TO_IDLE_SCREEN = 1;
}