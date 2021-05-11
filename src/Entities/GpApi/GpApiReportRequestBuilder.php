<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\StringUtils;

class GpApiReportRequestBuilder implements IRequestBuilder
{
    public static function canProcess($builder)
    {
        if ($builder instanceof TransactionReportBuilder) {
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
        $queryParams = [];
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
                $queryParams['order_by'] = $builder->depositOrderBy;
                $queryParams['order'] = $builder->order;
                $queryParams['amount'] = StringUtils::toNumeric($builder->searchBuilder->amount);
                $queryParams['from_time_created'] = !empty($builder->startDate) ?
                    $builder->startDate->format('Y-m-d') : null;
                $queryParams['to_time_created'] = !empty($builder->endDate) ?
                    $builder->endDate->format('Y-m-d') : null;
                $queryParams['id'] = $builder->searchBuilder->depositId;
                $queryParams['status'] = $builder->searchBuilder->depositStatus;
                $queryParams['masked_account_number_last4'] = $builder->searchBuilder->accountNumberLastFour;
                $queryParams['system.mid'] = $builder->searchBuilder->merchantId;
                $queryParams['system.hierarchy'] = $builder->searchBuilder->systemHierarchy;
                break;
            case ReportType::FIND_TRANSACTIONS_PAGED:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams['id'] = $builder->transactionId;
                $queryParams['type'] = $builder->searchBuilder->paymentType;
                $queryParams['channel'] = $builder->searchBuilder->channel;
                $queryParams['amount'] = StringUtils::toNumeric($builder->searchBuilder->amount);
                $queryParams['currency'] = $builder->searchBuilder->currency;
                $queryParams['token_first6'] = $builder->searchBuilder->tokenFirstSix;
                $queryParams['token_last4'] = $builder->searchBuilder->tokenLastFour;
                $queryParams['account_name'] = $builder->searchBuilder->accountName;
                $queryParams['country'] = $builder->searchBuilder->country;
                $queryParams['batch_id'] = $builder->searchBuilder->batchId;
                $queryParams['entry_mode'] = $builder->searchBuilder->paymentEntryMode;
                $queryParams['name'] = $builder->searchBuilder->name;
                $queryParams = array_merge($queryParams,  $this->getTransactionParams($builder));
                break;
            case ReportType::FIND_SETTLEMENT_TRANSACTIONS_PAGED:
                $endpoint = GpApiRequest::SETTLEMENT_TRANSACTIONS_ENDPOINT;
                $verb = 'GET';
                $this->addBasicParams($queryParams, $builder);
                $queryParams['account_name'] = $config->accessTokenInfo->dataAccountName;
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
                $queryParams = array_merge($queryParams, $this->getDisputesParams($builder));
                break;
            case ReportType::FIND_STORED_PAYMENT_METHODS_PAGED:
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
            default:
                return null;
        }

        return new GpApiRequest($endpoint, $verb, null, $queryParams);
    }

    public function addBasicParams(&$data, $builder)
    {
        $data['page'] = $builder->page;
        $data['page_size'] = $builder->pageSize;
    }

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
            'system.mid' => $builder->searchBuilder->merchantId,
            'system.hierarchy' => $builder->searchBuilder->systemHierarchy
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