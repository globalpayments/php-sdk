<?php

namespace GlobalPayments\Api\Mapping;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\BankPaymentResponse;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\BNPLResponse;
use GlobalPayments\Api\Entities\Card;
use GlobalPayments\Api\Entities\CardIssuerResponse;
use GlobalPayments\Api\Entities\DisputeDocument;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\CaptureMode;
use GlobalPayments\Api\Entities\Enums\FraudFilterResult;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\PaymentProvider;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\Secure3dStatus;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\FraudManagementResponse;
use GlobalPayments\Api\Entities\FraudRule;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\Entities\PayerDetails;
use GlobalPayments\Api\Entities\PayLinkResponse;
use GlobalPayments\Api\Entities\PaymentMethodList;
use GlobalPayments\Api\Entities\Person;
use GlobalPayments\Api\Entities\PersonList;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Reporting\ActionSummary;
use GlobalPayments\Api\Entities\Reporting\DepositSummary;
use GlobalPayments\Api\Entities\Reporting\DisputeSummary;
use GlobalPayments\Api\Entities\Reporting\MerchantSummary;
use GlobalPayments\Api\Entities\Reporting\PayLinkSummary;
use GlobalPayments\Api\Entities\Reporting\StoredPaymentMethodSummary;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\RiskAssessment;
use GlobalPayments\Api\Entities\ThirdPartyResponse;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\User;
use GlobalPayments\Api\Entities\UserLinks;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\Utils\StringUtils;
use GlobalPayments\Api\Entities\MessageExtension;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;

class GpApiMapping
{
    const DCC_RESPONSE = 'RATE_LOOKUP';
    const LINK_CREATE = "LINK_CREATE";
    const LINK_EDIT = "LINK_EDIT";
    const TRN_INITIATE = "INITIATE";
    const MERCHANT_CREATE = 'MERCHANT_CREATE';
    const MERCHANT_LIST = 'MERCHANT_LIST';
    const MERCHANT_SINGLE = 'MERCHANT_SINGLE';
    const MERCHANT_EDIT = 'MERCHANT_EDIT';
    const MERCHANT_EDIT_INITIATED = 'MERCHANT_EDIT_INITIATED';

