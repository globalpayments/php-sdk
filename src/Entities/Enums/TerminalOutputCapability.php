<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TerminalOutputCapability extends Enum
{
    const NONE              = 'NONE';
    const PRINT_ONLY        = 'PRINT_ONLY';
    const DISPLAY_ONLY      = 'DISPLAY_ONLY';
    const PRINT_AND_DISPLAY = 'PRINT_AND_DISPLAY';
    const UNKNOWN           = 'UNKNOWN';
}
