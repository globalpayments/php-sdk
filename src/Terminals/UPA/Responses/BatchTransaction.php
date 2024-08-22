<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

class BatchTransaction
{
    public string $cardType;
    public float $totalAmount;
    public ?int $totalCount;
    public ?int $creditCnt;
    public ?float $creditAmt;
    public ?int $debitCnt;
    public ?float $debitAmt;
    public ?int $saleCnt;
    public ?float $saleAmt;
    public ?int $returnCnt;
    public ?float $returnAmt;
    public ?float $totalGratuityAmt;
}