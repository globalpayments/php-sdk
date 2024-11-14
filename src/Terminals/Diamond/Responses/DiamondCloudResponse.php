<?php

namespace GlobalPayments\Api\Terminals\Diamond\Responses;

use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;
use GlobalPayments\Api\Terminals\Diamond\Entities\Enums\AuthorizationMethod;
use GlobalPayments\Api\Terminals\Diamond\Entities\Enums\AuthorizationType;
use GlobalPayments\Api\Terminals\Diamond\Entities\Enums\CardSource;
use GlobalPayments\Api\Terminals\Diamond\Entities\Enums\TransactionResult;
use GlobalPayments\Api\Terminals\Diamond\Entities\Enums\TransactionTypeResponse;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Utils\StringUtils;

class DiamondCloudResponse extends TerminalResponse implements ITerminalReport
{
    /**
     * For Visa Contactless cards: Visa available offline spending amount For Erzsebet cards: remaining
     * balance of the Voucher Type used NOTE: should be printed on the customer receipt only, not on a
     * merchant/control receipt.
     */
    public ?string $aosa;

    /**
     * Authorization message number – usually equal to transaction number.
     */
    public ?string $authorizationMessage;

    /**
     * Cardholder authorization method, possible values enum class AuthorizationMethod
     */
    public ?string $authorizationMethod;

    /**
     * Authorization type, possible values enum class AuthorizationType
     */
    public ?string $authorizationType;

    /**
     * Brand name of the card – application label(EMV)or cardset name
     */
    public ?string $cardBrandName;

    /**
     * Reader used to read card data. This character depends on the acquirer values in enum class CardSource
     */
    public ?string $cardSource;

    /**
     * Transaction date in format YYYY.MM.DD
     */
    public ?string $date;

    /**
     * currencyExchangeRate float Currency exchange rate. Should be set only for DCC transaction.
     * Uses dot ‘.’ as a separator.
     */
    public ?float $currencyExchangeRate;

    /**
     * DCC currency exponent.
     */
    public ?int $dccCurrencyExponent;

    /**
     * DCC text 1. Should be set only for DCC transaction.
     */
    public ?string $dccText1;

    /**
     * DCC text 2. Should be set only for DCC transaction.
     */
    public ?string $dccText2;

    /**
     * Optional descriptive information about intent or android specific error
     */
    public ?string $errorMessage;

    /**
     * Merchant ID
     */
    public ?string $merchantId;

    /**
     * Transaction number
     */
    public ?string $clientTransactionId;

    /**
     * Terminal currency
     */
    public ?string $terminalCurrency;

    /** Terminal identifier */
    public ?string $terminalId;

    /**
     * Terminal printing indicator (value not equal 0 means that printout has been made by the terminal ).
     */
    public ?string $terminalPrintingIndicator;

    /** Transaction time format hh:mm:ss */
    public ?string $time;

    /**
     * Transaction amount in terminal currency. Should be set only for DCC transaction
     */
    public ?float $transactionAmountInTerminalCurrency;

    /**
     * Transaction currency. Should be always set. In DCC transaction this currency is selected by user.
     */
    public ?string $transactionCurrency;

    /** Transaction title */
    public ?string $transactionTitle;

    /** EMV Application Identifier */
    public ?string $emvApplicationId;//AID

    /** TVR for EMV */
    public ?string $emvTerminalVerificationResults; //TVR

    /** TSI for EMV  */
    public ?string $emvTransactionStatusInfo; //TSI

    /** EMV Transaction Cryptogram */
    public ?string $emvCryptogram; //AC

    /** EMV card transaction counter */
    public ?string $emvCardTransactionCounter; //ATC

    public ?string $invoiceNumber;

    public ?string $resultId;

    /** Current batch number, 4 digit value  */
    public ?string $batchNumber;

