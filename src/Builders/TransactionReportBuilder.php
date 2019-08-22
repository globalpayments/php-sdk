<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteriaBuilder;

class TransactionReportBuilder extends ReportBuilder
{
    /**
     * @internal
     * @var string
     */
    public $deviceId;

    /**
     * @internal
     * @var DateTime
     */
    public $endDate;

    /**
     * @internal
     * @var DateTime
     */
    public $startDate;

    /**
     * @internal
     * @var string
     */
    public $transactionId;
    
    /**
     * @internal
     * @var string
     */
    public $transactionType;
    
    /**
     * @internal
     * @var SearchCriteriaBuilder
     */
    public $searchBuilder;
    
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
    
    public function __construct($activity)
    {
        parent::__construct($activity);
        
        $this->searchBuilder = new SearchCriteriaBuilder($this);
    }
    
    /**
     * Sets the device ID as criteria for the report.
     *
     * @param string $value The device ID
     *
     * @return TransactionReportBuilder
     */
    public function withDeviceId($value)
    {
        $this->searchBuilder->deviceId = $value;
        return $this;
    }

    /**
     * Sets the end date as criteria for the report.
     *
     * @param DateTime $value The end date
     *
     * @return TransactionReportBuilder
     */
    public function withEndDate($value)
    {
        $this->searchBuilder->endDate = $value;
        return $this;
    }

    /**
     * Sets the start date as criteria for the report.
     *
     * @param DateTime $value The start date
     *
     * @return TransactionReportBuilder
     */
    public function withStartDate($value)
    {
        $this->searchBuilder->startDate = $value;
        return $this;
    }

    /**
     * Sets the timezone conversion method for the report.
     *
     * @param TimeZoneConversion $value The timezone conversion method
     *
     * @return TransactionReportBuilder
     */
    public function withTimeZoneConversion($value)
    {
        $this->timeZoneConversion = $value;
        return $this;
    }

    /**
     * Sets the transaction ID as criteria for the report.
     *
     * @param string $value The transaction ID
     *
     * @return TransactionReportBuilder
     */
    public function withTransactionId($value)
    {
        $this->transactionId = $value;
        return $this;
    }
    
    /**
     * @return SearchCriteriaBuilder
     */
    public function where($criteria, $value)
    {
        return $this->searchBuilder->and($criteria, $value);
    }
        
    protected function setupValidations()
    {
        $this->validations->of(ReportType::TRANSACTION_DETAIL)
            ->check('transactionId')->isNotNull();
            
        $this->validations->of(ReportType::ACTIVITY)
            ->check('transactionId')->isNull();
    }
}
