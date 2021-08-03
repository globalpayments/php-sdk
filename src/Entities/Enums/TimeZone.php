<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TimeZone extends Enum
{
    const UTC = "UTC"; //Universal Time Coordinated
    const PT = "PT"; //Pacific Time (US) (UTC-8/7DST)
    const MST = "MST"; //Arizona (Mountain Standard Time[US]) (UTC-7)
    const MT = 'MT'; //Mountain Time (US) (UTC-7/6DST)
    const CT = 'CT'; //Central Time (US) (UTC-6/5DST)
    const ET = 'ET'; //Eastern Time (US) (UTC-5/4DST)
    const HST = 'HST'; //Hawaii Standard Time (UTC-10)
    const AT = 'AT'; //Atlantic Time (UTC-4/3DST)
    const AST = 'AST'; //Puerto Rico (Atlantic Standard Time)(UTC-4)
    const AKST = 'AKST'; //Alaska (Alaska Standard Time) (UTC-9)
}
