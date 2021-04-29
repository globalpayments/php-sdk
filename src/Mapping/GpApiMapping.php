<?php


namespace GlobalPayments\Api\Mapping;


use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\Entities\Reporting\ActionSummary;
use GlobalPayments\Api\Entities\Reporting\DepositSummary;
use GlobalPayments\Api\Entities\Reporting\DisputeSummary;
use GlobalPayments\Api\Entities\Reporting\StoredPaymentMethodSummary;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Utils\StringUtils;

class GpApiMapping
{
    /**
     * Map a reponse to a Transaction object for further chaining
     *
     * @param $response Object
     */
    public static function mapResponse($response)
    {
        $transaction = new Transaction();

        if (empty($response)) {
            return $transaction;
        }

        $transaction->transactionId = $response->id;
        $transaction->balanceAmount = !empty($response->amount) ? StringUtils::toAmount($response->amount) : null;
        $transaction->timestamp = !empty($response->time_created) ? $response->time_created : '';
        $transaction->responseMessage = $response->status;
        $transaction->referenceNumber = !empty($response->reference) ? $response->reference : null;
        $batchSummary = new BatchSummary();
        $batchSummary->batchReference = !empty($response->batch_id) ? $response->batch_id : null;
        $batchSummary->totalAmount = !empty($response->amount) ? $response->amount : null;
        $batchSummary->transactionCount = !empty($response->transaction_count) ? $response->transaction_count : null;
        $transaction->batchSummary = $batchSummary;
        $transaction->responseCode = $response->action->result_code;
        $transaction->token = substr($response->id, 0, 4) === PaymentMethod::PAYMENT_METHOD_TOKEN_PREFIX ?
            $response->id : null;
        $transaction->clientTransactionId = !empty($response->reference) ? $response->reference : null;

        if (!empty($response->payment_method)) {
            $transaction->authorizationCode = $response->payment_method->result;
            if (!empty($response->payment_method->id)) {
                $transaction->token = $response->payment_method->id;
            }
            if (!empty($response->payment_method->card)) {
                $card = $response->payment_method->card;
                $transaction->cardLast4 = !empty($card->masked_number_last4) ?
                    $card->masked_number_last4 : null;
                $transaction->cardType = !empty($card->brand) ? $card->brand : null;
                $transaction->cvnResponseCode = !empty($card->cvv) ? $card->cvv : null;
            }
        }
        if (!empty($response->card)) {
            $transaction->cardNumber = !empty($response->card->number) ? $response->card->number : null;
            $transaction->cardType = !empty($response->card->brand) ? $response->card->brand : '';
            $transaction->cardExpMonth = $response->card->expiry_month;
            $transaction->cardExpYear = $response->card->expiry_year;
            $transaction->cvnResponseCode = !empty($response->card->cvv) ? $response->card->cvv : null;
        }

        return $transaction;
    }

