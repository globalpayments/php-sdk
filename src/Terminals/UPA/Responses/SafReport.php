<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Terminals\Enums\SummaryType;
use GlobalPayments\Api\Terminals\SummaryResponse;

class SafReport
{
    public int $totalCount;
    public ?float $totalAmount;
    /** @var array<SummaryType, SummaryResponse> */
    public array $approved = [];
    /** @var array<SummaryType, SummaryResponse> */
    public array $pending = [];
    /** @var array<SummaryType, SummaryResponse> */
    public array $decline = [];
}