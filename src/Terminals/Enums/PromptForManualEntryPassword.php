<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class PromptForManualEntryPassword extends Enum
{
    const DONT_PROMPT = "0";
    const WILL_PROMPT = "1";
}