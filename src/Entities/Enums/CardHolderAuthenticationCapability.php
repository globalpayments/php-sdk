<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CardHolderAuthenticationCapability extends Enum
{
    const NO_CAPABILITY                             = 'NO_CAPABILITY';
    const PIN_ENTRY                                 = 'PIN_ENTRY';
    const SIGNATURE_ANALYSIS                        = 'SIGNATURE_ANALYSIS';
    const SIGNATURE_ANALYSIS_INOPERATIVE            = 'SIGNATURE_ANALYSIS_INOPERATIVE';
    const MPOS_SOFTWARE_BASED_PIN_ENTRY_CAPABILITY  = 'MPOS_SOFTWARE_BASED_PIN_ENTRY_CAPABILITY';
    const OTHER                                     = 'OTHER';
    const UNKNOWN                                   = 'UNKNOWN';
}
