<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TimeZoneConversion;
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
    public function execute($configName = 'default')
    {
        parent::execute($configName);

        $client = ServicesContainer::instance()->getClient($configName);
        return $client->processReport($this);
    }
}
