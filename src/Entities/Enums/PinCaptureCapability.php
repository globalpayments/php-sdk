<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PinCaptureCapability extends Enum
{
    const FOUR_CHARACTERS   = '4';
    const FIVE_CHARACTERS   = '5';
    const SIX_CHARACTERS    = '6';
    const SEVEN_CHARACTERS  = '7';
    const EIGHT_CHARACTERS  = '8';
    const NINE_CHARACTERS   = '9';
    const TEN_CHARACTERS    = '10';
    const ELEVEN_CHARACTERS = '11';
    const TWELVE_CHARACTERS = '12';
    const UNKNOWN           = 'UNKNOWN';
    const NONE              = 'NOT_SUPPORTED';
}
