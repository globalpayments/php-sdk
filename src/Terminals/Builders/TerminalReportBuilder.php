<?php

namespace GlobalPayments\Api\Terminals\Builders;

use GlobalPayments\Api\Terminals\ConnectionContainer;
use GlobalPayments\Api\Terminals\Builders\TerminalSearchBuilder;

class TerminalReportBuilder
{

    /**
     * @internal
     * @var ReportType
     */
    public $reportType;

    /**
     * @internal
     * @var TimeZoneConversion
     */
    public $timeZoneConversion;
    public $searchBuilder;

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
    public function execute()
    {
        return ConnectionContainer::instance()->
                        processReport($this);
    }

    public function where($criteria, $value)
    {
        if ($this->searchBuilder == null) {
            $this->searchBuilder = new TerminalSearchBuilder($this);
        }

        return $this->searchBuilder->andCondition($criteria, $value);
    }
}
