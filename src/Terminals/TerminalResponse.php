<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Enums\ApplicationCryptogramType;

abstract class TerminalResponse extends DeviceResponse
{
    /** @var string */
    public ?string $responseCode = null;

    /** @var string */
    public ?string $responseText = null;

    /** @var string */
    public ?string $transactionId = null;

    /** @var string */
    public ?string $terminalRefNumber = null;

    /** @var string */
    public ?string $token = null;

    /** @var string */
    public ?string $signatureStatus = null;

    public mixed $signatureData = null;
    /** @var string */
    public ?string $transactionType = null;
    /** @var string */
    public ?string $maskedCardNumber = null;
    /** @var string */
    public ?string $entryMethod = null;
    /** @var string */
    public ?string $authorizationCode = null;
    /** @var string */
    public ?string $approvalCode = null;
    /** @var double */
    public float|int|string|null $transactionAmount = null;
    /** @var double */
    public float|int|string|null $amountDue = null;
    /** @var double */
    public float|int|string|null $balanceAmount = null;
    /** @var string */
    public ?string $cardHolderName = null;
    /** @var string Indicates the type of card used for the transaction. */
    public ?string $cardType = null;
    /** @var string|null Possible Values: Credit */
    public ?string $cardGroup = null;
    /** @var string */
    public ?string $cardBIN = null;
    /** @var bool */
    public ?bool $cardPresent = null;
    /** @var string */
    public ?string $expirationDate = null;
    /** @var double */
    public float|int|string|null $tipAmount = null;
    /** @var double */
    public float|int|string|null $cashBackAmount = null;
    /** @var string */
    public ?string $avsResponseCode = null;
    /** @var string */
    public ?string $avsResponseText = null;
    /** @var string */
    public ?string $cvvResponseCode = null;
    /** @var string */
    public ?string $cvvResponseText = null;
    /** @var bool */
    public ?bool $taxExempt = null;
    /** @var string */
    public ?string $taxExemptId = null;
    /** @var string */
    public ?string $ticketNumber = null;
    /** @var string */
    public ?string $paymentType = null;
    /** @var string */
    public ?string $applicationPreferredName = null;
    /** @var string */
    public ?string $applicationLabel = null;
    /** @var string */
    public ?string $applicationId = null;
    /** @var ApplicationCryptogramType */
    public mixed $applicationCryptogramType = null;
    /** @var string */
    public ?string $applicationCryptogram = null;
    /** @var string */
    public ?string $cardHolderVerificationMethod = null;
    /** @var string */
    public ?string $terminalVerificationResults = null;

    /** @var double */
    public float|int|string|null $merchantFee = null;

    public ?string $ecrId = null;

    public ?string $requestId = null;

    /**
     * Indicator if the POS should expect another response message. Possible Values: 0 or 1
     *
     * @var string|null
     */
    public ?string $multipleMessage;
}