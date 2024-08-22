<?php

namespace GlobalPayments\Api\Terminals\Entities;

use GlobalPayments\Api\Terminals\Enums\DisplayOption;

class PrintData
{
    /** @var string */
    public string $filePath;
    /** @var string Message to be displayed in Line 1. If blank, Line 1 will be displayed as a blank line. */
    public string $line1;
    /** @var string|null Message to be displayed in Line 2. */
    public ?string $line2;
    /** @var DisplayOption|null Display change after exiting the screen currently displayed */
    public ?string $displayOption;
}