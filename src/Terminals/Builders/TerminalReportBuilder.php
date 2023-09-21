<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\TerminalResponse;

class TerminalReportBuilder
{
    /**
     * @internal
     * @var ReportType
     */
    public $reportType;

    /**
     * 
     * @var TerminalSearchBuilder
     */
    public $searchBuilder;

    /**
     * @internal
     * @var TimeZoneConversion
     */
    public $timeZoneConversion;
    
    /** @var string */
    public $transactionId;

    /**
     * @param ReportType $reportType
     *
     * @return
     */
    public function __construct($reportType)
    {
        $this->reportType = $reportType;
    }

    /**
     * {@inheritdoc}
     *
     * @return Transaction
     */
    public function execute($configName = "default") : TerminalResponse
    {
        $client = ServicesContainer::instance()->getDeviceController($configName);
        return $client->processReport($this);
    }

    public function where($criteria, $value)
    {
        if ($this->searchBuilder == null) {
            $this->searchBuilder = new TerminalSearchBuilder($this);
        }

        return $this->searchBuilder->andCondition($criteria, $value);
    }
}
