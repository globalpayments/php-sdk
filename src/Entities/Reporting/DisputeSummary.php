<?php


namespace GlobalPayments\Api\Entities\Reporting;


use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\DisputeDocument;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class DisputeSummary
{
    /**
     * @var string
     */
    public $merchantHierarchy;

    public ?string $merchantName;

    /**
     * @var \DateTime
     */
    public $depositDate;
    /**
     * @var string
     */
    public $depositReference;

    /**
     * @var integer
     */
    public $caseAmount;
    /**
     * @var string
     */
    public $caseCurrency;

    /** This field indicates the distinct step a dispute is at, within the dispute lifecycle. */
    public ?string $caseStage;

    /** Time the current Dispute stage was created.  */
    public ?\DateTime $disputeStageTime;
    /**
     * @var string
     */
    public $caseStatus;
    /**
     * @var \DateTime
     */
    public $transactionTime;
    /**
     * @var string
     */
    public $transactionType;
    /**
     * @var integer
     */
    public $transactionAmount;
    /**
     * @var string
     */
    public $transactionCurrency;

    /** Unique identifier for the Dispute on the Global Payments system. */
    public string $caseId;
    /**
     * @var \DateTime
     */
    public $caseIdTime;
    /**
     * @var string
     */
    public $caseMerchantId;

    /**
     * @var string
     */
    public $transactionARN;
    /**
     * @var string
     */
    public $transactionReferenceNumber;

    /**
     * @var string
     */
    public $transactionAuthCode;
    /**
     * @var string
     */
    public $transactionCardType;
    /**
     * @var string
     */
    public $transactionMaskedCardNumber;
    /**
     * @var string
     */
    public $reason;
    /**
     * @var string
     */
    public $reasonCode;
    /**
     * @var string
     */
    public $result;
    public array $issuerComment = [];
    public array $issuerCaseNumber = [];

    /**
     * @var integer
     */
    public $disputeCustomerAmount;

    public ?string $disputeCustomerCurrency;
    /**
     * @var \DateTime
     */
    public $respondByDate;

    /**
     * @var integer
     */
    public $lastAdjustmentAmount;
    /**
     * @var string
     */
    public $lastAdjustmentCurrency;
    /**
     * @var string
     */
    public $lastAdjustmentFunding;

    /** @var array<DisputeDocument> */
    public $documents;

    public ?string $transactionBrandReference;

    /**
     * @return ManagementBuilder
     */
    public function accept()
    {
        return (new ManagementBuilder(TransactionType::DISPUTE_ACCEPTANCE))->withDisputeId($this->caseId);
    }

    /**
     * @param array $documents
     *
     * @return ManagementBuilder
     */
    public function challenge($documents)
    {
        return (new ManagementBuilder(TransactionType::DISPUTE_CHALLENGE))
            ->withDisputeId($this->caseId)
            ->withDisputeDocuments($documents);
    }
}