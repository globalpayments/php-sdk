<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\StringUtils;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;

class GpApiReportRequestBuilder implements IRequestBuilder
{
    public static function canProcess($builder)
    {
        if ($builder instanceof ReportBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param GpApiConfig $config
     * @return GpApiRequest|null
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $queryParams = $payload = null;
        /**
         * @var TransactionReportBuilder $builder
         */
        switch ($builder->reportType)
        {
            case ReportType::TRANSACTION_DETAIL:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->transactionId;
                $verb = 'GET';
                break;
            case ReportType::DEPOSIT_DETAIL:
                $endpoint = GpApiRequest::DEPOSITS_ENDPOINT . '/' . $builder->searchBuilder->depositId;
                $verb = 'GET';
                break;
            case ReportType::FIND_DEPOSITS_PAGED:
                $endpoint = GpApiRequest::DEPOSITS_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams['account_name'] = $config->accessTokenInfo->dataAccountName;
                $queryParams['account_id'] = $config->accessTokenInfo->dataAccountID;
                $queryParams['order_by'] = $builder->depositOrderBy;
                $queryParams['order'] = $builder->order;
                $queryParams['amount'] = StringUtils::toNumeric($builder->searchBuilder->amount);
                $queryParams['from_time_created'] = !empty($builder->searchBuilder->startDate) ?
                    $builder->searchBuilder->startDate->format('Y-m-d') : null;
                $queryParams['to_time_created'] = !empty($builder->searchBuilder->endDate) ?
                    $builder->searchBuilder->endDate->format('Y-m-d') : null;
                $queryParams['id'] = $builder->searchBuilder->depositId;
                $queryParams['status'] = $builder->searchBuilder->depositStatus;
                $queryParams['masked_account_number_last4'] = $builder->searchBuilder->accountNumberLastFour;
                $queryParams['system.mid'] = $builder->searchBuilder->merchantId;
                $queryParams['system.hierarchy'] = $builder->searchBuilder->systemHierarchy;
                break;
            case ReportType::FIND_TRANSACTIONS_PAGED:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT;
                $verb = 'GET';
                $queryParams = [
                    'id' => $builder->transactionId,
                    'type' => $builder->searchBuilder->paymentType,
                    'channel' => $builder->searchBuilder->channel,
                    'amount' => StringUtils::toNumeric($builder->searchBuilder->amount),
                    'currency' => $builder->searchBuilder->currency,
                    'token_first6' => $builder->searchBuilder->tokenFirstSix,
                    'token_last4' => $builder->searchBuilder->tokenLastFour,
                    'account_name' => $builder->searchBuilder->accountName,
                    'country' => $builder->searchBuilder->country,
                    'batch_id' => $builder->searchBuilder->batchId,
                    'entry_mode' => $builder->searchBuilder->paymentEntryMode,
                    'name' => $builder->searchBuilder->name,
                    'payment_method' => $builder->searchBuilder->paymentMethodName,
                    'risk_assessment_mode' => $builder->searchBuilder->riskAssessmentMode,
                    'risk_assessment_result' => EnumMapping::mapFraudFilterResult(
                        GatewayProvider::GP_API,
                        $builder->searchBuilder->riskAssessmentResult),
                    'risk_assessment_reason_code' => $builder->searchBuilder->riskAssessmentReasonCode,
                    'provider' => $builder->searchBuilder->paymentProvider,
                ];

                $this->addBasicParams($queryParams, $builder);
                $queryParams = array_merge($queryParams,  $this->getTransactionParams($builder));
                break;
            case ReportType::FIND_SETTLEMENT_TRANSACTIONS_PAGED:
                $endpoint = GpApiRequest::SETTLEMENT_TRANSACTIONS_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams['account_name'] = $config->accessTokenInfo->dataAccountName;
                $queryParams['account_id'] = $config->accessTokenInfo->dataAccountID;
                $queryParams['deposit_status'] = $builder->searchBuilder->depositStatus;
                $queryParams['arn'] = $builder->searchBuilder->aquirerReferenceNumber;
                $queryParams['deposit_id'] = $builder->searchBuilder->depositId;
                $queryParams['from_deposit_time_created'] = !empty($builder->searchBuilder->startDepositDate) ?
                    $builder->searchBuilder->startDepositDate->format('Y-m-d') : null;
                $queryParams['to_deposit_time_created'] = !empty($builder->searchBuilder->endDepositDate) ?
                    $builder->searchBuilder->endDepositDate->format('Y-m-d') : null;
                $queryParams['from_batch_time_created'] = !empty($builder->searchBuilder->startBatchDate) ?
                    $builder->searchBuilder->startBatchDate->format('Y-m-d') : null;
                $queryParams['to_batch_time_created'] = !empty($builder->searchBuilder->endBatchDate) ?
                    $builder->searchBuilder->endBatchDate->format('Y-m-d') : null;
                $queryParams['system.mid'] = $builder->searchBuilder->merchantId;
                $queryParams['system.hierarchy'] = $builder->searchBuilder->systemHierarchy;
                $queryParams = array_merge($queryParams,  $this->getTransactionParams($builder));
                break;
            case ReportType::DISPUTE_DETAIL:
                $endpoint = GpApiRequest::DISPUTES_ENDPOINT . '/' . $builder->searchBuilder->disputeId;
                $verb = 'GET';
                break;
            case ReportType::DOCUMENT_DISPUTE_DETAIL:
                $endpoint = GpApiRequest::DISPUTES_ENDPOINT . '/' . $builder->searchBuilder->disputeId . '/documents/' .
                    $builder->searchBuilder->disputeDocumentId;
                $verb = 'GET';
                break;
            case ReportType::FIND_DISPUTES_PAGED:
                $endpoint = GpApiRequest::DISPUTES_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams = array_merge($queryParams, $this->getDisputesParams($builder));
                break;
            case ReportType::SETTLEMENT_DISPUTE_DETAIL:
                $endpoint = GpApiRequest::SETTLEMENT_DISPUTES_ENDPOINT . '/' . $builder->searchBuilder->settlementDisputeId;
                $verb = 'GET';
                break;
            case ReportType::FIND_SETTLEMENT_DISPUTES_PAGED:
                $endpoint = GpApiRequest::SETTLEMENT_DISPUTES_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams['account_name'] = $config->accessTokenInfo->dataAccountName;
                $queryParams['account_id'] = $config->accessTokenInfo->dataAccountID;
                $queryParams = array_merge($queryParams, $this->getDisputesParams($builder));
                break;
            case ReportType::FIND_STORED_PAYMENT_METHODS_PAGED:
                if ($builder->searchBuilder->paymentMethod instanceof CreditCardData) {
                    $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT . '/search';
                    $verb = 'POST';
                    $paymentMethod = $builder->searchBuilder->paymentMethod;
                    $card = [
                        'number' => $paymentMethod->number,
                        'expiry_month' => str_pad((string) $paymentMethod->expMonth, 2, '0', STR_PAD_LEFT),
                        'expiry_year' => substr(str_pad((string) $paymentMethod->expYear, 4, '0', STR_PAD_LEFT), 2, 2)
                    ];
                    $payload = [
                        'account_name' => $config->accessTokenInfo->tokenizationAccountName,
                        'account_id' => $config->accessTokenInfo->tokenizationAccountID,
                        'reference' => $builder->searchBuilder->referenceNumber,
                        'card' => !empty($card) ? $card : null
                    ];
                    break;
                }
                $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams = $queryParams + [
                    'order_by' => $builder->storedPaymentMethodOrderBy,
                    'order' => $builder->order,
                    'number_last4' => $builder->searchBuilder->cardNumberLastFour,
                    'reference' => $builder->searchBuilder->referenceNumber,
                    'status' => $builder->searchBuilder->storedPaymentMethodStatus,
                    'from_time_created' => !empty($builder->searchBuilder->startDate) ?
                        $builder->searchBuilder->startDate->format('Y-m-d') : null,
                    'to_time_created' => !empty($builder->searchBuilder->endDate) ?
                        $builder->searchBuilder->endDate->format('Y-m-d') : null,
                    'from_time_last_updated' => !empty($builder->searchBuilder->fromTimeLastUpdated) ?
                        $builder->searchBuilder->fromTimeLastUpdated->format('Y-m-d') : null,
                    'to_time_last_updated' => !empty($builder->searchBuilder->toTimeLastUpdated) ?
                        $builder->searchBuilder->toTimeLastUpdated->format('Y-m-d') : null,
                    'id' => !empty($builder->searchBuilder->storedPaymentMethodId) ?
                            $builder->searchBuilder->storedPaymentMethodId : null
                ];
                break;
            case ReportType::STORED_PAYMENT_METHOD_DETAIL:
                $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT . '/' . $builder->searchBuilder->storedPaymentMethodId;
                $verb = 'GET';
                break;
            case ReportType::ACTION_DETAIL:
                $endpoint = GpApiRequest::ACTIONS_ENDPOINT . '/' . $builder->searchBuilder->actionId;
                $verb = 'GET';
                break;
            case ReportType::FIND_ACTIONS_PAGED:
                $endpoint = GpApiRequest::ACTIONS_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams = $queryParams + [
                        'order_by' => $builder->actionOrderBy,
                        'order' => $builder->order,
                        'id' => $builder->searchBuilder->actionId,
                        'type' => $builder->searchBuilder->actionType,
                        'resource' => $builder->searchBuilder->resource,
                        'resource_status' => $builder->searchBuilder->resourceStatus,
                        'resource_id' => $builder->searchBuilder->resourceId,
                        'from_time_created' => !empty($builder->searchBuilder->startDate) ?
                            $builder->searchBuilder->startDate->format('Y-m-d') : null,
                        'to_time_created' => !empty($builder->searchBuilder->endDate) ?
                            $builder->searchBuilder->endDate->format('Y-m-d') : null,
                        'merchant_name' => $builder->searchBuilder->merchantName,
                        'account_name' => $builder->searchBuilder->accountName,
                        'app_name' => $builder->searchBuilder->appName,
                        'version' => $builder->searchBuilder->version,
                        'response_code' => $builder->searchBuilder->responseCode,
                        'http_response_code' => $builder->searchBuilder->httpResponseCode
                    ];
                break;
            case ReportType::PAYLINK_DETAIL:
                $endpoint = GpApiRequest::PAYLINK_ENDPOINT . '/' . $builder->searchBuilder->payLinkId;
                $verb = 'GET';
                break;
            case ReportType::FIND_PAYLINK_PAGED:
                $endpoint = GpApiRequest::PAYLINK_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams['from_time_created'] = !empty($builder->searchBuilder->startDate) ?
                    $builder->searchBuilder->startDate->format('Y-m-d') : null;
                $queryParams['to_time_created'] = !empty($builder->searchBuilder->endDate) ?
                    $builder->searchBuilder->endDate->format('Y-m-d') : null;
                $queryParams['order'] = $builder->order;
                $queryParams['order_by'] = $builder->payLinkOrderBy;
                $queryParams['status'] = $builder->searchBuilder->payLinkStatus;
                $queryParams['usage_mode'] = $builder->searchBuilder->paymentMethodUsageMode;
                $queryParams['name'] = $builder->searchBuilder->displayName;
                $queryParams['amount'] = StringUtils::toNumeric($builder->searchBuilder->amount);;
                $queryParams['description'] = $builder->searchBuilder->description;
                $queryParams['reference'] = $builder->searchBuilder->referenceNumber;
                $queryParams['country'] = $builder->searchBuilder->country;
                $queryParams['currency'] = $builder->searchBuilder->currency;
                $queryParams['expiration_date'] = !empty($builder->searchBuilder->expirationDate) ?
                    $builder->searchBuilder->expirationDate->format('Y-m-d') : null;
                break;
            case ReportType::FIND_MERCHANTS_PAGED:
                $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT;
                $verb = 'GET';
                $queryParams['page'] = $builder->page;
                $queryParams['page_size'] = $builder->pageSize;
                break;
            default:
                throw new ArgumentException(sprintf("Unknown report type!"));
        }

        return new GpApiRequest($endpoint, $verb, $payload, $queryParams);
    }

    public function addBasicParams(&$data, $builder)
    {
        $data['page'] = $builder->page;
        $data['page_size'] = $builder->pageSize;
    }

    /**
     * @param ReportBuilder $builder
     * @return array
     */
    private function getDisputesParams($builder)
    {
        return [
            'order_by' => $builder->disputeOrderBy,
            'order' => $builder->order,
            'arn' => $builder->searchBuilder->aquirerReferenceNumber,
            'brand' => $builder->searchBuilder->cardBrand,
            'status' => $builder->searchBuilder->disputeStatus,
            'stage' => $builder->searchBuilder->disputeStage,
            'from_stage_time_created' => !empty($builder->searchBuilder->startStageDate) ?
                $builder->searchBuilder->startStageDate->format('Y-m-d') : null,
            'to_stage_time_created' => !empty($builder->searchBuilder->endStageDate) ?
                $builder->searchBuilder->endStageDate->format('Y-m-d') : null,
            'from_deposit_time_created' => !empty($builder->searchBuilder->startDepositDate) ?
                $builder->searchBuilder->startDepositDate->format('Y-m-d') : null,
            'to_deposit_time_created' => !empty($builder->searchBuilder->endDepositDate) ?
                $builder->searchBuilder->endDepositDate->format('Y-m-d') : null,
            'system.mid' => $builder->searchBuilder->merchantId,
            'system.hierarchy' => $builder->searchBuilder->systemHierarchy,
            'deposit_id' => $builder->searchBuilder->depositReference
        ];
    }

    private function getTransactionParams($builder)
    {
        $queryParams['order_by'] = $builder->transactionOrderBy;
        $queryParams['order'] = $builder->order;
        $queryParams['number_first6'] = $builder->searchBuilder->cardNumberFirstSix;
        $queryParams['number_last4'] = $builder->searchBuilder->cardNumberLastFour;
        $queryParams['brand'] = $builder->searchBuilder->cardBrand;
        $queryParams['brand_reference'] = $builder->searchBuilder->brandReference;
        $queryParams['authcode'] = $builder->searchBuilder->authCode;
        $queryParams['reference'] = $builder->searchBuilder->referenceNumber;
        $queryParams['status'] = $builder->searchBuilder->transactionStatus;
        $queryParams['from_time_created'] = !empty($builder->searchBuilder->startDate) ?
            $builder->searchBuilder->startDate->format('Y-m-d') : null;
        $queryParams['to_time_created'] = !empty($builder->searchBuilder->endDate) ?
            $builder->searchBuilder->endDate->format('Y-m-d') : null;

        return $queryParams;
    }
}