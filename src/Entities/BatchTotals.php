<?php

namespace GlobalPayments\Api\Entities;

class BatchTotals
{
    public ?int $salesCount;
    public ?string $saleAmount;
    public ?int $refundsCount;
    public ?string $refundsAmount;
    public ?int $fundingDebitCount;
    public ?string $fundingDebitAmount;
    public ?int $fundingCreditCount;
    public ?string $fundingCreditAmount;
    public ?string $totalGratuityAmt;

    public ?string $totalAmount;
    public ?string $totalCount;
}