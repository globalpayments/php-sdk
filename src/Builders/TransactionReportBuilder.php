<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\ActionSortProperty;
use GlobalPayments\Api\Entities\Enums\DepositSortProperty;
use GlobalPayments\Api\Entities\Enums\DisputeSortProperty;
use GlobalPayments\Api\Entities\Enums\PayLinkSortProperty;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\StoredPaymentMethodSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TimeZoneConversion;
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
    public $clientTransactionId;

    /**
     * @internal
     * @var string
     */
    public $transactionId;

    /**
     * @internal
     * @var string
     */
    public $paymentType;

    /**
     * @var TransactionSortProperty
     */
    public $transactionOrderBy;

    /**
     * @var DepositSortProperty
     */
    public $depositOrderBy;

    /**
     * @var DisputeSortProperty
     */
    public $disputeOrderBy;

    /**
     * @var  StoredPaymentMethodSortProperty
     */
    public $storedPaymentMethodOrderBy;

    /**
     * @var  ActionSortProperty
     */
    public $actionOrderBy;

    /** @var PayLinkSortProperty */
    public $payLinkOrderBy;

    /**
     * @var SortDirection
     */
    public $order;

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

    public $transactionModifier = TransactionModifier::NONE;

    /**
     * @internal
     * @var TimeZoneConversion
     */
    public $timeZoneConversion;

    public function __construct($activity)
    {
        parent::__construct($activity);

        $this->transactionType = $activity;
        $this->searchBuilder = new SearchCriteriaBuilder($this);
    }

    /**
     * Sets the client transaction ID as criteria for the report.
     *
     * @param string $value The client transaction ID
     *
     * @return TransactionReportBuilder
     */
    public function withClientTransactionId($value)
    {
        $this->clientTransactionId = $value;
        return $this;
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
     * 
     * @param mixed $value 
     * @return $this 
     */
    public function withStartDate($value)
    {
        $this->searchBuilder->startDate = $value;
        return $this;
    }

    /**
     * Sets the payment type.
     *
     * @param string $value The payemtn type
     *
     * @return TransactionReportBuilder
     */
    public function withPaymentType($value)
    {
        $this->paymentType = $value;
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
     * Sets the gateway stored payment method id as criteria for the report.
     *
     * @param string $storedPaymentMethodId
     * @return $this
     */
    public function withStoredPaymentMethodId($storedPaymentMethodId)
    {
        $this->searchBuilder->storedPaymentMethodId = $storedPaymentMethodId;
        return $this;
    }

    /**
     * Sets the gateway stored action id as criteria for the report.
     *
     * @param string $actionId
     *
     * @return $this
     */
    public function withActionId($actionId)
    {
        $this->searchBuilder->actionId = $actionId;
        return $this;
    }

    public function withBankPaymentId($bankPaymentId)
    {
        $this->searchBuilder->bankPaymentId = $bankPaymentId;
        return $this;
    }

    public function withPayLinkId($payLinkId)
    {
        $this->searchBuilder->payLinkId = $payLinkId;
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
                $this->order = $sortDirection;
                break;
            case ReportType::FIND_DEPOSITS:
            case ReportType::FIND_DEPOSITS_PAGED:
                $this->depositOrderBy = $sortProperty;
                $this->order = $sortDirection;
                break;
            case ReportType::FIND_DISPUTES:
            case ReportType::FIND_DISPUTES_PAGED:
            case ReportType::FIND_SETTLEMENT_DISPUTES:
            case ReportType::FIND_SETTLEMENT_DISPUTES_PAGED:
                $this->disputeOrderBy = $sortProperty;
                $this->order = $sortDirection;
                break;
            case ReportType::FIND_STORED_PAYMENT_METHODS_PAGED:
                $this->storedPaymentMethodOrderBy = $sortProperty;
                $this->order = $sortDirection;
                break;
            case ReportType::FIND_ACTIONS_PAGED:
                $this->actionOrderBy = $sortProperty;
                $this->order = $sortDirection;
                break;
            case ReportType::FIND_PAYLINK_PAGED:
                $this->payLinkOrderBy = $sortProperty;
                $this->order = $sortDirection;
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

        $this->validations->of(ReportType::DOCUMENT_DISPUTE_DETAIL)
            ->check('disputeDocumentId')->isNotNullInSubProperty('searchBuilder');

        $this->validations->of(ReportType::PAYLINK_DETAIL)
            ->check('payLinkId')->isNotNullInSubProperty('searchBuilder');
    }
}
