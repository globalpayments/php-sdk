<?php
namespace GlobalPayments\Api\Entities\Reporting;

use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\DepositStatus;
use GlobalPayments\Api\Entities\Enums\DisputeStage;
use GlobalPayments\Api\Entities\Enums\DisputeStatus;
use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\Enums\FraudFilterResult;
use GlobalPayments\Api\Entities\Enums\PayLinkStatus;
use GlobalPayments\Api\Entities\Enums\PaymentEntryMode;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use GlobalPayments\Api\Entities\Enums\PaymentProvider;
use GlobalPayments\Api\Entities\Enums\PaymentType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\ReasonCode;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

class SearchCriteriaBuilder
{
    /**
     * @var TransactionReportBuilder
     */
    public $reportBuilder;

    /**
     * @var string
     */
    public $accountName;

    /**
     * @var string
     */
    public $accountNumberLastFour;
    
    /**
     * @var string
     */
    public $altPaymentStatus;

    /**
     * @var integer
     */
    public $amount;

    /**
     * @var string
     */
    public $aquirerReferenceNumber;
    
    /**
     * @var string
     */
    public $authCode;

    /**
     * @var string
     */
    public $bankAccountNumber;
    
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
    public $brandReference;
    
    /**
     * @var string
     */
    public $buyerEmailAddress;

    /**
     * @var string
     */
    public $cardBrand;
    
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
     * @var array<CardType>
     */
    public $cardTypes;

    /**
     * @var Channel
     */
    public $channel;
    
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
    public $country;

    /**
     * @var string
     */
    public $currency;
    
    /**
     * @var string
     */
    public $customerId;

    /**
     * @var string
     */
    public $depositId;

    /**
     * @var string
     */
    public $depositReference;

    /**
     * @var DepositStatus
     */
    public $depositStatus;
    
    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $disputeId;

    /**
     * @var DisputeStage
     */
    public $disputeStage;

    /**
     * @var DisputeStatus
     */
    public $disputeStatus;

    /** @var string */
    public $disputeDocumentId;

    /**
     * @var \DateTime
     */
    public $endBatchDate;
    
    /**
     * @var \DateTime
     */
    public $endDate;

    /**
     * @var \DateTime
     */
    public $fromTimeLastUpdated;

    /**
     * @var \DateTime
     */
    public $toTimeLastUpdated;

    /**
     * @var \DateTime
     */
    public $endDepositDate;

    /**
     * @var \DateTime
     */
    public $endStageDate;

    /**
     * @var bool
     */
    public $fullyCaptured;

    /**
     * @var string
     */
    public $giftCurrency;

    /**
     * @var string
     */
    public $giftMaskedAlias;

    /**
     * @var string
     */
    public $hierarchy;

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
     * @var \DateTime
     */
    public $localTransactionEndTime;

    /**
     * @var \DateTime
     */
    public $localTransactionStartTime;

    /**
     * @var string
     */
    public $merchantId;

    /**
     * @var string
     */
    public $name;
    
    /**
     * @var bool
     */
    public $oneTime;

    /**
     * @var string
     */
    public $oderId;

    /**
     * @var PaymentEntryMode
     */
    public $paymentEntryMode;

    /**
     * @var PaymentType
     */
    public $paymentType;

    /**
     * @var PaymentMethodName
     */
    public $paymentMethodName;

    /**
     * @var PaymentProvider
     */
    public $paymentProvider;

    /**
     * @var IPaymentMethod
     */
    public $paymentMethod;

    /** @var PaymentMethodUsageMode */
    public $paymentMethodUsageMode;

    /** @var string */
    public $paymentMethodKey;

    /**
     * @var PaymentMethodType
     */
    public $paymentMethodType;
    
    /**
     * @var string
     */
    public $referenceNumber;
    
    /**
     * @var array<TransactionType>
     */
    public $transactionType;
    
    /**
     * @var integer
     */
    public $settlementAmount;

    /**
     * @var string
     */
    public $settlementDisputeId;

    /**
     * @var string
     */
    public $storedPaymentMethodId;

    /**
     * @var string
     */
    public $storedPaymentMethodStatus;
    
    /**
     * @var string
     */
    public $scheduleId;
    
    /**
     * @var string
     */
    public $siteTrace;

    /**
     * @var \DateTime
     */
    public $startBatchDate;
    
    /**
     * @var \DateTime
     */
    public $startDate;

    /**
     * @var \DateTime
     */
    public $startDepositDate;

    /**
     * @var \DateTime
     */
    public $startStageDate;

    /**
     * @var string
     */
    public $systemHierarchy;

    /**
     * @var string
     */
    public $tokenFirstSix;

    /**
     * @var string
     */
    public $tokenLastFour;

    /**
     * @var TransactionStatus
     */
    public $transactionStatus;
    
    /**
     * @var string
     */
    public $uniqueDeviceId;
    
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $timezone;

    /**
     * @var string
     */
    public $actionId;

    /**
     * @var string
     */
    public $actionType;

    /**
     * @var string
     */
    public $resource;

    /**
     * @var string
     */
    public $resourceStatus;

    /**
     * @var string
     */
    public $resourceId;

    /**
     * @var string
     */
    public $merchantName;

    /**
     * @var string
     */
    public $appName;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $responseCode;

    /**
     * @var string
     */
    public $httpResponseCode;

    /**
     * This is the PayLink id
     *
     * @var string
     */
    public $payLinkId;

    /** @var string */
    public $description;

    /** @var \DateTime */
    public $expirationDate;

    /** @var PayLinkStatus */
    public $payLinkStatus;
    
    /** ******************************
     * Open banking search criteria *
     ********************************/

    /**
     * Open banking transaction id
     *
     * @var string
     */
    public $bankPaymentId;

    /**
     * Indicates if sensitive information including destination account number, IBAN, account name, or sort code
     * should be sent back in the response (when set to TRUE).
     *
     * @var boolean
     */
    public $returnPii;

    /** END Open Banking search criteria */
    

    /** START FRAUD MANAGEMENT search criteria */

    /** @var FraudFilterMode */
    public $riskAssessmentMode;

    /** @var FraudFilterResult */
    public $riskAssessmentResult;

    /** @var ReasonCode */
    public $riskAssessmentReasonCode;

    /** END FRAUD MANAGEMENT search criteria */

    public function __construct(TransactionReportBuilder $reportBuilder = null)
    {
        $this->reportBuilder = $reportBuilder;
    }
    
    public function andWith($criteria, $value)
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
