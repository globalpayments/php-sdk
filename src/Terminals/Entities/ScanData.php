<?php

namespace GlobalPayments\Api\Terminals\Entities;

use GlobalPayments\Api\Terminals\Enums\DisplayOption;

class ScanData
{
    /** @var string The text displayed below the QR code preview pane on the screen. */
    public ?string $header;

    /** @var DisplayOption Display change after exiting the screen currently displayed*/
    public ?string $displayOption;

    /** @var int Number of seconds that the message will be displayed. */
    public ?int $timeout;

    public PromptMessages $prompts;
}