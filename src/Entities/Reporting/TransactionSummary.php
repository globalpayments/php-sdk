<?php

namespace GlobalPayments\Api\Entities\Reporting;

use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\BankPaymentResponse;
use GlobalPayments\Api\Entities\BNPLResponse;
use GlobalPayments\Api\Entities\Card;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\Enums\PaymentEntryMode;
use GlobalPayments\Api\Entities\FraudManagementResponse;
use GlobalPayments\Api\Entities\LodgingData;
use GlobalPayments\Api\Entities\ThreeDSecure;

class TransactionSummary extends BaseSummary
{
    /**
     * @var string
     */
    public $accountDataSource;

    /*** @var AltPaymentData
     */
    public $altPaymentData;

    /**
     * @var string
     */
    public $aquirerReferenceNumber;

    /**
     * The authorized amount.
     *
     * @var decimal
     */
    public $authorizedAmount;

    /**
     * @var string
     */
    public $application;

    /**
     * @var ?AuthorizationRecord[]
     */
    public $authorizationRecords;

    /**
     * @var decimal
     */
    public $captureAmount;

    /**
     * The originally requested convenience amount.
     *
     * @var decimal
     */
    public $convenienceAmount;

    /**
     * @var decimal
     */
    public $gratuityAmount;

    /**
     * The settled from the authorization.
     *
     * @var decimal
     */
    public $settlementAmount;

    /**
     * The originally requested shipping amount.
     *
     * @var decimal
     */
    public $shippingAmount;

    /**
     * @var decimal
     */
    public $surchargeAmount;

    /**
     * @var decimal
     */
    public $taxAmount;

    public ?float $cashBackAmount = null;
    public ?float $merchantAmount = null;
    public ?string $merchantCurrency = null;

    /**
     * @var string
     */
    public $merchantInvoiceNumber;

    /**
     * @var string
    */
    public $merchantPONumber;

    /**
     * @var string
     */
    public $merchantTransactionDescription;

    /**
     * @var string
     */
    public $merchantTransactionID;

    /**
     * @var float
     */
    public $netAmount;

    /**
     * @var float
     */
    public $netFeeAmount;

    /**
     * The authorization code provided by the issuer.
     *
     * @var string
     */
    public $authCode;

    /** @var string */
    public $avsResponseCode;

    /** @var string */
    public $avsResponseMessage;

    /**
     * @var DateTime
     */
    public $batchCloseDate;

    /**
     * @var string
     */
    public $batchSequenceNumber;

    /**
     * @var Address
     */
    public $billingAddress;

    /**
     * @var ?Bill[] $bills 
     */
    public $billTransactions;

    /**
     * @var string
     */
    public $brandReference;

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
    public $cardHolderAddr;

    /**
     * @var string
     */
    public $cardHolderCity;

    /**
     * @var string
     */
    public $cardHolderState;

    /**
     * @var string
     */
    public $cardHolderZip;

    /**
     * @var string
     */
    public $cardHolderName;

    /**
     * @var string
     */
    public $cardSwiped;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->brand
     */
    public $cardType;

    public ?Card $cardDetails = null;

    /**
     * @var string
     */
    public $cavvResponseCode;

    /**
     * @var string
     */
    public $channel;

    /**
     * @var CheckData
     */
    public $checkData;

    /**
     * @var string
     */
    public $clerkId;

    /**
     * The client transaction ID sent in the authorization request.
     *
     * @var string
     */
    public $clientTransactionId;

    /**
     * @var string
     */
    public $companyName;

    /**
     * @var string
     */
    public $customerFirstName;

    /**
     * @var string
     */
    public $customerId;

    /**
     * @var string
     */
    public $customerLastName;

    /** @var string */
    public $cvnResponseCode;

    /** @var string */
    public $cvnResponseMessage;

    /**
     * @var bool
     */
    public $debtRepaymentIndicator;

    /**
     * @var string
     */
    public $description;

    /**
     * The device ID where the transaction was ran; where applicable.
     *
     * @var int
     */
    public $deviceId;

    /**
     * @var string
     */
    public $emvChipCondition;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var float
     */
    public $feeAmount;

    /**
     * @var string
     */
    public $fraudRuleInfo;

