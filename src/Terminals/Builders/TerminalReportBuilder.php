<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;
use GlobalPayments\Api\Terminals\Enums\TerminalReportType;
use GlobalPayments\Api\Terminals\TerminalResponse;

class TerminalReportBuilder extends TerminalBuilder
{
    /**
     * @internal
     * @var TerminalReportType
     */
    public mixed $reportType = null;

    /**
     * 
     * @var TerminalSearchBuilder
     */
    public ?TerminalSearchBuilder $searchBuilder = null;

    /**
     * @internal
     * @var TimeZoneConversion
     */
    public mixed $timeZoneConversion = null;

    /** @var string */
    public ?string $transactionId = null;

    /**
     * @param TerminalReportType $reportType
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
    public function execute($configName = "default") : ITerminalReport
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
