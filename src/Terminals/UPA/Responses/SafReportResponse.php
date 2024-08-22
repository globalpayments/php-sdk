<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\TransactionList;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Terminals\DeviceResponse;
use GlobalPayments\Api\Terminals\Enums\SummaryType;
use GlobalPayments\Api\Terminals\SummaryResponse;
use GlobalPayments\Api\Utils\StringUtils;

class SafReportResponse extends UpaResponseHandler
{
    const AUTHORIZED = "AUTHORIZED TRANSACTIONS";
    const PENDING = "PENDING TRANSACTIONS";
    const FAILED = "FAILED TRANSACTIONS";

    public SafReport $reportResult;
    public ?string $multipleMessage;

    /**
     * SafReportResponse constructor.
     * @param $jsonResponse
     * @throws GatewayException
     */
    public function __construct($jsonResponse)
    {
        $this->parseJsonResponse($jsonResponse);
    }

    protected function parseJsonResponse($jsonResponse): void
    {
        parent::parseJsonResponse($jsonResponse);
        $firstDataNode = $this->isGpApiResponse($jsonResponse) ? $jsonResponse->response : $jsonResponse->data;
        $secondDataNode = $firstDataNode->data ?? null;
        if (empty($secondDataNode)) {
            throw new GatewayException(self::INVALID_RESPONSE_FORMAT);
        }
        $this->reportResult = new SafReport();
        $this->multipleMessage = $secondDataNode->multipleMessage ?? null;
        foreach ($secondDataNode->SafDetails as $detail) {
            $this->reportResult->totalAmount += floatval($detail->SafTotal);
            $this->reportResult->totalCount += intval($detail->SafCount);
            $summaryResponse = new SummaryResponse();
            $summaryResponse->totalAmount = $detail->SafTotal ?? null;
            $summaryResponse->count = $detail->SafCount;
            $summaryResponse->summaryType = $this->mapSummaryType($detail->SafType);
            $summaryResponse->transactions = new TransactionList();
            foreach ($detail->SafRecords as $record) {
                $transactionSummary = new TransactionSummary();
                $transactionSummary->transactionType = $record->transactionType;
                $transactionSummary->terminalRefNumber = $record->transId;
                $transactionSummary->referenceNumber = $record->referenceNumber;
                $transactionSummary->gratuityAmount = $record->tipAmount;
                $transactionSummary->taxAmount = $record->taxAmount;
                $transactionSummary->amount = $record->baseAmount;
                $transactionSummary->authorizedAmount = $record->authorizedAmount;
                $transactionSummary->cardType = $record->cardType;
                $transactionSummary->maskedCardNumber = $record->maskedPan;
                $transactionSummary->transactionDate = !empty($record->transactionTime) ? new \DateTime($record->transactionTime) : '';
                $transactionSummary->authCode = $record->approvalCode;
                $transactionSummary->hostTimeout = $record->hostTimeOut;
                $transactionSummary->cardEntryMethod = $record->cardAcquisition;
                $transactionSummary->status = $record->responseCode;
                $summaryResponse->transactions->add($transactionSummary);
            }
            if ($summaryResponse->summaryType == SummaryType::APPROVED) {
                $this->reportResult->approved[] = $summaryResponse;
            } elseif ($summaryResponse->summaryType == SummaryType::PENDING) {
                $this->reportResult->pending[] = $summaryResponse;
            } elseif ($summaryResponse->summaryType == SummaryType::DECLINED) {
                $this->reportResult->decline[] = $summaryResponse;
            }
        }
    }

    private function mapSummaryType(string $safType) : string
    {
        switch ($safType) {
            case self::AUTHORIZED:
                return SummaryType::APPROVED;
            case self::PENDING:
                return SummaryType::PENDING;
            default:
                return SummaryType::DECLINED;
        }
    }
}