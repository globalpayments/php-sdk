<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\TransactionApi;

use GlobalPayments\Api\Builders\{BaseBuilder, TransactionReportBuilder};
use GlobalPayments\Api\Entities\Enums\{PaymentMethodType, ReportType};
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiRequest;

class TransactionApiReportRequestBuilder implements IRequestBuilder
{
    public static function canProcess($builder)
    {
        return $builder instanceof TransactionReportBuilder;
    }

    /**
     * @param BaseBuilder $builder
     * @param TransactionApiConfig $config
     * @return TransactionApiRequest|null
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $queryParams = $payload = null;
        /**
         * @var TransactionReportBuilder $builder
         */
        switch ($builder->reportType) {
            case ReportType::TRANSACTION_DETAIL || ReportType::FIND_TRANSACTIONS:
                if (is_object($builder->transactionId)) {
                    $transData = $builder->transactionId;
                    if ($transData->paymentMethodType == PaymentMethodType::CREDIT) {
                        if (isset($transData->transactionReference->transactionId)) {
                            $endpoint = TransactionApiRequest::CREDITREFUND . '/' . $transData->transactionReference->transactionId;
                        }
                        if (isset($transData->transactionReference->clientTransactionId)) {
                            $endpoint = TransactionApiRequest::CREDITREFUNDREF . '/' . $transData->transactionReference->clientTransactionId;
                        }
                    }
                    if ($transData->paymentMethodType == PaymentMethodType::ACH) {
                        if ($transData->originalTransactionType == "REFUND") {
                            if (isset($transData->transactionReference->transactionId)) {
                                $endpoint = TransactionApiRequest::CHECKREFUND . '/' . $transData->transactionReference->transactionId;
                            }
                            if (isset($transData->transactionReference->clientTransactionId)) {
                                $endpoint = TransactionApiRequest::CHECKREFUNDREF . '/' . $transData->transactionReference->clientTransactionId;
                            }
                        } else {
                            if (isset($transData->transactionReference->transactionId)) {
                                $endpoint = TransactionApiRequest::CHECKSALES . '/' . $transData->transactionReference->transactionId;
                            }
                            if (isset($transData->transactionReference->clientTransactionId)) {
                                $endpoint = TransactionApiRequest::CHECKSALESREF . '/' . $transData->transactionReference->clientTransactionId;
                            }
                        }
                    }
                } else {
                    if (isset($builder->transactionId)) {
                        $endpoint = TransactionApiRequest::CREDITSALE . '/' . $builder->transactionId;
                    }
                    if (isset($builder->searchBuilder->clientTransactionId)) {
                        $endpoint = TransactionApiRequest::CREDITSALEREF . '/' . $builder->searchBuilder->clientTransactionId;
                    }
                }

                $verb = 'GET';
                break;
            default:
                return null;
        }
        return new TransactionApiRequest($endpoint, $verb, $payload, $queryParams);
    }
}
