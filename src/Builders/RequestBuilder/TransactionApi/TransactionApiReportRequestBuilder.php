<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\TransactionApi;

use GlobalPayments\Api\Builders\{BaseBuilder, TransactionReportBuilder};
use GlobalPayments\Api\Entities\Enums\{PaymentMethodType, PaymentType, ReportType};
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
        $queryParams = $payload = $endpoint = null;
        /**
         * @var TransactionReportBuilder $builder
         */
        switch ($builder->reportType) {
            case ReportType::TRANSACTION_DETAIL:
                $verb = 'GET';
                $endpoint = TransactionApiRequest::CREDITSALE . '/' . $builder->transactionId;
                break;
            case ReportType::FIND_TRANSACTIONS:
                $verb = 'GET';

                switch ($builder->searchBuilder->paymentMethodType)
                {
                    case PaymentMethodType::CREDIT:
                        if (isset($builder->transactionId)) {
                            $endpoint = TransactionApiRequest::CREDITREFUND . '/' . $builder->transactionId;
                        }
                        if (isset($builder->searchBuilder->clientTransactionId)) {
                            $endpoint = TransactionApiRequest::CREDITREFUNDREF . '/' . $builder->searchBuilder->clientTransactionId;
                        }
                        break;
                    case PaymentMethodType::ACH:
                        if ($builder->searchBuilder->paymentType == PaymentType::REFUND) {
                            if (isset($builder->transactionId)) {
                                $endpoint = TransactionApiRequest::CHECKREFUND . '/' . $builder->transactionId;
                            }
                            if (isset($builder->searchBuilder->clientTransactionId)) {
                                $endpoint = TransactionApiRequest::CHECKREFUNDREF . '/' . $builder->searchBuilder->clientTransactionId;
                            }
                        } else {
                            if (isset($builder->transactionId)) {
                                $endpoint = TransactionApiRequest::CHECKSALES . '/' . $builder->transactionId;
                            }
                            if (isset($builder->searchBuilder->clientTransactionId)) {
                                $endpoint = TransactionApiRequest::CHECKSALESREF . '/' . $builder->searchBuilder->clientTransactionId;
                            }
                        }
                        break;
                    default:
                        if (isset($builder->searchBuilder->clientTransactionId)) {
                            $endpoint = TransactionApiRequest::CREDITSALEREF . '/' . $builder->searchBuilder->clientTransactionId;
                        }
                        break;
                }
                break;
            default:
                return null;
        }

        return new TransactionApiRequest($endpoint, $verb, $payload, $queryParams);
    }
}
