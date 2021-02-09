<?php


namespace GlobalPayments\Api\Entities\Reporting;


use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class DisputeSummary
{
    /**
     * @var string
     */
    public $merchantHierarchy;
    /**
     * @var string
     */
    public $merchantName;
    /**
     * @var string
     */
    public $merchantDbaName;
    /**
     * @var string
     */
    public $merchantNumber;
    /**
     * @var string
     */
    public $merchantCategory;
    /**
     * @var \DateTime
     */
    public $depositDate;
    /**
     * @var string
     */
    public $depositReference;
    /**
     * @var string
     */
    public $depositType;
    /**
     * @var string
     */
    public $type;
    /**
     * @var integer
     */
    public $caseAmount;
    /**
     * @var string
     */
    public $caseCurrency;
    /**
     * @var string
     */
    public $caseStage;
    /**
     * @var string
     */
    public $caseStatus;
    /**
     * @var string
     */
    public $caseDescription;
    /**
     * @var string
     */
    public $transactionOrderId;
    /**
     * @var \DateTime
     */
    public $transactionLocalTime;
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
    /**
     * @var string
     */
    public $caseNumber;
    /**
     * @var \DateTime
     */
    public $caseTime;
    /**
     * @var string
     */
    public $caseId;
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
    public $caseTerminalId;
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
    public $transactionSRD;
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
    /**
     * @var string
     */
    public $issuerComment;
    /**
     * @var string
     */
    public $issuerCaseNumber;
    /**
     * @var integer
     */
    public $disputeAmount;
    /**
     * @var string
     */
    public $disputeCurrency;
    /**
     * @var integer
     */
    public $disputeCustomerAmount;
    /**
     * @var string
     */
    public $disputeCustomerCurrency;
    /**
     * @var \DateTime
     */
    public $respondByDate;
    /**
     * @var string
     */
    public $caseOriginalReference;
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