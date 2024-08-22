<?php

namespace GlobalPayments\Api\Terminals\Entities;

class MessageLines
{
    public ?string $line1 = null;
    public ?string $line2 = null;
    public ?string $line3 = null;
    public ?string $line4 = null;
    public ?string $line5 = null;
    /** @var int|null Number of seconds that the message will be displayed. If this parameter is not received, the default
    timeout is the IdleTimeout value set through the settings. */
    public ?int $timeout = null;
}