    /**
     * @param $response
     * @param string $reportType
     */
    public static function mapReportResponse($response, $reportType)
    {
        switch ($reportType) {
            case ReportType::TRANSACTION_DETAIL:
                $report = self::mapTransactionSummary($response);
                break;
            case ReportType::FIND_TRANSACTIONS_PAGED:
            case ReportType::FIND_SETTLEMENT_TRANSACTIONS_PAGED:
                $report = self::setPagingInfo($response);
                foreach ($response->transactions as $transaction) {
                    array_push($report->result, self::mapTransactionSummary($transaction));
                }
                break;
            case ReportType::DEPOSIT_DETAIL:
                $report = self::mapDepositSummary($response);
                break;
            case ReportType::FIND_DEPOSITS_PAGED:
                $report = self::setPagingInfo($response);
                foreach ($response->deposits as $deposit) {
                    array_push($report->result, self::mapDepositSummary($deposit));
                }
                break;
            case ReportType::DISPUTE_DETAIL:
            case ReportType::SETTLEMENT_DISPUTE_DETAIL:
                $report = self::mapDisputeSummary($response);
                break;
            case ReportType::FIND_DISPUTES_PAGED:
            case ReportType::FIND_SETTLEMENT_DISPUTES_PAGED:
                $report = self::setPagingInfo($response);
                foreach ($response->disputes as $dispute) {
                    array_push($report->result, self::mapDisputeSummary($dispute));
                }
                break;
            case ReportType::FIND_STORED_PAYMENT_METHODS_PAGED:
                $report = self::setPagingInfo($response);
                foreach ($response->payment_methods as $spm) {
                    array_push($report->result, self::mapStoredPaymentMethodSummary($spm));
                }
                break;
            case ReportType::STORED_PAYMENT_METHOD_DETAIL:
                $report = self::mapStoredPaymentMethodSummary($response);
                break;
            case ReportType::ACTION_DETAIL:
                $report = self::mapActionsSummary($response);
                break;
            case ReportType::FIND_ACTIONS_PAGED:
                $report = self::setPagingInfo($response);
                foreach ($response->actions as $action) {
                    array_push($report->result, self::mapActionsSummary($action));
                }
                break;
            default:
                throw new ApiException("Report type not supported!");
        }

        return $report;
    }
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
        $summary->amount = StringUtils::toAmount($response->amount);
        $summary->currency = $response->currency;
        $summary->referenceNumber = $response->reference;
        $summary->clientTransactionId = $response->reference;
        $summary->transactionLocalDate = !empty($response->time_created_reference) ?
            new \DateTime($response->time_created_reference) : '';
        $summary->batchSequenceNumber = $response->batch_id;
        $summary->country = !empty($response->country) ? $response->country : null;
        // $summary->unknown = $response->action_create_id;
        $summary->originalTransactionId = !empty($response->parent_resource_id) ? $response->parent_resource_id : null;
        $summary->depositReference = !empty($response->deposit_id) ? $response->deposit_id : '';
        $summary->depositStatus = !empty($response->deposit_status) ? $response->deposit_status : '';
        $summary->depositTimeCreated = !empty($response->deposit_time_created) ?
            new \DateTime($response->deposit_time_created) : '';
        $summary->batchCloseDate = !empty($response->batch_time_created) ? new \DateTime($response->batch_time_created) : '';
        if (isset($response->system)) {
            $system = $response->system;
            $summary->merchantId = $system->mid;
            $summary->merchantHierarchy = $system->hierarchy;
            $summary->merchantName = $system->name;
            $summary->merchantDbaName = $system->dba;
        }
        if (isset($response->payment_method)) {
            $paymentMethod = $response->payment_method;
            $summary->gatewayResponseMessage = isset($paymentMethod->message) ? $paymentMethod->message : null;
            $summary->entryMode = isset($paymentMethod->entry_mode) ? $paymentMethod->entry_mode : null;
            $summary->cardHolderName = isset($paymentMethod->name) ? $paymentMethod->name : '';
            if (isset($response->payment_method->card)) {
                $card = $response->payment_method->card;
                $summary->aquirerReferenceNumber = isset($card->arn) ? $card->arn : null;
                $summary->maskedCardNumber = isset($card->masked_number_first6last4) ?
                    $card->masked_number_first6last4 : null;
            } elseif (isset($response->payment_method->digital_wallet)) {
                $card = $response->payment_method->digital_wallet;
                $summary->maskedCardNumber = isset($card->masked_token_first6last4) ?
                    $card->masked_token_first6last4 : null;
            }
            if (!empty($card)) {
                $summary->cardType = isset($card->brand) ? $card->brand : null;
                $summary->authCode = isset($card->authcode) ? $card->authcode : null;
                $summary->brandReference = isset($card->brand_reference) ? $card->brand_reference : null;
            }
        }

