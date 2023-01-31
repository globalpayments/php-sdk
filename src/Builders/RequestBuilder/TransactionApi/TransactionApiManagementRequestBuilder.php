<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\TransactionApi;

use GlobalPayments\Api\Builders\{BaseBuilder, ManagementBuilder};
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiRequest;
use GlobalPayments\Api\Entities\Enums\{PaymentMethodType, TransactionType, Region};
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\Utils\AmountUtils;

class TransactionApiManagementRequestBuilder implements IRequestBuilder
{
    /**
     * @param $builder
     * @return bool
     */
    public static function canProcess($builder)
    {
        if ($builder instanceof ManagementBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param TransactionApiConfig $config
     *
     * @return TransactionApiRequest|null
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $payload = null;
        /**
         * @var ManagementBuilder $builder
         */

        switch ($builder->transactionType) {
            case TransactionType::REFUND:
                if (isset($builder->paymentMethod) && $builder->paymentMethod->paymentMethodType == PaymentMethodType::ACH) {
                    $verb = 'POST';
                    $payload["check"] = [
                        "account_type" => isset($builder->bankTransferDetails->accountType) ? $builder->bankTransferDetails->accountType : "0.00",
                        "check_number" => isset($builder->bankTransferDetails->checkNumber) ? $builder->bankTransferDetails->checkNumber : null
                    ];
                    $payload["payment"] = [
                        "amount" => isset($builder->amount) ? (string)AmountUtils::transitFormat($builder->amount) : "0.00",
                    ];
                    if ($config->country == Region::CA) {
                        $payload["transaction"] = [
                            "payment_purpose_code" => isset($builder->paymentPurposeCode) ? $builder->paymentPurposeCode : null,
                        ];
                    } else if ($config->country == Region::US) {
                        $payload["transaction"] = [
                            "entry_class" => isset($builder->entryClass) ? $builder->entryClass : null,
                        ];
                    }

                    if (isset($builder->paymentMethod->transactionId)) {
                        $endpoint = TransactionApiRequest::CHECKSALES . '/' .
                            $builder->paymentMethod->transactionId . '/' . TransactionApiRequest::CHECKREFUND;
                    } else if (isset($builder->paymentMethod->clientTransactionId)) {
                        $endpoint = TransactionApiRequest::CHECKSALESREF . '/'
                            . $builder->paymentMethod->clientTransactionId . '/' . TransactionApiRequest::CHECKREFUND;
                    } else {
                        throw new ApiException("Previous transaction ID must be supplied");
                    }
                }
                if (isset($builder->paymentMethod) && $builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    $verb = 'POST';
                    if (isset($builder->paymentMethod->clientTransactionId)) {
                        $endpoint = TransactionApiRequest::CREDITSALEREF . '/'
                            . $builder->paymentMethod->clientTransactionId . '/'
                            . TransactionApiRequest::CREDITREFUND;
                    } else if (isset($builder->paymentMethod->transactionId)) {
                        $endpoint = TransactionApiRequest::CREDITSALE . '/'
                            . $builder->paymentMethod->transactionId . '/'
                            . TransactionApiRequest::CREDITREFUND;
                    } else {
                        throw new ApiException("Previous transaction ID must be supplied");
                    }
                    $payload = [
                        'payment' => [
                            'amount' => isset($builder->amount) ? (string)AmountUtils::transitFormat($builder->amount) : "0.00",
                            "invoice_number" => isset($builder->invoiceNumber) ? $builder->invoiceNumber : null
                        ],
                        'transaction' => [
                            "generate_receipt" => isset($builder->transactionData->generateReceipt)
                                ? $builder->transactionData->generateReceipt : null,
                            "allow_duplicate" => isset($builder->allowDuplicates) ? $builder->allowDuplicates : null
                        ]
                    ];
                }
                break;
            case TransactionType::EDIT:
                $verb = 'GET';
                if ($builder->transactionData) {
                    $verb = 'PATCH';
                    $payload["payment"] = [
                        "amount" => isset($builder->amount) ? (string)AmountUtils::transitFormat($builder->amount) : "0.00",
                        "gratuity_amount" => isset($builder->gratuity) ? $builder->gratuity : null,
                        "invoice_number" => isset($builder->invoiceNumber) ? $builder->invoiceNumber : null
                    ];
                    $payload["transaction"]["processing_indicators"] = [
                        "generate_receipt" => isset($builder->transactionData->generateReceipt)
                            ? $builder->transactionData->generateReceipt : null,
                        "allow_duplicate" => isset($builder->allowDuplicates) ? $builder->allowDuplicates : null
                    ];
                }

                if (isset($builder->paymentMethod) && isset($builder->paymentMethod->transactionId)) {
                    $endpoint = TransactionApiRequest::CREDITSALE . '/' . $builder->paymentMethod->transactionId;
                } else if (isset($builder->paymentMethod) && isset($builder->paymentMethod->clientTransactionId)) {
                    $endpoint = TransactionApiRequest::CREDITSALEREF . '/' . $builder->paymentMethod->clientTransactionId;
                } else {
                    throw new ApiException("Previous transaction ID must be supplied");
                }

                break;
            case TransactionType::VOID:
                $verb = 'PUT';
                $payload["payment"]["amount"] = isset($builder->amount) ? (string)AmountUtils::transitFormat($builder->amount) : "0.00";
                $payload["transaction"]["processing_indicators"]["generate_receipt"] = null;
                if (isset($builder->originalTransactionType) && $builder->originalTransactionType === TransactionType::SALE) {
                    if (isset($builder->paymentMethod->transactionId)) {
                        $endpoint = TransactionApiRequest::CREDITSALE . '/' . $builder->paymentMethod->transactionId . "/" . TransactionApiRequest::CREDITSALEVOID;
                    } else if (isset($builder->paymentMethod->clientTransactionId)) {
                        $endpoint = TransactionApiRequest::CREDITSALEREF . '/' . $builder->paymentMethod->clientTransactionId . "/" . TransactionApiRequest::CREDITSALEVOID;
                    } else {
                        throw new ApiException("Previous transaction ID must be supplied");
                    }
                } else if (isset($builder->originalTransactionType) && $builder->originalTransactionType === TransactionType::REFUND) {
                    if (isset($builder->paymentMethod->transactionId)) {
                        $endpoint = TransactionApiRequest::CREDITREFUND . '/' . $builder->paymentMethod->transactionId . "/" . TransactionApiRequest::CREDITSALEVOID;
                    } else if (isset($builder->paymentMethod->clientTransactionId)) {
                        $endpoint = TransactionApiRequest::CREDITREFUNDREF . '/' . $builder->paymentMethod->clientTransactionId . "/" . TransactionApiRequest::CREDITSALEVOID;
                    } else {
                        throw new ApiException("Previous transaction ID must be supplied");
                    }
                } else {
                    throw new ApiException("Must be either a sale or refund transaction type");
                }

                break;
            default:
                return null;
        }

        return new TransactionApiRequest($endpoint, $verb, $payload);
    }
}
