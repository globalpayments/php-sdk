<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Entities\Reporting\TransactionList;
use GlobalPayments\Api\Terminals\Enums\SummaryType;

class SummaryResponse
{
    public ?float $amount;
    public ?float $amountDue;
    public ?float $authorizedAmount;
    public int $count;
    /** @var SummaryType */
    public string $summaryType;
    public ?float $totalAmount;
    public TransactionList $transactions;

    public function __construct()
    {
        $this->transactions = new TransactionList();
    }
}