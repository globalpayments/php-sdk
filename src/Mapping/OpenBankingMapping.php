<?php

namespace GlobalPayments\Api\Mapping;

use GlobalPayments\Api\Entities\BankPaymentResponse;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Utils\StringUtils;

class OpenBankingMapping
{
    public static function mapResponse($response)
    {
        $response = json_decode($response);
        $transaction = new Transaction();

        if (empty($response)) {
            return $transaction;
        }

        $transaction->transactionId = $response->ob_trans_id;
        $transaction->paymentMethodType = PaymentMethodType::BANK_PAYMENT;
        $transaction->orderId = $response->order->id;
        $transaction->responseMessage = $response->status;

        $obResponse = new BankPaymentResponse();
        $obResponse->redirectUrl = $response->redirect_url;
        $obResponse->paymentStatus = $response->status;
        $obResponse->id = $response->ob_trans_id;
        $transaction->bankPaymentResponse = $obResponse;

        return $transaction;
    }

    public static function mapReportResponse($response, $reportType)
    {
        $response = json_decode($response);
        $report = null;
        switch ($reportType) {
            case ReportType::FIND_BANK_PAYMENT:
                $report = self::setPagingInfo($response);
                foreach ($response->payments as $transaction) {
                    array_push($report->result, self::mapTransactionSummary($transaction));
                }
                break;
            default:
                break;

        }

        return $report;
    }

    private static function setPagingInfo($response)
    {
        $report = new PagedResult();
        $report->totalRecordCount = !empty($response->total_number_of_records) ?
            $response->total_number_of_records : null;
        $report->pageSize = !empty($response->max_page_size) ? $response->max_page_size :  null;
        $report->page = !empty($response->page_number) ? $response->page_number :  null;

        return $report;
    }

    public static function mapTransactionSummary($response)
    {
        $summary = new TransactionSummary();
        $summary->transactionId = $response->ob_trans_id;
        $summary->orderId = $response->order_id;
        $summary->amount = StringUtils::toAmount($response->amount);
        $summary->currency = $response->currency;
        $summary->transactionStatus = $response->status;
        $summary->paymentType = PaymentMethodName::BANK_PAYMENT;
        $summary->transactionDate = !empty($response->created_on) ?
            new \DateTime($response->created_on) : '';

        $bankPaymentData = new BankPaymentResponse();
        $bankPaymentData->id = $response->ob_trans_id;
        $bankPaymentData->type = $response->payment_type;
        $bankPaymentData->tokenRequestId = $response->token_request_id;
        $bankPaymentData->iban = isset($response->dest_iban) ? $response->dest_iban : null;
        $bankPaymentData->accountName = isset($response->dest_account_name) ? $response->dest_account_name : null;
        $bankPaymentData->accountNumber = isset($response->dest_account_number) ? $response->dest_account_number : null;
        $bankPaymentData->sortCode = isset($response->dest_sort_code) ? $response->dest_sort_code : null;
        $bankPaymentData->paymentStatus = $response->status;
        $summary->bankPaymentResponse = $bankPaymentData;

        return $summary;
    }
}