    public function __construct($rawResponse)
    {
        if (StringUtils::isJson($rawResponse)) {
            $rawResponse = json_decode($rawResponse);
            $this->transactionId = $rawResponse->CloudTxnId ?? ($rawResponse->cloudTxnId ?? null);
            $this->invoiceNumber = $rawResponse->InvoiceId ?? null;
            $this->referenceNumber = $rawResponse->Device ?? null;
            $this->terminalRefNumber = $rawResponse->PosId ?? null;
            $this->resultId = $rawResponse->PaymentResponse->ResultId ?? null;
            $paymentDetails = $rawResponse->PaymentResponse->PaymentResponse ??
                ($rawResponse->PaymentResponse ?? $rawResponse);
            if (!empty($paymentDetails)) {
                if (empty($this->transactionId)) {
                    $this->transactionId = $rawResponse->transactionId ?? null;
                }
                $this->status = $paymentDetails->transactionStatus ?? null;
                $this->responseCode = $paymentDetails->resultCode ?? null;
                if (empty($this->responseCode)) {
                    $this->responseCode = isset($paymentDetails->result) ?
                        TransactionResult::getKey($paymentDetails->result) : null;
                }
                $this->responseText = $paymentDetails->hostMessage ?? null;
                if (empty($this->responseText)) {
                    $this->responseText = $paymentDetails->serverMessage ?? null;
                }
                $this->aosa = $paymentDetails->aosa ?? null;
                $this->version = $paymentDetails->applicationVersion ?? null;
                $this->authorizationCode = $paymentDetails->authorizationCode ?? null;
                $this->authorizationMessage = $paymentDetails->authorizationMessage ?? null;
                $this->authorizationMethod = isset($paymentDetails->authorizationMethod) ?
                    AuthorizationMethod::getKey($paymentDetails->authorizationMethod) : null;
                $this->authorizationType = isset($paymentDetails->PaymentResponse->authorizationType) ?
                    AuthorizationType::getKey($paymentDetails->authorizationType) : null;
                $this->cardBrandName =  $paymentDetails->cardBrandName ?? ($paymentDetails->cardBrand ?? null);
                $this->cardSource = isset($paymentDetails->cardSource) ?
                    CardSource::getKey($paymentDetails->cardSource) : null;
                $this->entryMethod = $paymentDetails->entryMethod ?? null;
                $this->cashBackAmount = $paymentDetails->cashback ?? null;
                $this->currencyExchangeRate = $paymentDetails->currencyExchangeRate ?? null;
                $this->date =  $paymentDetails->date ?? null;
                $this->dccCurrencyExponent = $paymentDetails->dccCurrencyExponent ?? null;
                $this->dccText1 = $paymentDetails->dccText1 ?? null;
                $this->dccText2 = $paymentDetails->dccText2 ?? null;
                $this->errorMessage = $paymentDetails->errorMessage ?? null;
                $this->maskedCardNumber = $paymentDetails->maskedCardNumber ?? ($paymentDetails->maskedCard ?? null);
                $this->merchantId =  $paymentDetails->merchantId ?? null;
                $this->clientTransactionId = $paymentDetails->slipNumber ?? null;
                $this->terminalCurrency = $paymentDetails->terminalCurrency ?? null;
                $this->terminalId = $paymentDetails->terminalId ?? null;
                $this->terminalPrintingIndicator = $paymentDetails->terminalPrintingIndicator ?? null;
                $this->time = $paymentDetails->time ?? null;
                $this->date = $paymentDetails->dateTime ?? null;
                $this->tipAmount = $paymentDetails->tipAmount ?? ($paymentDetails->tip ?? null);
                $this->token = $paymentDetails->token ?? null;
                $this->transactionAmount = $paymentDetails->transactionAmount ?? ($paymentDetails->requestAmount ?? null);
                $this->transactionAmountInTerminalCurrency =  $paymentDetails->transactionAmountInTerminalCurrency ?? null;
                $this->transactionCurrency = $paymentDetails->transactionCurrency ?? null;
                $this->transactionTitle = $paymentDetails->transactionTitle ?? null;
                $this->transactionType = isset($paymentDetails->type) ?
                    TransactionTypeResponse::getKey($paymentDetails->type) : null;
                $this->emvCardTransactionCounter = $paymentDetails->ATC ?? null;
                $this->emvCryptogram =  $paymentDetails->AC ?? null;
                $this->emvApplicationId = $paymentDetails->AID ?? null;
                $this->emvTerminalVerificationResults =  $paymentDetails->TVR ?? null;
                $this->emvTransactionStatusInfo =  $paymentDetails->TSI ?? null;
                $this->token = $paymentDetails->paymentToken ?? ($paymentDetails->token ?? null);
                $this->batchNumber = $paymentDetails->batchNumber ?? null;
            }
            $cloudInfo = $rawResponse->PaymentResponse->CloudInfo ?? ($rawResponse->CloudInfo ?? null);
            $this->command = $cloudInfo->Command ?? null;
            $this->deviceResponseCode = '00';
        } else {
            $this->deviceResponseCode = '00';
            $this->transactionId = $rawResponse;
        }
    }
}