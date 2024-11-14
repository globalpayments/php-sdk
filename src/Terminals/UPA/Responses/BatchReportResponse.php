<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Card;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\TransactionList;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Terminals\DeviceResponse;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Utils\StringUtils;

class BatchReportResponse extends UpaResponseHandler
{
    public ?string $merchantName;
    public BatchRecordResponse $batchRecord;

    /**
     * BatchReportResponse constructor.
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
        $this->ecrId = $firstDataNode->EcrId ?? null;
        $secondDataNode = $firstDataNode->data ?? null;
        $this->merchantName = $secondDataNode->merchantName ?? null;
        $this->multipleMessage = $secondDataNode->multipleMessage ?? null;
        $rawBatchRecord = $secondDataNode->batchRecord ?? null;
        if (!empty($rawBatchRecord)) {
            $batchRecordResponse = $this->mapBatchRecordInfo($rawBatchRecord);
            if ($this->command == UpaMessageId::GET_BATCH_REPORT) {
                $this->mapBatchTransactions($batchRecordResponse, $rawBatchRecord->batchTransactions);

            } elseif ($this->command == UpaMessageId::GET_BATCH_DETAILS) {
                $this->mapTransactionDetails($batchRecordResponse, $rawBatchRecord->batchDetailRecords);
            }
            $this->batchRecord = $batchRecordResponse;
        }
    }

    private function mapBatchRecordInfo($rawBatchRecord): BatchRecordResponse
    {
        $batchRecordResponse = new BatchRecordResponse();
        $batchRecordResponse->batchId = $rawBatchRecord->batchId;
        $batchRecordResponse->batchSeqNbr = $rawBatchRecord->batchSeqNbr;
        $batchRecordResponse->batchStatus = $rawBatchRecord->batchStatus;
        $batchRecordResponse->openUtcDateTime = $rawBatchRecord->openUtcDateTime;
        $batchRecordResponse->closeUtcDateTime = $rawBatchRecord->closeUtcDateTime ?? null;
        $batchRecordResponse->openTnxId = $rawBatchRecord->openTnxId;
        $this->extractedTotals($rawBatchRecord, $batchRecordResponse);

        return $batchRecordResponse;
    }
    private function mapTransactionDetails(BatchRecordResponse &$batchRecordResponse, $transactions): void
    {
        $batchRecordResponse->transactionDetails = new TransactionList();
        foreach ($transactions as $transaction) {
            $transactionSummary = new TransactionSummary();
            $transactionSummary->transactionDate = !empty($transaction->transactionTime) ?
                new \DateTime($transaction->transactionTime) : '';
            $transactionSummary->authCode = $transaction->approvalCode ?? null;
            $transactionSummary->authorizedAmount = $transaction->authorizedAmount;
            $transactionSummary->cardEntryMethod = $transaction->cardAcquisition ?? null;
            $transactionSummary->cardType = $transaction->cardType;
            $transactionSummary->maskedCardNumber = $transaction->maskedPan ?? null;
            $transactionSummary->cardDetails = new Card();
            $transactionSummary->cardDetails->brand = $transaction->cardType ?? null;
            $transactionSummary->cardDetails->maskedCardNumber = $transaction->maskedPan ?? null;
            $transactionSummary->referenceNumber = $transaction->referenceNumber;
            $transactionSummary->issuerTransactionId = $transaction->gatewayTxnId;
            $transactionSummary->clerkId = $transaction->clerkId ?? null;
            $transactionSummary->amount = $transaction->requestedAmount;
            $transactionSummary->gatewayResponseCode = $transaction->responseCode;
            $transactionSummary->gatewayResponseMessage = $transaction->responseText;
            $transactionSummary->transactionStatus = $transaction->transactionStatus;
            $transactionSummary->transactionType = $transaction->transactionType;
            $transactionSummary->gratuityAmount = $transaction->tipAmount ?? null;
            $transactionSummary->settlementAmount = $transaction->settleAmount ?? null;
            $transactionSummary->taxAmount = $transaction->taxAmount ?? null;
            $transactionSummary->cardSwiped = $transaction->cardSwiped ?? null;
            $batchRecordResponse->transactionDetails->add($transactionSummary);
        }
    }

    private function mapBatchTransactions(BatchRecordResponse &$batchRecordResponse, $batchTransactions): void
    {
        $batchRecordResponse->batchTransactions = new BatchTransactionList();
        foreach ($batchTransactions as $transaction) {
            $batchTransaction = new BatchTransaction();
            $batchTransaction->cardType = $transaction->cardType;
            $this->extractedTotals($transaction, $batchTransaction);
            $batchRecordResponse->batchTransactions->add($batchTransaction);
        }
    }

    /**
     * @param $rawBatchRecord
     * @param BatchRecordResponse $batchRecordResponse
     * @return void
     */
    private function extractedTotals($rawBatchRecord, BatchRecordResponse|BatchTransaction $batchRecordResponse): void
    {
        $batchRecordResponse->totalAmount = $rawBatchRecord->totalAmount;
        $batchRecordResponse->totalCount = $rawBatchRecord->totalCnt ?? null;
        $batchRecordResponse->creditAmt = $rawBatchRecord->creditAmt ?? null;
        $batchRecordResponse->creditCnt = $rawBatchRecord->creditCnt ?? null;
        $batchRecordResponse->debitAmt = $rawBatchRecord->debitAmt ?? null;
        $batchRecordResponse->debitCnt = $rawBatchRecord->debitCnt ?? null;
        $batchRecordResponse->saleCnt = $rawBatchRecord->saleCnt ?? null;
        $batchRecordResponse->saleAmt = $rawBatchRecord->saleAmt ?? null;
        $batchRecordResponse->returnCnt = $rawBatchRecord->returnCnt ?? null;
        $batchRecordResponse->returnAmt = $rawBatchRecord->returnAmt ?? null;
        $batchRecordResponse->totalGratuityAmt = $rawBatchRecord->totalGratuityAmt ?? null;
    }
}