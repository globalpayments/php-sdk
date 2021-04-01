<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\GpApi\DepositSortProperty;
use GlobalPayments\Api\Entities\Enums\GpApi\DisputeSortProperty;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Enums\GpApi\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TimeZoneConversion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteriaBuilder;
use phpDocumentor\Parser\Exception;

class TransactionReportBuilder extends ReportBuilder
{
    /**
     * @internal
     * @var string
     */
    public $deviceId;

    /**
     * @internal
     * @var \DateTime
     */
    public $endDate;

    /**
     * @internal
     * @var \DateTime
     */
    public $startDate;

    /**
     * @internal
     * @var string
     */
    public $transactionId;

    /**
     * @var integer
     */
    public $page;

    /**
     * @var integer
     */
    public $pageSize;

    /**
     * @var TransactionSortProperty
     */
    public $transactionOrderBy;

    /**
     * @var SortDirection
     */
    public $transactionOrder;

    /**
     * @var DepositSortProperty
     */
    public $depositOrderBy;

    /**
     * @var SortDirection
     */
    public $depositOrder;

    /**
     * @var DisputeSortProperty
     */
    public $disputeOrderBy;

    /**
     * @var SortDirection
     */
    public $disputeOrder;

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
     * Sets the gateway deposit id as criteria for the report.
     * @param $depositId
     * @return $this
     */
    public function withDepositId($depositId)
    {
        $this->searchBuilder->depositId = $depositId;
        return $this;
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
     * Sets the gateway dispute id as criteria for the report.
     *
     * @param $disputeId
     * @return $this
     */
    public function withDisputeId($disputeId)
    {
        $this->searchBuilder->disputeId = $disputeId;
        return $this;
    }

    /**
     * Sets the end date as criteria for the report.
     *
     * @param \DateTime $value The end date
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
     * @param \DateTime $value The start date
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

    /**
     * Sets the gateway settlement dispute id as criteria for the report.
     *
     * @param $settlementDisputeId
     * @return $this
     */
    public function withSettlementDisputeId($settlementDisputeId)
    {
        $this->searchBuilder->settlementDisputeId = $settlementDisputeId;
        return $this;
    }

    /**
     * Set the gateway order for the criteria
     * @param string $sortProperty sorting property
     * @param string $sortDirection sorting direction
     * @return $this
     */
    public function orderBy($sortProperty, $sortDirection = SortDirection::DESC)
    {
        switch ($this->reportType) {
            case ReportType::FIND_TRANSACTIONS:
            case ReportType::FIND_TRANSACTIONS_PAGED:
            case ReportType::FIND_SETTLEMENT_TRANSACTIONS:
            case ReportType::FIND_SETTLEMENT_TRANSACTIONS_PAGED:
                $this->transactionOrderBy = $sortProperty;
                $this->transactionOrder = $sortDirection;
                break;
            case ReportType::FIND_DEPOSITS:
            case ReportType::FIND_DEPOSITS_PAGED:
                $this->depositOrderBy = $sortProperty;
                $this->depositOrder = $sortDirection;
            break;
            case ReportType::FIND_DISPUTES:
            case ReportType::FIND_DISPUTES_PAGED:
            case ReportType::FIND_SETTLEMENT_DISPUTES:
            case ReportType::FIND_SETTLEMENT_DISPUTES_PAGED:
                $this->disputeOrderBy = $sortProperty;
                $this->disputeOrder = $sortDirection;
                break;
            default:
                throw new \InvalidArgumentException("Invalid order found");
        }

        return $this;
    }

    /**
     * @return SearchCriteriaBuilder
     */
    public function where($criteria, $value)
    {
        return $this->searchBuilder->andWith($criteria, $value);
    }

    protected function setupValidations()
    {
        $this->validations->of(ReportType::TRANSACTION_DETAIL)
            ->check('transactionId')->isNotNull();

        $this->validations->of(ReportType::ACTIVITY)
            ->check('transactionId')->isNull();
    }
}
