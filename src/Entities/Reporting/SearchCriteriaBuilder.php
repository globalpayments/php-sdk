<?php
namespace GlobalPayments\Api\Entities\Reporting;

use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;

class SearchCriteriaBuilder
{
    /**
     * @var TransactionReportBuilder
     */
    public $reportBuilder;
    
    /**
     * @var string
     */
    public $accountNumberLastFour;
    
    /**
     * @var string
     */
    public $altPaymentStatus;
    
    /**
     * @var string
     */
    public $authCode;
    
    /**
     * @var string
     */
    public $bankRoutingNumber;
    
    /**
     * @var string
     */
    public $batchId;
    
    /**
     * @var string
     */
    public $batchSequenceNumber;
    
    /**
     * @var string
     */
    public $buyerEmailAddress;
    
    /**
     * @var string
     */
    public $cardHolderFirstName;
    
    /**
     * @var string
     */
    public $cardHolderLastName;
    
    /**
     * @var string
     */
    public $cardHolderPoNumber;
    
    /**
     * @var string
     */
    public $cardNumberFirstSix;
    
    /**
     * @var string
     */
    public $cardNumberLastFour;
    
    /**
     * @var IEnumerable<CardType>
     */
    public $cardTypes;
    
    /**
     * @var string
     */
    public $checkFirstName;
    
    /**
     * @var string
     */
    public $checkLastName;
    
    /**
     * @var string
     */
    public $checkName;
    
    /**
     * @var string
     */
    public $checkNumber;
    
    /**
     * @var string
     */
    public $clerkId;
    
    /**
     * @var string
     */
    public $clientTransactionId;
    
    /**
     * @var string
     */
    public $customerId;
    
    /**
     * @var string
     */
    public $displayName;
    
    /**
     * @var DateTime
     */
    public $endDate;
    
    /**
     * @var string
     */
    public $giftCurrency;
    
    /**
     * @var string
     */
    public $giftMaskedAlias;
    
    /**
     * @var bool
     */
    public $fullyCaptured;
    
    /**
     * @var string
     */
    public $invoiceNumber;
    
    /**
     * @var string
     */
    public $issuerResult;
    
    /**
     * @var string
     */
    public $issuerTransactionId;
    
    /**
     * @var bool
     */
    public $oneTime;
    
    /**
     * @var string
     */
    public $paymentMethodKey;
    
    /**
     * @var IEnumerable<PaymentMethodType>
     */
    public $paymentTypes;
    
    /**
     * @var string
     */
    public $referenceNumber;
    
    /**
     * @var IEnumerable<TransactionType>
     */
    public $transactionType;
    
    /**
     * @var decimal
     */
    public $settlementAmount;
    
    /**
     * @var string
     */
    public $scheduleId;
    
    /**
     * @var string
     */
    public $siteTrace;
    
    /**
     * @var DateTime
     */
    public $startDate;
    
    /**
     * @var string
     */
    public $uniqueDeviceId;
    
    /**
     * @var string
     */
    public $username;
    
    public function __construct(TransactionReportBuilder $reportBuilder = null)
    {
        $this->reportBuilder = $reportBuilder;
    }
    
    public function and($criteria, $value)
    {
        if (property_exists($this, $criteria)) {
            $this->{$criteria} = $value;
        }
        return $this;
    }
    
    public function execute($configName = "default")
    {
        if (($this->reportBuilder) === null) {
            throw new ArgumentException(
                sprintf(
                    'ReportBuilder is null',
                    $this->reportBuilder,
                    static::class
                )
            );
        }
        return $this->reportBuilder->execute($configName);
    }
}
