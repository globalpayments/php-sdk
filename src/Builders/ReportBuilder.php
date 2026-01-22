<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TimeZoneConversion;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Reporting\SearchCriteriaBuilder;
use GlobalPayments\Api\ServicesContainer;

abstract class ReportBuilder extends BaseBuilder
{
    /**
     * @internal
     * @var ReportType
     */
    public mixed $reportType = null;

    /**
     * @internal
     * @var TimeZoneConversion
     */
    public mixed $timeZoneConversion = null;


    /**
     * @var integer
     */
    public ?int $page = null;

    /**
     * @var integer
     */
    public ?int $pageSize = null;

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
        switch ($this->reportType) {
            case ReportType::FIND_BANK_PAYMENT:
                $client = ServicesContainer::instance()->getOpenBanking($configName);
                break;
            default:
                $client = ServicesContainer::instance()->getClient($configName);
                break;

        }

        return $client->processReport($this);
    }

    /**
     * @return SearchCriteriaBuilder
     */
    public function where($criteria, $value)
    {
        return $this->searchBuilder->andWith($criteria, $value);
    }

    /**
     * Set the gateway paging criteria for the report
     * @param $page
     * @param $pageSize
     * @return $this
     */
    public function withPaging($page, $pageSize)
    {
        $this->page = $page;
        $this->pageSize = $pageSize;
        return $this;
    }
}
