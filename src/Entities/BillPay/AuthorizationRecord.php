<?php

namespace GlobalPayments\Api\Entities\BillPay;

class AuthorizationRecord
{
    public string $addToBatchReferenceNumber;
    
    public ?float $amount;

    public string $authCode;

    public string $authorizationType;

    public string $avsResultCode;

    public string $avsResultText;

    public ?string $cardEntryMethod;

    public string $cvvResultCode;

    public string $cvvResultText;

    public ?string $emvApplicationCryptogram;

    public string $emvApplicationCryptogramType;

    public string $emvApplicationID;

    public string $emvApplicationName;

    public string $emvCardholderVerificationMethod;

    public ?string $emvIssuerResponse;

    public ?string $emvSignatureRequired;

    public string $gateway;

    public string $gatewayBatchID;

    public string $gatewayDescription;

    public string $maskedAccountNumber;

    public string $maskedRoutingNumber;

    public string $paymentMethod;

    public ?int $referenceAuthorizationID;

    public string $referenceNumber;

    public string $routingNumber;

    public ?int $authorizationID;

    public ?float $netAmount;

    public ?int $originalAuthorizationID;
}