    /**
     * @var bool
     */
    public $fullyCaptured;

    /**
     * @var bool
     */
    public $hasEcomPaymentData;

    /**
     * @var bool
     */
    public $hasEmvTags;

    /**
     * @var string
     */
    public $invoiceNumber;

    /**
     * The original response code from the issuer.
     *
     * @var string
     */
    public $issuerResponseCode;

    /**
     * The original response message from the issuer.
     *
     * @var string
     */
    public $issuerResponseMessage;

    /**
     * @var string
     */
    public $issuerTransactionId;

    /**
     * The original response code from the gateway.
     *
     * @var string
     */
    public $gatewayResponseCode;

    /**
     * The original response message from the gateway.
     *
     * @var string
     */
    public $gatewayResponseMessage;

    /**
     * @var string
     */
    public $giftCurrency;

    /**
     * @var LodgingData
     */
    public $lodgingData;

    /**
     * @var string
     */
    public $maskedAlias;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->maskedCardNumber
     * The authorized card number, masked.
     *
     * @var string
     */
    public $maskedCardNumber;

    /**
     * The authorized account number, masked.
     *
     * @var string
     */
    public $maskedAccountNumber;

    public ?string $maskedPaymentToken;

    /**
     * @var bool
     */
    public $oneTimePayment;

    /**
     * The gateway transaction ID of the authorization request.
     *
     * @var string
     */
    public $originalTransactionId;

    /**
     * @var string
     */
    public $paymentMethodKey;

    /**
     * @var string
     */
    public $paymentPurposeCode;

    /**
     * @var string
     */
    public $paymentType;

    /**
     * @var Customer
     */
    public $payorData;

    /**
     * @var string
     */
    public $poNumber;

    /**
     * @var string
     */
    public $recurringDataCode;

    /**
     * The reference number provided by the issuer.
     *
     * @var string
     */
    public $referenceNumber;

    /**
     * @var int
     */
    public $repeatCount;

    /**
     * @var DateTime
     */
    public $responseDate;

    /**
     * @var string
     */
    public $scheduleId;

    /**
     * The transaction type.
     *
     * @var string
     */
    public $serviceName;

    /**
     * @var string
     */
    public $siteTrace;

    /**
     * The transaction status.
     *
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $transactionType;

    /**
     * @var string
     */
    public $taxType;

    /** 
     * @var ThreeDSecure 
     */
    public $threeDSecure;

    /**
     * @var string
     */
    public $tokenPanLastFour;

    /**
     * The date/time of the original transaction.
     *
     * @var DateTime
     */
    public $transactionDate;

    /**
     * @var DateTime
     */
    public $transactionLocalDate;

    /**
     * @var string
     */
    public $transactionDescriptor;

    /**
     * @var string
     */
    public $transactionStatus;

    /**
     * The gateway transaction ID of the transaction.
     *
     * @var string
     */
    public $transactionId;

    /**
     * @var string
     */
    public $uniqueDeviceId;

    /**
     * @var string
     */
    public $username;
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
     * @var string
     */
    public $country;
    /**
     * @var PaymentEntryMode
     */
    public $entryMode;

    /**
     * @var string
     */
    public $depositReference;

    /**
     * @var string
     */
    public $depositStatus;

    /**
     * @var DateTime
     */
    public $depositTimeCreated;

    /**
     * @var string
     */
    public $accountNumberLast4;

    /**
     * @var string
     */
    public $accountType;

    public ?AlternativePaymentResponse $alternativePaymentResponse;

    /** @var string */
    public $orderId;

    /** @var string */
    public $eciIndicator;

    /** @var string */
    public $xid;

    /** @var string */
    public $schemeReferenceData;

    /** @var BankPaymentResponse */
    public $bankPaymentResponse;

    public ?BNPLResponse $bnplResponse;

    /** @var string */
    public $fingerprint;

    /** @var string */
    public $fingerprintIndicator;
    public ?FraudManagementResponse $fraudManagementResponse;
    public ?string $terminalRefNumber;
    public ?bool $hostTimeout;
    public ?string $cardEntryMethod;

    public ?DccRateData $dccRateData;

    /**
     * @var installment
     */
    public $installment;

    public string $amountIndicator;
}
