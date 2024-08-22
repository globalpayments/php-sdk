<?php

namespace GlobalPayments\Api\Terminals\Entities;

use GlobalPayments\Api\Terminals\Enums\InputAlignment;
use GlobalPayments\Api\Terminals\Enums\TextFormat;

class GenericData
{
    public ?PromptMessages $prompts = null;

    public ?string $textButton1 = null;

    public ?string $textButton2 = null;
    /** @var array<TextFormat> Indicates the format of the data to be entered. */
    public ?array $entryFormat = null;
    /** @var int Number of decimal places for numeric entry format. This is required if the entry format is
    numeric */
    public ?int $decimalPlaces = null;
    /** @var int Minimum length for the entry */
    public ?int $entryMinLen = null;

    /** @var string|InputAlignment Alignment when displaying the inputs. If this is not sent in the packet,
     * the default alignment is LR.
     */
    public ?string $alignment = null;
    /** @var int Maximum length for the entry. */
    public ?int $entryMaxLen = null;
    /** @var int Number of seconds that the message will be displayed. If this parameter is not received, the
    default timeout is the IdleTimeout value set through the settings. */
    public ?int $timeout = null;
}