        return $summary;
    }

    /**
     * @param Object $response
     *
     * @return DepositSummary
     */
    public static function mapDepositSummary($response)
    {
        $summary = new DepositSummary();
        $summary->depositId = $response->id;
        $summary->depositDate = new \DateTime($response->time_created);
        $summary->status = $response->status;
        $summary->type = $response->funding_type;
        $summary->amount = StringUtils::toAmount($response->amount);
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
            $summary->salesTotalAmount = isset($sales->amount) ? StringUtils::toAmount($sales->amount) : 0;
        }

        if (isset($response->refunds)) {
            $refunds = $response->refunds;
            $summary->refundsTotalCount = isset($refunds->count) ? $refunds->count : 0;
            $summary->refundsTotalAmount = isset($refunds->amount) ? StringUtils::toAmount($refunds->amount) : 0;
        }

        if (isset($response->disputes)) {
            $disputes = $response->disputes;
            $summary->chargebackTotalCount = isset($disputes->chargebacks->count) ? $disputes->chargebacks->count : 0;
            $summary->chargebackTotalAmount = isset($disputes->chargebacks->amount) ?
                StringUtils::toAmount($disputes->chargebacks->amount) : 0;

            $summary->adjustmentTotalCount = isset($disputes->reversals->count) ? $disputes->reversals->count : 0;
            $summary->adjustmentTotalAmount = isset($disputes->reversals->amount) ?
                StringUtils::toAmount($disputes->reversals->amount) : 0;
        }

        $summary->feesTotalAmount = isset($response->fees->amount) ? StringUtils::toAmount($response->fees->amount) : 0;

        return $summary;
    }

    /**
     * @param Object $response
     *
     * @return DisputeSummary
     */
    public static function mapDisputeSummary($response)
    {
        $summary = new DisputeSummary();
        $summary->caseId = $response->id;
        $summary->caseIdTime = !empty($response->time_created) ? new \DateTime($response->time_created) :
            (!empty($response->stage_time_created) ? $response->stage_time_created : '');
        $summary->caseStatus = $response->status;
        $summary->caseStage = $response->stage;
        $summary->caseAmount = StringUtils::toAmount($response->amount);
        $summary->caseCurrency = $response->currency;
        if (isset($response->system)) {
            $system = $response->system;
            $summary->caseMerchantId = $system->mid;
            $summary->merchantHierarchy = $system->hierarchy;
            $summary->merchantName = !empty($system->name) ? $system->name : null;
        }
        if (
            isset($response->payment_method) &&
            isset($response->payment_method->card)
        ) {
            $card = $response->payment_method->card;
            $summary->transactionMaskedCardNumber = $card->number;
        }
        if (isset($response->transaction)) {
            $summary->transactionTime = $response->transaction->time_created;
            $summary->transactionType = $response->transaction->type;
            $summary->transactionAmount = StringUtils::toAmount($response->transaction->amount);
            $summary->transactionCurrency = $response->transaction->currency;
            $summary->transactionReferenceNumber = $response->transaction->reference;
            if (isset($response->transaction->payment_method->card)) {
                $card = $response->transaction->payment_method->card;
                $summary->transactionMaskedCardNumber = !empty($card->masked_number_first6last4) ?
                    $card->masked_number_first6last4 : '';
                $summary->transactionAuthCode = $card->authcode;
            }
        }

        if (!empty($card)) {
            $summary->transactionARN = $card->arn;
            $summary->transactionCardType = $card->brand;
        }
        $summary->reasonCode = $response->reason_code;
        $summary->reason = $response->reason_description;
        $summary->respondByDate = new \DateTime($response->time_to_respond_by);
        $summary->result = $response->result;
        $summary->lastAdjustmentAmount = StringUtils::toAmount($response->last_adjustment_amount);
        $summary->lastAdjustmentCurrency = $response->last_adjustment_currency;
        $summary->lastAdjustmentFunding = $response->last_adjustment_funding;

        return $summary;
    }

    /**
     * Map the store payment methods report response
     *
     * @param $response
     *
     * @return StoredPaymentMethodSummary
     */
    public static function mapStoredPaymentMethodSummary($response)
    {
        $summary = new StoredPaymentMethodSummary();
        $summary->paymentMethodId = $response->id;
        $summary->timeCreated = !empty($response->time_created) ? new \DateTime($response->time_created) : '';
        $summary->status = !empty($response->status) ? $response->status : '';
        $summary->reference = !empty($response->reference) ? $response->reference : '';
        $summary->cardHolderName = !empty($response->name) ? $response->name : '';
        if (!empty($response->card)) {
            $card = $response->card;
            $summary->cardType = !empty($card->brand) ? $card->brand : '';
            $summary->cardNumberLastFour = !empty($card->number_last4) ? $card->number_last4 : '';
            $summary->cardExpMonth = !empty($card->expiry_month) ? $card->expiry_month : '';
            $summary->cardExpYear = !empty($card->expiry_year) ? $card->expiry_year : '';
        }

        return $summary;
    }

    public static function mapActionsSummary($response)
    {
        $summary = new ActionSummary();

        $summary->id = $response->id;
        $summary->timeCreated = !empty($response->time_created) ? new \DateTime($response->time_created) : null;
        $summary->type = !empty($response->type) ? $response->type : null;
        $summary->resource = !empty($response->resource) ? $response->resource : null;
        $summary->resourceId = !empty($response->resource_id) ? $response->resource_id : null;
        $summary->resourceStatus = !empty($response->resource_status) ? $response->resource_status : null;
        $summary->version = !empty($response->version) ? $response->version : null;
        $summary->httpResponseCode = !empty($response->http_response_code) ? $response->http_response_code : null;
        $summary->responseCode = !empty($response->response_code) ? $response->response_code : null;
        $summary->appId = !empty($response->app_id) ? $response->app_id : null;
        $summary->appName = !empty($response->app_name) ? $response->app_name : null;
        $summary->merchantName = !empty($response->merchant_name) ? $response->merchant_name : null;
        $summary->accountName = !empty($response->account_name) ? $response->account_name : null;
        $summary->accountId = !empty($response->account_id) ? $response->account_id : null;

        return $summary;
    }

    /**
     * @param Object $response
     */
    public static function mapResponseSecure3D($response)
    {
        $transaction = new Transaction();
        $threeDSecure = new ThreeDSecure();
        $threeDSecure->serverTransactionId = !empty($response->id) ? $response->id :
            (!empty($response->three_ds->server_trans_ref) ? $response->three_ds->server_trans_ref : '');
        if (!empty($response->three_ds->message_version)) {
            $messageVersion = $response->three_ds->message_version;
            switch (substr($messageVersion, 0, 2)) {
                case '1.':
                    $version = Secure3dVersion::ONE;
                    break;
                case '2.':
                    $version = Secure3dVersion::TWO;
                    break;
                default:
                    $version = Secure3dVersion::ANY;
            }
            $threeDSecure->messageVersion = $messageVersion;
            $threeDSecure->setVersion($version);
        }

        $threeDSecure->directoryServerStartVersion = !empty($response->three_ds->ds_protocol_version_start) ?
            $response->three_ds->ds_protocol_version_start : '';
        $threeDSecure->directoryServerEndVersion = !empty($response->three_ds->ds_protocol_version_end) ?
            $response->three_ds->ds_protocol_version_end : '';
        $threeDSecure->acsStartVersion = !empty($response->three_ds->acs_protocol_version_start) ?
            $response->three_ds->acs_protocol_version_start : '';
        $threeDSecure->acsEndVersion = !empty($response->three_ds->acs_protocol_version_end) ?
            $response->three_ds->acs_protocol_version_end : '';
        $threeDSecure->enrolled = !empty($response->three_ds->enrolled_status) ?
            $response->three_ds->enrolled_status : 'NOT_ENROLLED';
        $threeDSecure->eci = !empty($response->three_ds->eci) ? $response->three_ds->eci : '';
        $threeDSecure->acsInfoIndicator = !empty($response->three_ds->acs_info_indicator) ?
            $response->three_ds->acs_info_indicator : null;
        $threeDSecure->challengeMandated = !empty($response->three_ds->challenge_status) ?
            ($response->three_ds->challenge_status == 'MANDATED') : false;
        $threeDSecure->payerAuthenticationRequest = !empty($response->three_ds->method_data->encoded_method_data) ?
            $response->three_ds->method_data->encoded_method_data : null;
        $threeDSecure->issuerAcsUrl = !empty($response->three_ds->method_url) ? $response->three_ds->method_url : '';
        $threeDSecure->challengeValue = !empty($response->three_ds->challenge_value) ?
            $response->three_ds->challenge_value : '';
        if (!empty($response->three_ds->acs_challenge_request_url) && $threeDSecure->challengeMandated === true) {
            $threeDSecure->issuerAcsUrl = !empty($response->three_ds->acs_challenge_request_url) ?
                $response->three_ds->acs_challenge_request_url : null;
            $threeDSecure->payerAuthenticationRequest = !empty($response->three_ds->challenge_value) ?
                $response->three_ds->challenge_value : '';
        }
        $threeDSecure->setCurrency($response->currency);
        $threeDSecure->setAmount(StringUtils::toAmount($response->amount));
        $threeDSecure->status = $response->status;
        $threeDSecure->authenticationValue = !empty($response->three_ds->authenticationValue) ?
            $response->three_ds->authenticationValue : '';
        $threeDSecure->directoryServerTransactionId = !empty($response->three_ds->ds_trans_ref) ?
            $response->three_ds->ds_trans_ref : '';
        $threeDSecure->acsTransactionId = !empty($response->three_ds->acs_trans_ref) ?
            $response->three_ds->acs_trans_ref : '';
        $threeDSecure->statusReason = !empty($response->three_ds->status_reason) ?
            $response->three_ds->status_reason : '';
        $threeDSecure->messageCategory = !empty($response->three_ds->message_category) ?
            $response->three_ds->message_category : '';
        $threeDSecure->messageType = !empty($response->three_ds->message_type) ?
            $response->three_ds->message_type : '';
        $threeDSecure->sessionDataFieldName = !empty($response->three_ds->session_data_field_name) ?
            $response->three_ds->session_data_field_name : '';
        $threeDSecure->challengeReturnUrl = !empty($response->notifications->challenge_return_url) ?
            $response->notifications->challenge_return_url : '';

        $transaction->threeDSecure = $threeDSecure;

        return $transaction;
    }

    private static function setPagingInfo($response)
    {
        $pageInfo = new PagedResult();
        $pageInfo->totalRecordCount = !empty($response->total_count) ? $response->total_count :
            (!empty($response->total_record_count) ? $response->total_record_count : null);
        $pageInfo->pageSize = !empty($response->paging->page_size) ? $response->paging->page_size :  null;
        $pageInfo->page = !empty($response->paging->page) ? $response->paging->page :  null;
        $pageInfo->order = !empty($response->paging->order) ? $response->paging->order :  null;
        $pageInfo->oderBy = !empty($response->paging->order_by) ? $response->paging->order_by :  null;

        return $pageInfo;
    }
}