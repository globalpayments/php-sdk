<?php

namespace GlobalPayments\Api\Terminals\Builders;

class TerminalSearchBuilder
{
    private $reportBuilder;

    /** @var string */
    public $transactionId;

    /** @var TransactionIdType */
    public $transactionIdType;

    /** @var TransactionType */
    public $transactionType;

    public $cardType;
    public $recordNumber;
    public $terminalReferenceNumber;
    public $authCode;
    public $referenceNumber;
    public $merchantId;
    public $merchantName;

    public string $ecrId;
    public string $reportOutput;
    public ?string $reportType;
    public string $batch;

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
