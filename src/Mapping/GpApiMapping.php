<?php

namespace GlobalPayments\Api\Mapping;

use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
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
use GlobalPayments\Api\Entities\MessageExtension;

class GpApiMapping
{
    const DCC_RESPONSE = 'RATE_LOOKUP';

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
        $transaction->fingerprint = !empty($response->fingerprint) ? $response->fingerprint : null;
        $transaction->fingerprintIndicator = !empty($response->fingerprint_presence_indicator) ?
            $response->fingerprint_presence_indicator : null;
        if (!empty($response->payment_method)) {
            $transaction->authorizationCode = $response->payment_method->result;
            if (!empty($response->payment_method->id)) {
                $transaction->token = $response->payment_method->id;
            }
            $transaction->fingerprint = !empty($response->payment_method->fingerprint) ?
                $response->payment_method->fingerprint : null;
            $transaction->fingerprintIndicator = !empty($response->payment_method->fingerprint_presence_indicator) ?
                $response->payment_method->fingerprint_presence_indicator : null;
            if (!empty($response->payment_method->card)) {
                $card = $response->payment_method->card;
                $transaction->cardLast4 = !empty($card->masked_number_last4) ?
                    $card->masked_number_last4 : null;
                $transaction->cardType = !empty($card->brand) ? $card->brand : null;
                $transaction->cvnResponseCode = !empty($card->cvv) ? $card->cvv : null;
                $transaction->cvnResponseMessage = !empty($card->cvv_result) ? $card->cvv_result : null;
                $transaction->cardBrandTransactionId = !empty($card->brand_reference) ?
                    $card->brand_reference : null;
                $transaction->avsResponseCode = !empty($card->avs_postal_code_result) ?
                    $card->avs_postal_code_result : null;
                $transaction->avsAddressResponse = !empty($card->avs_address_result) ? $card->avs_address_result : null;
                $transaction->avsResponseMessage = !empty($card->avs_action) ? $card->avs_action : null;
            }
            if (!empty($response->payment_method->bank_transfer)) {
                $bankTransfer = $response->payment_method->bank_transfer;
                $transaction->accountNumberLast4 = !empty($bankTransfer->masked_account_number_last4) ?
                    $bankTransfer->masked_account_number_last4 : null;
                $transaction->accountType = !empty($bankTransfer->account_type) ? $bankTransfer->account_type : null;
                $transaction->paymentMethodType = PaymentMethodType::ACH;
            }
            if (!empty($response->payment_method->apm)) {
                $transaction->paymentMethodType = PaymentMethodType::APM;
            }
        }

        if (!empty($response->card)) {
            $transaction->cardNumber = !empty($response->card->number) ? $response->card->number : null;
            $transaction->cardType = !empty($response->card->brand) ? $response->card->brand : '';
            $transaction->cardExpMonth = $response->card->expiry_month;
            $transaction->cardExpYear = $response->card->expiry_year;
            $transaction->cvnResponseCode = !empty($response->card->cvv) ? $response->card->cvv : null;
            $transaction->cardBrandTransactionId = !empty($response->card->brand_reference) ?
                $response->card->brand_reference : null;
        }

        $transaction->dccRateData = self::mapDccInfo($response);

