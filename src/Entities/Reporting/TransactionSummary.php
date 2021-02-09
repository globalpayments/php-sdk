<?php
namespace GlobalPayments\Api\Entities\Reporting;

use GlobalPayments\Api\Entities\Enums\GpApi\EntryMode;

class TransactionSummary
{
    /**
     * @var string
     */
    public $accountDataSource;

    /*** @var AltPaymentData
     */
    public $altPaymentData;

    /**
     * The originally requested authorization amount.
     *
     * @var decimal
     */
    public $amount;

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
     * The authorization code provided by the issuer.
     *
     * @var string
     */
    public $authCode;

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
     * @var string
     */
    public $brandReference;

    /**
     * @var decimal
     */
    public $captureAmount;

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
    public $cardHolderName;

    /**
     * @var string
     */
    public $cardSwiped;

    /**
     * @var string
     */
    public $cardType;

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
     * The originally requested convenience amount.
     *
     * @var decimal
     */
    public $convenienceAmount;

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
    public $fraudRuleInfo;

    /**
     * @var bool
     */
    public $fullyCaptured;

    /**
     * @var decimal
     */
    public $gratuityAmount;

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
     * The authorized card number, masked.
     *
     * @var string
     */
    public $maskedCardNumber;

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
    public $paymentType;

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
     * @var decimal
     */
    public $surchargeAmount;

    /**
     * @var decimal
     */
    public $taxAmount;

    /**
     * @var string
     */
    public $taxType;

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
     * @var string
     */
    public $currency;

    /**
     * @var EntryMode
     */
    public $entryMode;

    /**
     * @var string
     */
    public $depositId;

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
    public $merchantId;

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
}
