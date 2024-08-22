<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities;

use GlobalPayments\Api\Terminals\Entities\PromptMessages;
use GlobalPayments\Api\Terminals\Enums\DisplayOption;

class SignatureData
{
    /** @var DisplayOption */
    public $displayOption;

    public PromptMessages $prompts;
}