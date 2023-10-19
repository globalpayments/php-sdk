<?php

namespace GlobalPayments\Api\Terminals\Diamond\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CardSource extends Enum
{
    const CONTACTLESS = 'B';
    const MANUAL = 'M';
    const MAGSTRIPE = 'C';
    const ICC = 'P';
    const UNKNOWN = '?';
}