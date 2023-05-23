<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Enums\ApplicationCryptogramType;

class TerminalResponse extends DeviceResponse
{
    /** @var string */
    public $responseCode;

    /** @var string */
    public $responseText;

    /** @var string */
    public $transactionId;

    /** @var string */
    public $terminalRefNumber;

    /** @var string */
    public $token;

    /** @var string */
    public $signatureStatus;

    public $signatureData;
    /** @var string */
    public $transactionType;
    /** @var string */
    public $maskedCardNumber ;
    /** @var string */
    public $entryMethod;
    /** @var string */
    public $authorizationCode;
    /** @var string */
    public $approvalCode ;
    /** @var double */
    public $transactionAmount ;
    /** @var double */
    public $amountDue ;
    /** @var double */
    public $balanceAmount ;
    /** @var string */
    public $cardHolderName ;
    /** @var string */
    public $cardBIN ;
    /** @var bool */
    public $cardPresent;
    /** @var string */
    public $expirationDate;
    /** @var double */
    public $tipAmount ;
    /** @var double */
    public $cashBackAmount;
    /** @var string */
    public $avsResponseCode ;
    /** @var string */
    public $avsResponseText;
    /** @var string */
    public $cvvResponseCode;
    /** @var string */
    public $cvvResponseText;
    /** @var bool */
    public $taxExempt;
    /** @var string */
    public $taxExemptId;
    /** @var string */
    public $ticketNumber;
    /** @var string */
    public $paymentType ;
    /** @var string */
    public $applicationPreferredName ;
    /** @var string */
    public $applicationLabel;
    /** @var string */
    public $applicationId ;
    /** @var ApplicationCryptogramType */
    public $applicationCryptogramType ;
    /** @var string */
    public $applicationCryptogram;
    /** @var string */
    public $cardHolderVerificationMethod;
    /** @var string */
    public $terminalVerificationResults;
    /** @var double */
    public $merchantFee ;
}