    /**
     * Map a response to a Transaction object for further chaining
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
        $transaction->responseCode = $response->action->result_code;
        $transaction->responseMessage = $response->status;

        switch ($response->action->type) {
            case self::LINK_CREATE:
            case self::LINK_EDIT:
                $transaction->payLinkResponse = self::mapPayLinkResponse($response);
                if (!empty($response->transactions)) {
                    $trn = $response->transactions;
                    $transaction->balanceAmount = isset($trn->amount) ? StringUtils::toAmount($trn->amount) : null;
                    $transaction->payLinkResponse->allowedPaymentMethods = $trn->allowed_payment_methods;
                }

                return $transaction;
        }

        $transaction->transactionId = $response->id;
        $transaction->clientTransactionId = !empty($response->reference) ? $response->reference : null;
        $transaction->timestamp = !empty($response->time_created) ? $response->time_created : '';
        $transaction->referenceNumber = !empty($response->reference) ? $response->reference : null;
        $batchSummary = new BatchSummary();
        $batchSummary->batchReference = !empty($response->batch_id) ? $response->batch_id : null;
        $batchSummary->totalAmount = !empty($response->amount) ? $response->amount : null;
        $batchSummary->transactionCount = !empty($response->transaction_count) ? $response->transaction_count : null;
        $transaction->batchSummary = $batchSummary;
        $transaction->balanceAmount = !empty($response->amount) ? StringUtils::toAmount($response->amount) : null;
        $transaction->authorizedAmount = ($response->status == TransactionStatus::PREAUTHORIZED && !empty($response->amount)) ?
            StringUtils::toAmount($response->amount) : null;
        $transaction->multiCapture = (!empty($response->capture_mode) && $response->capture_mode == CaptureMode::MULTIPLE);
        $transaction->fingerprint = !empty($response->fingerprint) ? $response->fingerprint : null;
        $transaction->fingerprintIndicator = !empty($response->fingerprint_presence_indicator) ?
            $response->fingerprint_presence_indicator : null;

        if (isset($response->payment_method->bnpl)) {
            return self::mapBNPLResponse($response, $transaction);
        }

        $transaction->token = substr($response->id, 0, 4) === PaymentMethod::PAYMENT_METHOD_TOKEN_PREFIX ?
            $response->id : null;
        $transaction->tokenUsageMode = !empty($response->usage_mode) ? $response->usage_mode : null;
        if (!empty($response->payment_method)) {
            $transaction->authorizationCode = $response->payment_method->result ?? null;
            if (!empty($response->payment_method->id)) {
                $transaction->token = $response->payment_method->id;
            }
            $transaction->fingerprint = !empty($response->payment_method->fingerprint) ?
                $response->payment_method->fingerprint : null;
            $transaction->fingerprintIndicator = !empty($response->payment_method->fingerprint_presence_indicator) ?
                $response->payment_method->fingerprint_presence_indicator : null;
            if (!empty($response->payment_method->card)) {
                $card = $response->payment_method->card;
                $cardDetails = new Card();
                $cardDetails->maskedNumberLast4 = $card->masked_number_last4 ?? null;
                $cardDetails->brand = $card->brand ?? null;
                $transaction->cardDetails = $cardDetails;

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
                if (!empty($card->provider)) {
                    self::mapCardIssuerResponse($transaction, $card->provider);
                }
            }
            if (!empty($response->payment_method->apm) &&
                $response->payment_method->apm->provider == strtolower(PaymentProvider::OPEN_BANKING)
            ) {
                $transaction->paymentMethodType = PaymentMethodType::BANK_PAYMENT;
                $obResponse = new BankPaymentResponse();
                $obResponse->redirectUrl = $response->payment_method->redirect_url ?? null;
                $obResponse->paymentStatus = $response->payment_method->message ?? null;
                $obResponse->accountNumber = $response->payment_method->bank_transfer->account_number ?? null;
                $obResponse->sortCode = $response->payment_method->bank_transfer->bank->code ?? null;
                $obResponse->accountName = $response->payment_method->bank_transfer->bank->name ?? null;
                $obResponse->iban = $response->payment_method->bank_transfer->iban ?? null;
                $transaction->bankPaymentResponse = $obResponse;
            } elseif (!empty($response->payment_method->bank_transfer)) {
                $bankTransfer = $response->payment_method->bank_transfer;
                $transaction->accountNumberLast4 = !empty($bankTransfer->masked_account_number_last4) ?
                    $bankTransfer->masked_account_number_last4 : null;
                $transaction->accountType = !empty($bankTransfer->account_type) ? $bankTransfer->account_type : null;
                $transaction->paymentMethodType = PaymentMethodType::ACH;
            } elseif (!empty($response->payment_method->apm)) {
                $transaction->paymentMethodType = PaymentMethodType::APM;
            }

            if (
                !empty($response->payment_method->shipping_address) ||
                !empty($response->payment_method->payer)
            ) {
                $payerDetails = new PayerDetails();
                $payerDetails->email = $response->payment_method->payer->email ?? null;
                if (!empty($response->payment_method->payer->billing_address)) {
                    $billingAddress = $response->payment_method->payer->billing_address;
                    $payerDetails->firstName = $billingAddress->first_name  ?? null;
                    $payerDetails->lastName = $billingAddress->last_name  ?? null;
                    $payerDetails->billingAddress = self::mapAddressObject(
                        $billingAddress,
                        AddressType::BILLING
                    );
                }
                $payerDetails->shippingAddress = self::mapAddressObject(
                    $response->payment_method->shipping_address,
                    AddressType::SHIPPING
                );
                $transaction->payerDetails = $payerDetails;
            }
        }

        if (!empty($response->card)) {
            $cardDetails = new Card();
            $cardDetails->cardNumber = $response->card->number ?? null;
            $cardDetails->brand = $response->card->brand ?? null;
            $cardDetails->cardExpMonth =$response->card->expiry_month ?? null;
            $cardDetails->cardExpYear = $response->card->expiry_year ?? null;
            $transaction->cardDetails = $cardDetails;

            $transaction->cardNumber = !empty($response->card->number) ? $response->card->number : null;
            $transaction->cardType = !empty($response->card->brand) ? $response->card->brand : '';
            $transaction->cardExpMonth = $response->card->expiry_month ?? null;
            $transaction->cardExpYear = $response->card->expiry_year ?? null;
            $transaction->cvnResponseCode = !empty($response->card->cvv) ? $response->card->cvv : null;
            $transaction->cardBrandTransactionId = !empty($response->card->brand_reference) ?
                $response->card->brand_reference : null;
        }

        $transaction->dccRateData = self::mapDccInfo($response);
        $transaction->multiCapture = (!empty($response->capture_mode) && $response->capture_mode == CaptureMode::MULTIPLE);
        $transaction->fraudFilterResponse = !empty($response->risk_assessment) ?
            self::mapFraudManagement(reset($response->risk_assessment)) : null;

        return $transaction;
    }

    private static function mapFraudManagement($fraudResponse)
    {
        $fraudFilterResponse = new FraudManagementResponse();
        $fraudFilterResponse->fraudResponseMode = $fraudResponse->mode ?? null;
        $fraudFilterResponse->fraudResponseResult = !empty($fraudResponse->result) ?
            self::mapFraudResponseResult($fraudResponse->result) : '';
        $fraudFilterResponse->fraudResponseMessage = $fraudResponse->message ?? null;
        if (!empty($fraudResponse->rules)) {
            foreach ($fraudResponse->rules as $rule) {
                $fraudRule = new FraudRule();
                $fraudRule->key = $rule->reference ?? null;
                $fraudRule->mode = $rule->mode ?? null;
                $fraudRule->description = $rule->description ?? null;
                $fraudRule->result = !empty($rule->result) ? self::mapFraudResponseResult($rule->result) : null;
                $fraudFilterResponse->fraudResponseRules[] = $fraudRule;
            }
        }

        return $fraudFilterResponse;
    }

    private static function mapFraudResponseResult($fraudResponseResult)
    {
        switch ($fraudResponseResult) {
            case 'PENDING_REVIEW':
                return FraudFilterResult::HOLD;
            case 'ACCEPTED':
                return FraudFilterResult::PASS;
            case 'REJECTED':
                return FraudFilterResult::BLOCK;
            case 'NOT_EXECUTED':
                return FraudFilterResult::NOT_EXECUTED;
            case 'RELEASE_SUCCESSFULL':
                return FraudFilterResult::RELEASE_SUCCESSFUL;
            case 'HOLD_SUCCESSFULL':
                return FraudFilterResult::HOLD_SUCCESSFUL;
            default:
                return 'UNKNOWN';
        }
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
            case ReportType::DOCUMENT_DISPUTE_DETAIL:
                $report = new DisputeDocument();
                $report->id = $response->id;
                $report->b64_content = $response->b64_content;
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
            case ReportType::PAYLINK_DETAIL:
                $report = self::mapPayLinkSummary($response);
                break;
            case ReportType::FIND_PAYLINK_PAGED:
                $report = self::setPagingInfo($response);
                foreach ($response->links as $link) {
                    array_push($report->result, self::mapPayLinkSummary($link));
                }
                break;
            case ReportType::FIND_MERCHANTS_PAGED:
                $report = self::setPagingInfo($response);
                foreach ($response->merchants as $merchant) {
                    array_push($report->result, self::mapMerchantSummary($merchant));
                }
                return $report;
            default:
                throw new ApiException("Report type not supported!");
        }

        return $report;
    }

    /**
     * Map the response from the search transaction request
     *
     * @param $response
     * @return TransactionSummary
     */
    public static function mapTransactionSummary($response)
    {
        $summary = self::createTransactionSummary($response);
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
        $summary->orderId = $response->order_reference ?? null;
        if (isset($response->system)) {
            self::mapSystemResponse($summary, $response->system);
        }
        if (isset($response->payment_method)) {
            $paymentMethod = $response->payment_method;
            $summary->gatewayResponseMessage = isset($paymentMethod->message) ? $paymentMethod->message : null;
            $summary->entryMode = isset($paymentMethod->entry_mode) ? $paymentMethod->entry_mode : null;
            $summary->cardHolderName = isset($paymentMethod->name) ? $paymentMethod->name : '';

            /** map card details */
            if (isset($paymentMethod->card)) {
                $card = $paymentMethod->card;
                $summary->aquirerReferenceNumber = isset($card->arn) ? $card->arn : null;
                $summary->maskedCardNumber = isset($card->masked_number_first6last4) ?
                    $card->masked_number_first6last4 : null;
                $summary->paymentType = PaymentMethodName::CARD;
            }
            /** map digital wallet info */
            if (isset($paymentMethod->digital_wallet)) {
                $card = $response->payment_method->digital_wallet;
                $summary->maskedCardNumber = isset($card->masked_token_first6last4) ?
                    $card->masked_token_first6last4 : null;
                $summary->paymentType = PaymentMethodName::DIGITAL_WALLET;
            }
            /** map ACH response info */
            if (
                isset($response->payment_method->bank_transfer) &&
                !isset($response->payment_method->apm)
            ) {
                $summary->paymentType = PaymentMethodName::BANK_TRANSFER;
                $bankTransfer = $response->payment_method->bank_transfer;
                $summary->accountNumberLast4 = !empty($bankTransfer->masked_account_number_last4) ?
                    $bankTransfer->masked_account_number_last4 : null;
                $summary->accountType = !empty($bankTransfer->account_type) ? $bankTransfer->account_type : null;
            }
            if (isset($response->payment_method->apm)) {
                /** map Open Banking response info */
                if ($response->payment_method->apm->provider == strtolower(PaymentProvider::OPEN_BANKING)) {
                    $summary->paymentType = PaymentMethodName::BANK_PAYMENT;
                    $bankPaymentResponse = new BankPaymentResponse();
                    $bankPaymentResponse->iban = $response->payment_method->bank_transfer->iban ?? null;
                    $bankPaymentResponse->accountNumber = $response->payment_method->bank_transfer->account_number ?? null;
                    $bankPaymentResponse->accountName = $response->payment_method->bank_transfer->bank->name ?? null;
                    $bankPaymentResponse->sortCode = $response->payment_method->bank_transfer->bank->code ?? null;
                    $bankPaymentResponse->remittanceReferenceValue =
                        $response->payment_method->bank_transfer->remittance_reference->value ?? null;
                    $bankPaymentResponse->remittanceReferenceType =
                        $response->payment_method->bank_transfer->remittance_reference->type ?? null;
                    $summary->bankPaymentResponse = $bankPaymentResponse;
                } else { /** map APMs (Paypal) response info */
                    $apm = $response->payment_method->apm;
                    $alternativePaymentResponse = new AlternativePaymentResponse();
                    $alternativePaymentResponse->redirectUrl = !empty($response->payment_method->redirect_url) ?
                        $response->payment_method->redirect_url : null;
                    $alternativePaymentResponse->providerName = !empty($apm->provider) ? $apm->provider : null;
                    $alternativePaymentResponse->providerReference = !empty($apm->provider_reference) ? $apm->provider_reference : null;
                    $summary->alternativePaymentResponse = $alternativePaymentResponse;
                    $summary->paymentType = PaymentMethodName::APM;
                }
            }
            /** map BNPL response info */
            if (isset($response->payment_method->bnpl)) {
                $bnpl = $response->payment_method->bnpl;
                $bnplResponse = new BNPLResponse();
                $bnplResponse->providerName = $bnpl->provider ?? null;
                $summary->bnplResponse = $bnplResponse;
                $summary->paymentType = PaymentMethodName::BNPL;
            }

            if (!empty($card)) {
                $summary->cardType = isset($card->brand) ? $card->brand : null;
                $summary->authCode = isset($card->authcode) ? $card->authcode : null;
                $summary->brandReference = isset($card->brand_reference) ? $card->brand_reference : null;
            }
        }

        $summary->fraudManagementResponse = !empty($response->risk_assessment) ?
            self::mapFraudManagement($response->risk_assessment) : null;

        return $summary;
    }

