<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities;

use GlobalPayments\Api\Terminals\Entities\PromptMessages;
use GlobalPayments\Api\Terminals\Enums\DisplayOption;

class SignatureData
{
    /** @var DisplayOption */
    public mixed $displayOption = null;

    public PromptMessages $prompts;
}