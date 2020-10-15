<?php

namespace GlobalPayments\Api\Terminals\PAX\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaxEntryMode extends Enum
{

    const MANUAL = "0";
    const SWIPE = "1";
    const CONTACTLESS = "2";
    const SCANNER = "3";
    const CHIP = "4";
    const CHIP_FALLBACK_SWIPE = "5";
}