        return $transaction;
    }

    private static function mapDccInfo($response)
    {
        if (
            $response->action->type != self::DCC_RESPONSE &&
            empty($response->currency_conversion)
        ) {
            return;
        }

        if (!empty($response->currency_conversion)) {
            $response = $response->currency_conversion;
        }

        $dccRateData = new DccRateData();
        $dccRateData->cardHolderCurrency = !empty($response->payer_currency) ? $response->payer_currency : null;
        $dccRateData->cardHolderAmount = !empty($response->payer_amount) ?
            StringUtils::toAmount($response->payer_amount) : null;
        $dccRateData->cardHolderRate = !empty($response->exchange_rate) ? $response->exchange_rate : null;
        $dccRateData->merchantCurrency = !empty($response->currency) ? $response->currency : null;
        $dccRateData->merchantAmount = !empty($response->amount) ? StringUtils::toAmount($response->amount) : null;
        $dccRateData->marginRatePercentage = !empty($response->margin_rate_percentage) ?
            $response->margin_rate_percentage : null;
        $dccRateData->exchangeRateSourceName = !empty($response->exchange_rate_source) ?
            $response->exchange_rate_source : null;
        $dccRateData->commissionPercentage = !empty($response->commission_percentage) ?
            $response->commission_percentage : null;
        $dccRateData->exchangeRateSourceTimestamp = !empty($response->exchange_rate_time_created) ?
            $response->exchange_rate_time_created: null;
        $dccRateData->dccId = !empty($response->id) ? $response->id : null;

        return $dccRateData;
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
                $summary->paymentType = PaymentMethodName::CARD;
            } elseif (isset($response->payment_method->digital_wallet)) {
                $card = $response->payment_method->digital_wallet;
                $summary->maskedCardNumber = isset($card->masked_token_first6last4) ?
                    $card->masked_token_first6last4 : null;
                $summary->paymentType = PaymentMethodName::DIGITAL_WALLET;
            } elseif (isset($response->payment_method->bank_transfer)) {
                $bankTransfer = $response->payment_method->bank_transfer;
                $summary->accountNumberLast4 = !empty($bankTransfer->masked_account_number_last4) ?
                    $bankTransfer->masked_account_number_last4 : null;
                $summary->accountType = !empty($bankTransfer->account_type) ? $bankTransfer->account_type : null;
                $summary->paymentType = PaymentMethodName::BANK_TRANSFER;
            } elseif (isset($response->payment_method->apm)) {
                $apm = $response->payment_method->apm;
                $alternativePaymentResponse = new AlternativePaymentResponse();
                $alternativePaymentResponse->redirectUrl = !empty($response->payment_method->redirect_url) ?
                    $response->payment_method->redirect_url : null;
                $alternativePaymentResponse->providerName = !empty($apm->provider) ? $apm->provider : null;
                $alternativePaymentResponse->providerReference = !empty($apm->provider_reference) ? $apm->provider_reference : null;
                $summary->alternativePaymentResponse = $alternativePaymentResponse;
                $summary->paymentType = PaymentMethodName::APM;
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
            (!empty($response->stage_time_created) ? new \DateTime($response->stage_time_created) : '');
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
        $summary->depositDate = !empty($response->deposit_time_created) ? $response->deposit_time_created : null;
        $summary->depositReference = !empty($response->deposit_id) ? $response->deposit_id : null;

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
            (!empty($response->three_ds->server_trans_ref) ? $response->three_ds->server_trans_ref : null);
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
        $threeDSecure->status = $response->status;
        $threeDSecure->directoryServerStartVersion = !empty($response->three_ds->ds_protocol_version_start) ?
            $response->three_ds->ds_protocol_version_start : null;
        $threeDSecure->directoryServerEndVersion = !empty($response->three_ds->ds_protocol_version_end) ?
            $response->three_ds->ds_protocol_version_end : null;
        $threeDSecure->acsStartVersion = !empty($response->three_ds->acs_protocol_version_start) ?
            $response->three_ds->acs_protocol_version_start : null;
        $threeDSecure->acsEndVersion = !empty($response->three_ds->acs_protocol_version_end) ?
            $response->three_ds->acs_protocol_version_end : null;
        $threeDSecure->enrolled = !empty($response->three_ds->enrolled_status) ?
            $response->three_ds->enrolled_status : null;
        $threeDSecure->eci = !empty($response->three_ds->eci) ? $response->three_ds->eci : null;
        $threeDSecure->acsInfoIndicator = !empty($response->three_ds->acs_info_indicator) ?
            $response->three_ds->acs_info_indicator : null;
        $threeDSecure->challengeMandated = !empty($response->three_ds->challenge_status) ?
            ($response->three_ds->challenge_status == 'MANDATED') : false;
        $threeDSecure->payerAuthenticationRequest = !empty($response->three_ds->method_data->encoded_method_data) ?
            $response->three_ds->method_data->encoded_method_data : null;
        $threeDSecure->issuerAcsUrl = !empty($response->three_ds->method_url) ? $response->three_ds->method_url : null;
        if (
            !empty($response->three_ds->acs_challenge_request_url) &&
            $threeDSecure->status == Secure3dStatus::CHALLENGE_REQUIRED
        ) {
            $threeDSecure->issuerAcsUrl = $response->three_ds->acs_challenge_request_url;
            $threeDSecure->payerAuthenticationRequest = !empty($response->three_ds->challenge_value) ?
                $response->three_ds->challenge_value : null;
        }
        $threeDSecure->setCurrency($response->currency);
        $threeDSecure->setAmount(StringUtils::toAmount($response->amount));
        $threeDSecure->authenticationValue = !empty($response->three_ds->authentication_value) ?
            $response->three_ds->authentication_value : null;
        $threeDSecure->directoryServerTransactionId = !empty($response->three_ds->ds_trans_ref) ?
            $response->three_ds->ds_trans_ref : null;
        $threeDSecure->acsTransactionId = !empty($response->three_ds->acs_trans_ref) ?
            $response->three_ds->acs_trans_ref : null;
        $threeDSecure->statusReason = !empty($response->three_ds->status_reason) ?
            $response->three_ds->status_reason : null;
        $threeDSecure->messageCategory = !empty($response->three_ds->message_category) ?
            $response->three_ds->message_category : null;
        $threeDSecure->messageType = !empty($response->three_ds->message_type) ?
            $response->three_ds->message_type : null;
        $threeDSecure->sessionDataFieldName = !empty($response->three_ds->session_data_field_name) ?
            $response->three_ds->session_data_field_name : null;
        $threeDSecure->challengeReturnUrl = !empty($response->notifications->challenge_return_url) ?
            $response->notifications->challenge_return_url : null;
        $threeDSecure->liabilityShift = !empty($response->three_ds->liability_shift) ?
            $response->three_ds->liability_shift : null;
        $threeDSecure->authenticationSource = !empty($response->three_ds->authentication_source) ?
            $response->three_ds->authentication_source : null;
        $threeDSecure->authenticationType = !empty($response->three_ds->authentication_request_type) ?
            $response->three_ds->authentication_request_type : null;
        $threeDSecure->acsInfoIndicator = !empty($response->three_ds->acs_decoupled_response_indicator) ?
            $response->three_ds->acs_decoupled_response_indicator : null;
        $threeDSecure->whitelistStatus = !empty($response->three_ds->whitelist_status) ?
            $response->three_ds->whitelist_status : null;
        if (!empty($response->three_ds->message_extension)) {
            foreach ($response->three_ds->message_extension as $messageExtension) {
                $msgItem = new MessageExtension();
                $msgItem->criticalityIndicator = !empty($messageExtension->criticality_indicator) ?
                        $messageExtension->criticality_indicator : null;
                $msgItem->messageExtensionData = !empty($messageExtension->data) ?
                    json_encode($messageExtension->data) : null;
                $msgItem->messageExtensionId = !empty($messageExtension->id) ? $messageExtension->id : null;
                $msgItem->messageExtensionName = !empty($messageExtension->name) ? $messageExtension->name : null;
                $threeDSecure->messageExtension[] = $msgItem;
            }
        }

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
        $pageInfo->orderBy = !empty($response->paging->order_by) ? $response->paging->order_by :  null;

        return $pageInfo;
    }

    /**
     * Map response for an APM transaction
     *
     * @param Object $response
     *
     * @return Transaction
     */
    public static function mapResponseAPM($response)
    {
        $apm = new AlternativePaymentResponse();
        $transaction = self::mapResponse($response);
        $paymentMethodApm = $response->payment_method->apm;
        $apm->redirectUrl = !empty($response->payment_method->redirect_url) ? $response->payment_method->redirect_url : null;
        $apm->providerName = $paymentMethodApm->provider;
        $apm->ack = $paymentMethodApm->ack;
        $apm->sessionToken = !empty($paymentMethodApm->session_token) ? $paymentMethodApm->session_token : null;
        $apm->correlationReference = $paymentMethodApm->correlation_reference;
        $apm->versionReference = $paymentMethodApm->version_reference;
        $apm->buildReference = $paymentMethodApm->build_reference;
        $apm->timeCreatedReference = !empty($paymentMethodApm->time_created_reference) ?
            new \DateTime($paymentMethodApm->time_created_reference) : null;
        $apm->transactionReference = !empty($paymentMethodApm->transaction_reference) ?
            $paymentMethodApm->transaction_reference: null;
        $apm->secureAccountReference = !empty($paymentMethodApm->secure_account_reference) ?
            $paymentMethodApm->secure_account_reference : null;
        $apm->reasonCode = !empty($paymentMethodApm->reason_code) ? $paymentMethodApm->reason_code : null;
        $apm->pendingReason = !empty($paymentMethodApm->pending_reason) ? $paymentMethodApm->pending_reason : null;
        $apm->grossAmount = !empty($paymentMethodApm->gross_amount) ?
            StringUtils::toAmount($paymentMethodApm->gross_amount) : null;
        $apm->paymentTimeReference = !empty($paymentMethodApm->payment_time_reference) ?
            new \DateTime($paymentMethodApm->payment_time_reference) : null;
        $apm->paymentType = !empty($paymentMethodApm->payment_type) ? $paymentMethodApm->payment_type : null;
        $apm->paymentStatus = !empty($paymentMethodApm->payment_status) ? $paymentMethodApm->payment_status : null;
        $apm->type = !empty($paymentMethodApm->type) ? $paymentMethodApm->type : null;
        $apm->protectionEligibilty = !empty($paymentMethodApm->protection_eligibilty) ?
            $paymentMethodApm->protection_eligibilty : null;
        $apm->feeAmount = !empty($paymentMethodApm->fee_amount) ?
            StringUtils::toAmount($paymentMethodApm->fee_amount) : null;
        if (!empty($response->payment_method->authorization)) {
            $authorization = $response->payment_method->authorization;
            $apm->authStatus = !empty($authorization->status) ? $authorization->status : null;
            $apm->authAmount = !empty($authorization->amount) ? $authorization->amount : null;
            $apm->authAck = !empty($authorization->ack) ? $authorization->ack : null;
            $apm->authCorrelationReference = !empty($authorization->correlation_reference) ?
                $authorization->correlation_reference : null;
            $apm->authVersionReference = !empty($authorization->version_reference) ?
                $authorization->version_reference : null;
            $apm->authBuildReference = !empty($authorization->build_reference) ?
                $authorization->build_reference : null;
            $apm->authPendingReason = !empty($authorization->pending_reason) ? $authorization->pending_reason : null;
            $apm->authProtectionEligibilty = !empty($authorization->protection_eligibilty) ?
                $authorization->protection_eligibilty : null;
            $apm->authProtectionEligibiltyType = !empty($authorization->protection_eligibilty_type) ?
                $authorization->protection_eligibilty_type : null;
            $apm->authReference = !empty($authorization->reference) ? $authorization->reference : null;
        }

        $transaction->alternativePaymentResponse = $apm;

        return $transaction;
    }
}