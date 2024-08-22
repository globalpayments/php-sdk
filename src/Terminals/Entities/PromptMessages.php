<?php

namespace GlobalPayments\Api\Terminals\Entities;

class PromptMessages
{
    /** @var string Prompt for 1st line */
    public ?string $prompt1 = null;
    /** @var string|null Prompt for 2nd line */
    public ?string $prompt2 = null;
    /** @var string|null Prompt for 3rd line */
    public ?string $prompt3 = null;
}