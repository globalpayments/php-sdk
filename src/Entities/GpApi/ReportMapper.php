<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Entities\Reporting\DepositSummary;
use GlobalPayments\Api\Entities\Reporting\DisputeSummary;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Utils\StringUtils;

class ReportMapper
{
    /**
     * @param $response
     * @return TransactionSummary
     * @throws \Exception
     */
    public static function mapTransactionSummary($response)
    {
        $summary = new TransactionSummary();
        $summary->transactionId = isset($response->id) ? $response->id : null;
        $summary->transactionDate = new \DateTime($response->time_created);
        $summary->transactionStatus = $response->status;
        $summary->transactionType = $response->type;
        $summary->channel = !empty($response->channel) ? $response->channel : null;
        $summary->amount = $response->amount;
        $summary->currency = $response->currency;
        $summary->referenceNumber = $response->reference;
        $summary->clientTransactionId = $response->reference;
        // $summary->unknown = $response->time_created_reference;
        $summary->batchSequenceNumber = $response->batch_id;
        $summary->country = !empty($response->country) ? $response->country : null;
        // $summary->unknown = $response->action_create_id;
        $summary->originalTransactionId = !empty($response->parent_resource_id) ? $response->parent_resource_id : null;

        if (isset($response->payment_method)) {
            $summary->gatewayResponseMessage = isset($response->payment_method->message) ?
                $response->payment_method->message : null;
            $summary->entryMode = isset($response->payment_method->entry_mode) ?
                $response->payment_method->entry_mode : null;
            // $summary->unknown = isset($response->payment_method) ? $response->payment_method->name : null;

            if (isset($response->payment_method->card)) {
                $card = $response->payment_method->card;
                $summary->cardType = isset($card->brand) ?
                    $card->brand : null;
                $summary->authCode = isset($card->authcode) ?
                    $card->authcode : null;
                $summary->brandReference = isset($card->brand_reference) ?
                    $card->brand_reference : null;
                $summary->aquirerReferenceNumber = isset($card->arn) ?
                    $card->arn : null;
                $summary->maskedCardNumber = isset($card->masked_number_first6last4) ?
                    $card->masked_number_first6last4 : null;
            }
        }
        return $summary;
    }

    public static function mapDepositSummary($response)
    {
        $summary = new DepositSummary();
        $summary->depositId = $response->id;
        $summary->depositDate = new \DateTime($response->time_created);
        $summary->status = $response->status;
        $summary->type = $response->funding_type;
        $summary->amount = $response->amount;
        $summary->currency = $response->currency;

        if (isset($response->system)) {
            $system = $response->system;
            $summary->merchantNumber = $system->mid;
            $summary->merchantHierarchy = $system->hierarchy;
            $summary->merchantName = $system->name;
            $summary->merchantDbaName = $system->dba;
        }

        if (isset($response->sales)) {
            $sales = $response->sales;
            $summary->salesTotalCount = isset($sales->count) ? $sales->count : 0;
            $summary->salesTotalAmount = isset($sales->amount) ? $sales->amount : 0;
        }

        if (isset($response->refunds)) {
            $refunds = $response->refunds;
            $summary->refundsTotalCount = isset($refunds->count) ? $refunds->count : 0;
            $summary->refundsTotalAmount = isset($refunds->amount) ? $refunds->amount : 0;
        }

        if (isset($response->disputes)) {
            $disputes = $response->disputes;
            $summary->chargebackTotalCount = isset($disputes->chargebacks->count) ? $disputes->chargebacks->count : 0;
            $summary->chargebackTotalAmount = isset($disputes->chargebacks->amount) ?
                $disputes->chargebacks->amount : 0;

            $summary->adjustmentTotalCount = isset($disputes->reversals->count) ? $disputes->reversals->count : 0;
            $summary->adjustmentTotalAmount = isset($disputes->reversals->amount) ? $disputes->reversals->amount : 0;
        }

        $summary->feesTotalAmount = isset($response->fees->amount) ? $response->fees->amount : 0;
        return $summary;
    }

    public static function mapDisputeSummary($response)
    {
        $summary = new DisputeSummary();
        $summary->caseId = $response->id;
        $summary->caseIdTime = !empty($response->time_created) ? new \DateTime($response->time_created) : '';
        $summary->caseStatus = $response->status;
        $summary->caseStage = $response->stage;
        $summary->caseAmount = $response->amount;
        $summary->caseCurrency = $response->currency;
        if (isset($response->system)) {
            $system = $response->system;
            $summary->caseMerchantId = $system->mid;
            $summary->merchantHierarchy = $system->hierarchy;
        }
        if (
            isset($response->payment_method) &&
            isset($response->payment_method->card)
        ) {
            $card = $response->payment_method->card;
            $summary->transactionMaskedCardNumber = $card->number;
            $summary->transactionARN = $card->arn;
            $summary->transactionCardType = $card->brand;
        }
        $summary->reasonCode = $response->reason_code;
        $summary->reason = $response->reason_description;
        $summary->respondByDate = new \DateTime($response->time_to_respond_by);
        $summary->result = $response->result;
        $summary->lastAdjustmentAmount = $response->last_adjustment_amount;
        $summary->lastAdjustmentCurrency = $response->last_adjustment_currency;
        $summary->lastAdjustmentFunding = $response->last_adjustment_funding;

        return $summary;
    }
}