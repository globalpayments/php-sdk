<?php

namespace GlobalPayments\Api\Mapping;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Reporting\{CheckData, TransactionSummary};
use GlobalPayments\Api\Utils\StringUtils;

class TransactionApiMapping
{
    /**
     * Map a reponse to a Transaction object for further chaining
     *
     * @param Object $response
     * @return Transaction
     */
    public static function mapResponse($response)
    {
        $transaction = new Transaction();

        if (empty($response)) {
            return $transaction;
        }

        $transaction->clientTransactionId = !empty($response->creditauth_id)
            ? $response->creditauth_id : null;
        $transaction->checkSaleId = !empty($response->checksale_id)
            ? $response->checksale_id : null;
        $transaction->checkRefundId = !empty($response->checkrefund_id)
            ? $response->checkrefund_id : null;
        if (!empty($response->creditsale_id))
            $transaction->transactionId =  $response->creditsale_id;
        if (!empty($response->creditreturn_id))
            $transaction->transactionId =  $response->creditreturn_id;

        $transaction->responseMessage = !empty($response->processor_response)
            ? $response->processor_response : null;
        $transaction->responseCode = !empty($response->status)
            ? $response->status : null;
        $transaction->authorizationCode = !empty($response->approval_code)
            ? $response->approval_code : null;
        $transaction->avsResponseCode = !empty($response->avs_response)
            ? $response->avs_response : null;
        $transaction->avsResponseMessage = !empty($response->avs_response_description)
            ? $response->avs_response_description : null;
        $transaction->cardSecurityResponse = !empty($response->cardsecurity_response)
            ? $response->cardsecurity_response : null;

        if (!empty($response->card)) {
            $card = $response->card;
            $transaction->maskedCardNumber = !empty($card->masked_card_number) ? $card->masked_card_number : "";
            $transaction->cardholderName = !empty($card->cardholder_name) ? $card->cardholder_name : "";
            $transaction->cardExpMonth = !empty($card->expiry_month) ? $card->expiry_month : '';
            $transaction->cardExpYear = !empty($card->expiry_year) ? $card->expiry_year : '';
            $transaction->token = !empty($card->token) ? $card->token : '';
            $transaction->cardType = !empty($card->type) ? $card->type : '';
        }

        if (!empty($response->check)) {
            $check = $response->check;
            $transaction->token = !empty($check->token) ? $check->token : '';
        }

        $transaction->referenceNumber = !empty($response->reference_id)
            ? $response->reference_id : null;
        $transaction->balanceAmount = !empty($response->amount)
            ? StringUtils::toAmount($response->amount) : null;

        return $transaction;
    }

    /**
     * @param Object $response
     * @param string $reportType
     */
    public static function mapReportResponse($response, $reportType)
    {
        switch ($reportType) {
            case ReportType::TRANSACTION_DETAIL || ReportType::FIND_TRANSACTIONS:
                $report = self::mapTransactionSummary($response);
                break;
            default:
                throw new ApiException("Report type not supported!");
        }

        return $report;
    }

    /**
     * @param Object $response
     * @return TransactionSummary
     * @throws \Exception
     */
    public static function mapTransactionSummary($response)
    {
        $summary = new TransactionSummary();

        if (!empty($response->creditreturn_id)) {
            $summary->transactionId =  $response->creditreturn_id;
        }
        if (!empty($response->creditsale_id)) {
            $summary->transactionId = $response->creditsale_id;
        }
        if (!empty($response->checksale_id)) {
            $summary->transactionId =  $response->checksale_id;
        }
        if (!empty($response->checkrefund_id)) {
            $summary->transactionId =  $response->checkrefund_id;
        }

        $summary->transactionStatus = isset($response->status) ? $response->status : null;
        $summary->authCode = isset($response->approval_code)
            ? $response->approval_code : null;
        $summary->issuerResponseMessage = !empty($response->cardsecurity_response)
            ? $response->cardsecurity_response : null;

        if (isset($response->check)) {
            $checkSummary = new CheckData();
            $checkSummary->accountInfo = isset($response->check->account_type)
                ? $response->check->account_type : null;
            $checkSummary->accountNumber = isset($response->check->account_number)
                ? $response->check->account_number : null;
            $checkSummary->bankNumber = isset($response->check->bank_number)
                ? $response->check->bank_number : null;
            $checkSummary->branchTransitNumber = isset($response->check->branch_transit_number)
                ? $response->check->branch_transit_number : null;
            $checkSummary->bsbNumber = isset($response->check->bsb_number)
                ? $response->check->bsb_number : null;
            $checkSummary->maskedCardNumber = isset($response->check->masked_card_number)
                ? $response->check->masked_card_number : null;
            $checkSummary->financialInstitutionNumber = isset($response->check->financial_institution_number)
                ? $response->check->financial_institution_number : null;
            $checkSummary->checkNumber = isset($response->check->check_number)
                ? $response->check->check_number : null;
            $checkSummary->routingNumber = isset($response->check->routing_number)
                ? $response->check->routing_number : null;

            $summary->checkData = $checkSummary;
        }

        if (isset($response->check)) {
            $summary->maskedCardNumber = isset($response->card->masked_card_number)
                ? $response->card->masked_card_number : null;
            $summary->cardType = isset($response->card->type) ? $response->card->type : null;
        }

        if (isset($response->payment)) {
            $summary->amount = isset($response->payment->amount)
                ? $response->payment->amount : null;
            $summary->invoiceNumber = isset($response->payment->invoice_number)
                ? $response->payment->invoice_number : null;
            $summary->paymentType = isset($response->payment->type)
                ? $response->payment->type : null;
        }

        if (isset($response->transaction)) {
            $summary->batchSequenceNumber = isset($response->transaction->batch_number)
                ? $response->transaction->batch_number : null;
            $summary->paymentPurposeCode = isset($response->transaction->payment_purpose_code)
                ? $response->transaction->payment_purpose_code : null;
        }

        $summary->referenceNumber = isset($response->reference_id) ? $response->reference_id : null;

        return $summary;
    }
}
