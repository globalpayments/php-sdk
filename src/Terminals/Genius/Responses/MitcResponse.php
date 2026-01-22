<?php

namespace GlobalPayments\Api\Terminals\Genius\Responses;

use Exception;
use GlobalPayments\Api\Terminals\TerminalResponse;

class MitcResponse extends TerminalResponse
{
    /** @var string */
    public ?string $invoiceNumber = null;

    /** @var string */
    public ?string $responseDateTime = null;

    /** @var string */
    public ?string $gatewayResponseCode = null;

    /** @var string */
    public ?string $gatewayResponseMessage = null;

    /** @var double */
    public float|int|string|null $authorizedAmount = null;

    /** @var string */
    public ?string $tokenResponseCode = null;

    /** @var string */
    public ?string $tokenResponseMsg = null;

    /** @var string */
    public ?string $traceNumber = null;

    /** @var array */
    public ?array $icc = null;

    /** @var string */
    public ?string $transactionCurrencyCode = null;

    /** @var string */
    public ?string $tenderType = null;

    /** @var string */
    public ?string $clientTransactionId = null;

    /** @var string */
    public ?string $expMonth = null;

    /** @var string */
    public ?string $expYear = null;

    /** @var string */
    public ?string $postalCode = null;

    /** @var string */
    public ?string $customerId = null;

    /**
     * 
     * @param int $responseCode 
     * @param string $responseMessage 
     * @param array $rawResponse 
     * @return void 
     */
    public function __construct(
        int $gatewayResponseCode,
        string $gatewayResponseMessage,
        array $rawResponse
    ) {
        $this->deviceResponseCode = $gatewayResponseCode;
        $this->deviceResponseText = $gatewayResponseMessage;

        array_walk_recursive($rawResponse, array($this, 'assignValues'));

        if ($this->responseCode === null)
            $this->responseCode = $this->normalizeResponseCode($gatewayResponseCode);

        // not sure if we'll use the icc values so we're
        // returning the whole array for now
        try {
            $this->icc = $rawResponse['transactions'][0]['credit_attributes']['emv']['icc'];
        } catch (Exception $e) {
            try {
                $this->icc = $rawResponse['transactions'][0]['debit_attributes']['emv']['icc'];
            } catch (Exception $e) {
                // om nom nom
            }
        }
    }

    /**
     * callback for array_walk_recursive in constructor
     * 
     * @param string $value
     * @param string $key
     * @return void
     */
    private function assignValues(string $value, string $key): void
    {
        if ($key == 'invoice_number') $this->invoiceNumber = $value;
        if ($key == 'amount') $this->transactionAmount = (double) $value;
        if ($key == 'currency_code') $this->transactionCurrencyCode = $value;
        if ($key == 'gratuity_amount') $this->tipAmount = (double) $value;
        if ($key == 'tender_type') $this->tenderType = $value;
        if ($key == 'entry_type') $this->entryMethod = $value;
        if ($key == 'id') $this->transactionId = $value;
        if ($key == 'reference_id') $this->clientTransactionId = $value;
        if ($key == 'transaction_datetime') $this->responseDateTime = $value;
        if ($key == 'approval_code') $this->approvalCode = $value;
        if ($key == 'avs_response') $this->avsResponseCode = $value;
        if ($key == 'avs_response_description') $this->avsResponseText = $value;
        if ($key == 'cardsecurity_response') $this->cvvResponseCode = $value;
        if ($key == 'cashback_amount') $this->cashBackAmount = (double) $value;
        if ($key == 'type') $this->paymentType = $value;
        if ($key == 'masked_card_number') $this->maskedCardNumber = $value;
        if ($key == 'cardholder_name') $this->cardHolderName = $value;
        if ($key == 'expiry_month') $this->expMonth = $value;
        if ($key == 'expiry_year') $this->expYear = $value;
        if ($key == 'token') $this->token = $value;
        if ($key == 'type') $this->paymentType = $value;
        if ($key == 'balance') $this->balanceAmount = (double) $value;
        if ($key == 'postal_code') $this->postalCode = $value;
        if ($key == 'rfmiq') $this->customerId = $value;
        if ($key == 'debit_trace_number') $this->traceNumber = $value;
        if ($key == 'tokenization_error_code') $this->tokenResponseCode = $value;
        if ($key == 'tokenization_error_message') $this->tokenResponseMsg = $value;
        if ($key == 'amount_authorized') $this->authorizedAmount = (double) $value;
        if ($key == 'status_code') $this->responseCode = $this->normalizeResponseCode($value);
        if ($key == 'status') $this->responseText = $value;
    }

    private function normalizeResponseCode(int $code)
    {
        switch ($code) {
            case 200:
            case 201:
            case 473:
                return '00';
            case 470:
            case 472:
                return '05';
            case 471:
            case 474:
                return '10';
            case 400:
            case 401:
            case 402:
            case 403:
            case 404:
            case 409:
            case 429:
            case 500:
            case 503:
                return 'ER';
            default:
                break;
        }
    }
}
