<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\TransactionList;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Terminals\Abstractions\ISAFResponse;
use GlobalPayments\Api\Terminals\SummaryResponse;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaSAFType;

class UpaSAFResponse extends UpaResponseHandler implements ISAFResponse
{
    const INVALID_RESPONSE_FORMAT = "The response received is not in the proper format.";
    private array $approved;
    private array $declined;
    private array $pending;

    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse(array $response): void
    {
        parent::parseResponse($response);
        $secondDataNode = $response['data']['data'];
        $this->multipleMessage = $secondDataNode['multipleMessage'] ?? null;
        $safDetailsList = $secondDataNode['SafDetails'] ?? [];
        foreach ($safDetailsList as $safDetails) {
            $summaryResponse = new SummaryResponse();
            $summaryResponse->count = $safDetails['SafCount'];
            $summaryResponse->totalAmount = $safDetails['SafTotal'];
            $summaryResponse->summaryType = $safDetails['SafType'];
            $summaryResponse->transactions = new TransactionList();
            if (isset($safDetails['SafRecords'])) {
                foreach ($safDetails['SafRecords'] as $safRecord) {
                    $transactionSummary = new TransactionSummary();
                    $transactionSummary->transactionType = $safRecord['transactionType'];
                    $transactionSummary->terminalRefNumber = $safRecord['tranNo'];
                    $transactionSummary->referenceNumber = $safRecord['referenceNumber'];
                    $transactionSummary->gratuityAmount = $safRecord['tipAmount'];
                    $transactionSummary->taxAmount = $safRecord['taxAmount'];
                    $transactionSummary->amount = $safRecord['baseAmount'];
                    $transactionSummary->authorizedAmount = $safRecord['authorizedAmount'] ?? null;
                    $transactionSummary->cardType = $safRecord['cardType'] ?? null;
                    $transactionSummary->cardEntryMethod = $safRecord['cardAcquisition'] ?? null;
                    $transactionSummary->maskedCardNumber = $safRecord['maskedPan'] ?? null;
                    $transactionSummary->transactionDate = !empty($safRecord['transactionTime']) ?
                        new \DateTime($safRecord['transactionTime']) : '';
                    $transactionSummary->authCode = $safRecord['approvalCode'] ?? null;
                    $transactionSummary->hostTimeout = $safRecord['hostTimeOut'] ?? null;
                    $transactionSummary->status = $safRecord['responseCode'] ?? null;
                    $summaryResponse->transactions->add($transactionSummary);
                }
            }
            switch ($summaryResponse->summaryType) {
                case UpaSAFType::APPROVED:
                    $this->approved[] = $summaryResponse;
                    break;
                case UpaSAFType::DECLINED:
                    $this->declined[] = $summaryResponse;
                    break;
                case UpaSAFType::PENDING:
                    $this->pending[] = $summaryResponse;
                    break;
            }
        }
     }

    public function getApproved(): array
    {
       return $this->approved;
    }

    public function getPending(): array
    {
        return $this->pending;
    }

    public function getDeclined(): array
    {
        return $this->declined;
    }
}