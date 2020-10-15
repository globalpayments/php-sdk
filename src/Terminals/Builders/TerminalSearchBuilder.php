<?php

namespace GlobalPayments\Api\Terminals\Builders;

class TerminalSearchBuilder
{

    private $reportBuilder;
    
    public $TransactionType;
    public $CardType;
    public $RecordNumber;
    public $TerminalReferenceNumber;
    public $AuthCode;
    public $ReferenceNumber;
    public $MerchantId;
    public $MerchantName;

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
