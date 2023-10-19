<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Reporting\SearchCriteriaBuilder;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\TerminalResponse;

class TerminalReportBuilder extends TerminalBuilder
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
        $this->searchBuilder = new TerminalSearchBuilder($this);
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
        if (!isset($this->searchBuilder)) {
            $this->searchBuilder = new TerminalSearchBuilder($this);
        }

        return $this->searchBuilder->andCondition($criteria, $value);
    }

    protected function setupValidations()
    {
        // TODO: Implement setupValidations() method.
    }
}
