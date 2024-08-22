<?php

namespace GlobalPayments\Api\Terminals\Entities;

class PromptData
{
    /** @var PromptMessages Prompt messages */
    public PromptMessages $prompts;
    /** @var PromptButtons Prompt buttons */
    public PromptButtons $buttons;
    /** @var int Number of seconds that the message will be displayed. If this parameter is not received,
     * the default timeout is the IdleTimeout value set through the settings.
     */
    public int $timeout = 0;
    /** @var array Array of text which will be displayed in Menu 1 to Menu 6. This should contain at least 2 items
     * and a maximum of 6 items.
     */
    public ?array $menu = null;
}