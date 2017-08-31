<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\ServicesContainer;

abstract class ReportBuilder extends BaseBuilder
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

    /**
     * @param ReportType $reportType
     *
     * @return
     */
    public function __construct($reportType)
    {
        parent::__construct();
        $this->reportType = $reportType;
    }

    /**
     * Executes the builder against the gateway.
     *
     * @return mixed
     */
    public function execute()
    {
        parent::execute();

        $client = ServicesContainer::instance()->getClient();
        return $client->processReport($this);
    }
}
