<?php

namespace GlobalPayments\Api\Terminals\Builders;

class TerminalSearchBuilder
{
    private mixed $reportBuilder = null;

    /** @var string */
    public ?string $transactionId = null;

    /** @var TransactionIdType */
    public mixed $transactionIdType = null;

    /** @var TransactionType */
    public mixed $transactionType = null;

    public ?string $cardType = null;
    public ?string $recordNumber = null;
    public ?string $terminalReferenceNumber = null;
    public ?string $authCode = null;
    public ?string $referenceNumber = null;
    public ?string $merchantId = null;
    public ?string $merchantName = null;

    public ?string $ecrId = null;
    public ?string $reportOutput = null;
    public ?string $reportType = null;
    public ?string $batch = null;

    public function __construct($reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    public function execute()
    {
        return $this->reportBuilder->execute();
    }
    
    public function andCondition($criteria, $value)
    {
        if (property_exists($this, $criteria)) {
            $this->{$criteria} = $value;
        }
        return $this;
    }
}