    /**
     * Map the response from the search deposit request
     *
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
            self::mapSystemResponse($summary, $response->system);
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
     * Map the response from the search dispute response
     *
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
            $summary->caseMerchantId = $system->mid ?? null;
            $summary->merchantHierarchy = $system->hierarchy ?? null;
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
        if (!empty($response->documents)) {
            foreach ($response->documents as $document) {
                if (!empty($document->id)) {
                    $disputeDocument = new DisputeDocument();
                    $disputeDocument->id = $document->id;
                    $disputeDocument->type = !empty($document->type) ? $document->type : null;
                    $summary->documents[] = $disputeDocument;
                }
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

    public static function mapRiskAssessmentResponse($response)
    {
        $riskAssessment = new RiskAssessment();
        $riskAssessment->id = $response->id;
        $riskAssessment->timeCreated = $response->time_created;
        $riskAssessment->status = $response->status ?? null;
        $riskAssessment->amount = isset($response->amount) ? StringUtils::toAmount($response->amount) : null;
        $riskAssessment->currency = $response->currency ?? null;
        $riskAssessment->merchantId = $response->merchant_id ?? null;
        $riskAssessment->merchantName = $response->merchant_name ?? null;
        $riskAssessment->accountId = $response->account_id ?? null;
        $riskAssessment->accountName = $response->account_name ?? null;
        $riskAssessment->reference = $response->reference ?? null;
        $riskAssessment->responseCode = $response->action->result_code ?? null;
        $riskAssessment->responseMessage = $response->result ?? null;
        if (isset($response->payment_method->card)) {
            $paymentMethod = $response->payment_method->card;
            $card = new Card();
            $card->maskedNumberLast4 = $paymentMethod->masked_number_last4 ?? null;
            $card->brand = $paymentMethod->brand ?? null;
            $card->brandReference = $paymentMethod->brand_reference ?? null;
            $card->bin = $paymentMethod->bin ?? null;
            $card->binCountry = $paymentMethod->bin_country ?? null;
            $card->accountType = $paymentMethod->account_type ?? null;
            $card->issuer = $paymentMethod->issuer ?? null;

            $riskAssessment->cardDetails = $card;
        }
        if (isset($response->raw_response)) {
            $rawResponse = $response->raw_response;
            $thirdPartyResponse = new ThirdPartyResponse();
            $thirdPartyResponse->platform = $rawResponse->platform;
            $thirdPartyResponse->data = $rawResponse->data;
            $riskAssessment->thirdPartyResponse = $thirdPartyResponse;
        }
        $riskAssessment->actionId = $response->action->id ?? null;

        return $riskAssessment;
    }

    /**
     * @param Object $response
     */
    public static function mapResponseSecure3D($response)
    {
        $transaction = new Transaction();
        $threeDSecure = new ThreeDSecure();
        $threeDSecure->serverTransactionId = $response->id;

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
        $threeDSecure->acsReferenceNumber = $response->three_ds->acs_reference_number ?? null;
        $threeDSecure->providerServerTransRef = $response->three_ds->server_trans_ref ?? null;
        $threeDSecure->challengeMandated = !empty($response->three_ds->challenge_status) ?
            ($response->three_ds->challenge_status == 'MANDATED') : false;
        $threeDSecure->payerAuthenticationRequest = !empty($response->three_ds->method_data->encoded_method_data) ?
            $response->three_ds->method_data->encoded_method_data : null;
        $threeDSecure->issuerAcsUrl = !empty($response->three_ds->method_url) ? $response->three_ds->method_url : null;
        $threeDSecure->authenticationSource = !empty($response->three_ds->authentication_source) ?
            $response->three_ds->authentication_source : null;

        if (
            !empty($response->three_ds->acs_challenge_request_url) &&
            $threeDSecure->status == Secure3dStatus::CHALLENGE_REQUIRED
        ) {
            $threeDSecure->issuerAcsUrl = $response->three_ds->acs_challenge_request_url;
            $threeDSecure->payerAuthenticationRequest = !empty($response->three_ds->challenge_value) ?
                $response->three_ds->challenge_value : null;
        }
        if (
            $threeDSecure->authenticationSource == AuthenticationSource::MOBILE_SDK &&
            !empty($response->three_ds->mobile_data)
        ) {
            $mobileData = $response->three_ds->mobile_data;
            $threeDSecure->payerAuthenticationRequest = !empty($mobileData->acs_signed_content) ?
                $mobileData->acs_signed_content : null;
            $threeDSecure->acsInterface = !empty($mobileData->acs_rendering_type->acs_interface) ?
                $mobileData->acs_rendering_type->acs_interface : null;
            $threeDSecure->acsUiTemplate = !empty($mobileData->acs_rendering_type->acs_ui_template) ?
                $mobileData->acs_rendering_type->acs_ui_template : null;
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
        $threeDSecure->authenticationType = !empty($response->three_ds->authentication_request_type) ?
            $response->three_ds->authentication_request_type : null;
        $threeDSecure->decoupledResponseIndicator = $response->three_ds->acs_decoupled_response_indicator ?? null;
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
        $apm->ack = $paymentMethodApm->ack ?? null;
        $apm->sessionToken = !empty($paymentMethodApm->session_token) ? $paymentMethodApm->session_token : null;
        $apm->correlationReference = $paymentMethodApm->correlation_reference ?? null;
        $apm->versionReference = $paymentMethodApm->version_reference ?? null;
        $apm->buildReference = $paymentMethodApm->build_reference ?? null;
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
            $apm->authAmount = !empty($authorization->amount) ? StringUtils::toAmount($authorization->amount) : null;
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

    /**
     * @param $response
     * @return PayLinkSummary
     */
    public static function mapPayLinkSummary($response)
    {
        $summary = new PayLinkSummary();
        $summary->merchantId = $response->merchant_id ?? null;
        $summary->merchantName = $response->merchant_name ?? null;
        $summary->accountId = $response->account_id ?? null;
        $summary->accountName = $response->account_name ?? null;
        $summary->id = $response->id ?? null;
        $summary->url = $response->url ?? null;
        $summary->status = $response->status ?? null;
        $summary->type = $response->type ?? null;
        $summary->usageMode = $response->usage_mode ?? null;
        $summary->usageLimit = $response->usage_limit ?? null; //@TODO
        $summary->reference = $response->reference ?? null;
        $summary->name = $response->name ?? null;
        $summary->description = $response->description ?? null;
        $summary->viewedCount = $response->viewed_count ?? null;
        $summary->expirationDate = !empty($response->expiration_date) ?
            new \DateTime($response->expiration_date) : null;

        $summary->shippable = $response->shippable ?? null;
        $summary->usageCount = $response->usage_count ?? null;
        $summary->images = $response->images ?? null;
        $summary->shippingAmount = $response->shipping_amount ?? null;

        if (!empty($response->transactions)) {
            $summary->amount = StringUtils::toAmount($response->transactions->amount) ?? null;
            $summary->currency = $response->transactions->currency ?? null;
            $summary->allowedPaymentMethods = $response->transactions->allowed_payment_methods ?? null; //@TODO check
            if (!empty($response->transactions->transaction_list)) {
                foreach ($response->transactions->transaction_list as $transaction) {
                    $summary->transactions[] =  self::createTransactionSummary($transaction);
                }
            }
        }

        return $summary;
    }

    public static function mapPayLinkResponse($response)
    {
        $payLinkResponse = new PayLinkResponse();
        $payLinkResponse->id = $response->id;
        $payLinkResponse->accountName = $response->account_name ?? null;
        $payLinkResponse->url = $response->url ?? null;
        $payLinkResponse->status = $response->status ?? null;
        $payLinkResponse->type = $response->type ?? null;
        $payLinkResponse->usageMode = $response->usage_mode ?? null;
        $payLinkResponse->usageLimit = $response->usage_limit ?? null;
        $payLinkResponse->reference = $response->reference ?? null;
        $payLinkResponse->name = $response->name ?? null;
        $payLinkResponse->description = $response->description ?? null;
        $payLinkResponse->viewedCount = $response->viewed_count ?? null;
        $payLinkResponse->expirationDate = !empty($response->expiration_date) ? new \DateTime($response->expiration_date) : null;
        $payLinkResponse->isShippable = $response->shippable ?? null;

        return $payLinkResponse;
    }

    /**
     * Create an new TransactionSummary object
     *
     * @param $response
     *
     * @return TransactionSummary
     */
    private static function createTransactionSummary($response)
    {
        $transaction = new TransactionSummary();
        $transaction->transactionId = isset($response->id) ? $response->id : null;
        $timeCreated = self::validateStringDate($response->time_created);
        $transaction->transactionDate = !empty($timeCreated) ? new \DateTime($timeCreated) : '';
        $transaction->transactionStatus = $response->status;
        $transaction->transactionType = $response->type;
        $transaction->channel = !empty($response->channel) ? $response->channel : null;
        $transaction->amount = StringUtils::toAmount($response->amount);
        $transaction->currency = $response->currency;
        $transaction->referenceNumber = $transaction->clientTransactionId = $response->reference;
        $transaction->description = $response->description ?? null;
        $transaction->fingerprint = $response->payment_method->fingerprint ?? null;
        $transaction->fingerprintIndicator = $response->payment_method->fingerprint_presence_indicator ?? null;

        return $transaction;
    }

    /**
     * @param $response
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    private static function mapBNPLResponse($response,Transaction $transaction)
    {
        $transaction->paymentMethodType = PaymentMethodType::BNPL;
        $bnplResponse = new BNPLResponse();
        $bnplResponse->redirectUrl = !empty($response->payment_method->redirect_url) ?
            $response->payment_method->redirect_url : null;
        $bnplResponse->providerName = !empty($response->payment_method->bnpl->provider) ?
            $response->payment_method->bnpl->provider : null;
        $transaction->bnplResponse = $bnplResponse;

        return $transaction;
    }

    private static function mapMerchantSummary($merchant): MerchantSummary
    {
        $merchantInfo = new MerchantSummary();
        $merchantInfo->id = $merchant->id;
        $merchantInfo->name = $merchant->name;
        $merchantInfo->status = $merchant->status ?? '';
        if (!empty($merchant->links)) {
            foreach ($merchant->links as $link) {
                $userLink = new UserLinks();
                $userLink->rel = $link->rel ?? null;
                $userLink->href = $link->href ?? null;
                $merchantInfo->links[] = $userLink;
            }
        }

        return $merchantInfo;
    }

    public static function mapMerchantsEndpointResponse($response): User
    {
        if (empty($response->action->type)) {
            throw new UnsupportedTransactionException(sprintf("Empty action type response!"));
        }

        switch ($response->action->type) {
            case self::MERCHANT_CREATE:
            case self::MERCHANT_EDIT:
            case self::MERCHANT_EDIT_INITIATED:
            case self::MERCHANT_SINGLE:
                $user = new User();
                $user->userId = $response->id;
                $user->name = $response->name ?? null;
                $user->userStatus = $response->status;
                $user->userType = $response->type;
                $user->timeCreate = !empty($response->time_created) ? new \DateTime($response->time_created) : null;
                $user->timeLastUpdated = !empty($response->time_last_updated) ?
                    new \DateTime($response->time_last_updated) : null;
                $user->responseCode = $response->action->result_code ?? null;
                $user->statusDescription = $response->status_description ?? null;
                $user->email = $response->email ?? null;
                if (!empty($response->address)) {
                    foreach ($response->address as $address) {
                        $user->addresses[] = self::mapAddressObject($address);
                    }
                }
                if (
                    !empty($response->contact_phone->country_code) &&
                    !empty($response->contact_phone->subscriber_number)
                ) {
                    $user->contactPhone = new PhoneNumber(
                        $response->contact_phone->country_code,
                        $response->contact_phone->subscriber_number,
                        PhoneNumberType::WORK
                    );
                }
                if (!empty($response->persons)) {
                    self::mapMerchantPersonList($response->persons, $user);
                }
                if (!empty($response->payment_methods)) {
                    self::mapMerchantPaymentMethods($response->payment_methods,$user);
                }
                return $user;
            default:
                throw new UnsupportedTransactionException(sprintf("Unknown action type %s", $response->action->type));
        }
    }

    /**
     * @param $paymentMethods
     * @param User $user
     */
    private static function mapMerchantPaymentMethods($paymentMethods, &$user)
    {
        $pmList = new PaymentMethodList();
        foreach ($paymentMethods as $paymentMethod) {
            if (isset($paymentMethod->bank_transfer)) {
                $bankTransfer = $paymentMethod->bank_transfer;
                $pm = new ECheck();
                $pm->checkType = $bankTransfer->account_holder_type ?? null;
                $pm->accountNumber = $bankTransfer->account_number ?? null;
                $pm->accountType = $bankTransfer->account_type ?? null;
                if (isset($bankTransfer->bank)) {
                    $pm->routingNumber = $bankTransfer->bank->code ?? null;
                    $pm->bankName = $bankTransfer->bank->name ?? null;
                }
                $pm->checkHolderName = $paymentMethod->name ?? null;
            }
            if (isset($paymentMethod->card)) {
                $card = $paymentMethod->card;
                $pm = new CreditCardData();
                $pm->cardHolderName = $card->name ?? null;
                $pm->number = $card->number ?? null;
                $pm->expYear = $card->expiry_year ?? null;
            }
            $functions = $paymentMethod->functions ?? null;
            $pmList->append(['functions' => $functions, 'payment_method' => $pm]);
        }
        $user->paymentMethodList = $pmList;
    }

    private static function mapAddressObject($address, $type = null)
    {
        if (empty($address)) {
            return null;
        }
        $userAddress = new Address();
        $userAddress->type = $type;
        $userAddress->streetAddress1 = $address->line_1 ?? null;
        $userAddress->streetAddress2 = $address->line_2 ?? null;
        $userAddress->streetAddress3 = $address->line_3 ?? null;
        $userAddress->city = $address->city ?? null;
        $userAddress->state = $address->state ?? null;
        $userAddress->postalCode = $address->postal_code ?? null;
        $userAddress->countryCode = $address->country ?? null;
        $userAddress->type = !empty($address->functions) ? $address->functions[0] : $type;

        return $userAddress;
    }

    private static function mapMerchantPersonList($persons, &$user)
    {
        $personList = new PersonList();
        foreach ($persons as $person) {
            $newPerson = new Person();
            $newPerson->functions = $person->functions;
            $newPerson->firstName = $person->first_name;
            $newPerson->middleName = $person->middle_name;
            $newPerson->lastName = $person->last_name;
            $newPerson->email = $person->email;
            if (!empty($person->address)) {
                $newPerson->address = new Address();
                $newPerson->address->streetAddress1 = $person->address->line_1 ?? null;
                $newPerson->address->streetAddress2 = $person->address->line_2 ?? null;
                $newPerson->address->streetAddress3 = $person->address->line_3 ?? null;
                $newPerson->address->city = $person->address->city ?? null;
                $newPerson->address->state = $person->address->state ?? null;
                $newPerson->address->postalCode = $person->address->postal_code ?? null;
                $newPerson->address->country = $person->address->country ?? null;
            }

            $newPerson->workPhone = !empty($person->work_phone) ?
                new PhoneNumber('', $person->work_phone->subscriber_number, PhoneNumberType::WORK) : null;
            $newPerson->homePhone = !empty($person->contact_phone) ?
                new PhoneNumber('', $person->contact_phone->subscriber_number, PhoneNumberType::HOME) : null;
            $personList->append($newPerson);
        }
        $user->personList = $personList;
    }

    private static function validateStringDate($date): string
    {
        try {
            new \DateTime($date);
        } catch (\Exception $e) {
            $errors = \DateTime::getLastErrors();
            if (isset($errors['error_count']) && $errors['error_count'] > 0) {
                return current(explode('.', $date));
            }
        }

        return $date;
    }

    /**
     * Map the result codes directly from the card issuer.
     *
     * @param Transaction $transaction
     * @param $cardIssuerResponse
     */
    private static function mapCardIssuerResponse(Transaction &$transaction, $cardIssuerResponse)
    {
        $transaction->cardIssuerResponse = new CardIssuerResponse();
        $transaction->cardIssuerResponse->result = $cardIssuerResponse->result ?? null;
        $transaction->cardIssuerResponse->avsResult = $cardIssuerResponse->avs_result ?? null;
        $transaction->cardIssuerResponse->cvvResult = $cardIssuerResponse->cvv_result ?? null;
        $transaction->cardIssuerResponse->avsAddressResult = $cardIssuerResponse->avs_address_result ?? null;
        $transaction->cardIssuerResponse->avsPostalCodeResult = $cardIssuerResponse->avs_postal_code_result ?? null;
    }

    /**
     * @param TransactionSummary|DepositSummary $summary
     * @param $system
     */
    private static function mapSystemResponse(&$summary, $system)
    {
        if (!isset($system)) {
            return;
        }

        $summary->merchantId = $system->mid ?? null;
        $summary->merchantHierarchy = $system->hierarchy ?? null;
        $summary->merchantName = $system->name ?? null;
        $summary->merchantDbaName = $system->dba ?? null;
    }
}