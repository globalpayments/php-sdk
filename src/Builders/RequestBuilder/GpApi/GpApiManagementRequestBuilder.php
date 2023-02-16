<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\GpApi\DTO\Card;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\LodgingData;
use GlobalPayments\Api\Entities\LodgingItems;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\StringUtils;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

class GpApiManagementRequestBuilder implements IRequestBuilder
{
    private static $allowedActions =[
        PaymentMethodType::BANK_PAYMENT => []
    ];

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
     * @param GpApiConfig $config
     *
     * @return GpApiRequest|null
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $payload = null;

        if (!empty($builder->paymentMethod->paymentMethodType)) {
            switch ($builder->paymentMethod->paymentMethodType) {
                case PaymentMethodType::BANK_PAYMENT:
                    if (
                        !isset(self::$allowedActions[PaymentMethodType::BANK_PAYMENT]) ||
                        !in_array($builder->transactionType, self::$allowedActions[PaymentMethodType::BANK_PAYMENT])
                    ) {
                        throw new BuilderException(
                            sprintf(
                                "The %s is not supported for %s",
                                $this->getTransactionTypeName($builder->transactionType), PaymentMethodName::BANK_PAYMENT
                            )
                        );
                    }
                default:
                    break;
            }
        }

        /**
         * @var ManagementBuilder $builder
         */
        switch ($builder->transactionType) {
            case TransactionType::TOKEN_DELETE:
                $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token;
                $verb = 'DELETE';
                break;
            case TransactionType::TOKEN_UPDATE:
                if (!$builder->paymentMethod instanceof CreditCardData) {
                    throw new GatewayException("Payment method doesn't support this action!");
                }
                $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token;
                $verb = 'PATCH';
                $card = new Card();
                $builderCard = $builder->paymentMethod;

                $card->expiry_month = !empty($builderCard->expMonth) ? (string)$builderCard->expMonth : null;
                $card->expiry_year = !empty($builderCard->expYear) ?
                    substr(str_pad($builderCard->expYear, 4, '0', STR_PAD_LEFT), 2, 2) : null;
                $card->number = !empty($builderCard->number) ? $builderCard->number : null;
                $payload = [
                    'usage_mode' => !empty($builder->paymentMethodUsageMode) ? $builder->paymentMethodUsageMode : null,
                    'name' => !empty($builderCard->cardHolderName) ? $builderCard->cardHolderName : null,
                    'card' => $card
                ];

                break;
            case TransactionType::REFUND:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/refund';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                $payload['currency_conversion'] = !empty($builder->dccRateData) ? $this->getDccRate($builder->dccRateData) : null;
                break;
            case TransactionType::REVERSAL:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/reversal';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                $payload['currency_conversion'] = !empty($builder->dccRateData) ? $this->getDccRate($builder->dccRateData) : null;
                break;
            case TransactionType::CAPTURE:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/capture';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                $payload['gratuity'] = StringUtils::toNumeric($builder->gratuity);
                $payload['currency_conversion'] = !empty($builder->dccRateData) ? $this->getDccRate($builder->dccRateData) : null;
                break;
            case TransactionType::DISPUTE_ACCEPTANCE:
                $endpoint = GpApiRequest::DISPUTES_ENDPOINT . '/' . $builder->disputeId . '/acceptance';
                $verb = 'POST';
                break;
            case TransactionType::DISPUTE_CHALLENGE:
                $endpoint = GpApiRequest::DISPUTES_ENDPOINT . '/' . $builder->disputeId . '/challenge';
                $verb = 'POST';
                $payload['documents'] = $builder->disputeDocuments;
                break;
            case TransactionType::BATCH_CLOSE:
                $endpoint = GpApiRequest::BATCHES_ENDPOINT . '/' . $builder->batchReference;
                $verb = 'POST';
                break;
            case TransactionType::REAUTH:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/reauthorization';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::ACH) {
                    $payload['description'] = $builder->description;
                    if (!empty($builder->bankTransferDetails)) {
                        $eCheck = $builder->bankTransferDetails;
                        $payload['payment_method'] = [
                            'narrative' => $eCheck->merchantNotes,
                            'bank_transfer' => [
                                'account_number' => $eCheck->accountNumber,
                                'account_type' => EnumMapping::mapAccountType(GatewayProvider::GP_API, $eCheck->accountType),
                                'check_reference' => $eCheck->checkReference,
                                'bank' => [
                                    'code' => $eCheck->routingNumber,
                                    'name' => $eCheck->bankName
                                ]
                            ]
                        ];
                    }
                }
                break;
            case TransactionType::CONFIRM:
                if (
                    $builder->paymentMethod instanceof TransactionReference &&
                    $builder->paymentMethod->paymentMethodType == PaymentMethodType::APM
                ) {
                    $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/confirmation';
                    $verb = 'POST';
                    $apmResponse = $builder->paymentMethod->alternativePaymentResponse;
                    $payload = [
                        'payment_method' => [
                            'apm' => [
                                'provider' => $apmResponse->providerName,
                                'provider_payer_reference' => $apmResponse->providerReference
                            ]
                        ]
                    ];
                }
                break;
            case TransactionType::AUTH:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/incremental';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                if (!empty($builder->lodgingData)) {
                    /** @var LodgingData $lodging */
                    $lodging = $builder->lodgingData;
                    if (!empty($lodging->items)) {
                        $lodgingItems = [];
                        /** @var LodgingItems $item */
                        foreach ($lodging->items as $item) {
                            $lodgingItems[] = [
                                'types' => $item->types,
                                'reference' => $item->reference,
                                'total_amount' => !empty($item->totalAmount) ? StringUtils::toNumeric($item->totalAmount) : null,
                                'payment_method_program_codes' => $item->paymentMethodProgramCodes
                            ];
                        }
                    }

                    $payload['lodging'] = [
                        'booking_reference' => $lodging->bookingReference,
                        'duration_days' => $lodging->durationDays,
                        'date_checked_in' => !empty($lodging->checkedInDate) ?
                            (new \DateTime($lodging->checkedInDate))->format('Y-m-d') : null,
                        'date_checked_out' => !empty($lodging->checkedOutDate) ?
                            (new \DateTime($lodging->checkedOutDate))->format('Y-m-d') : null,
                        'daily_rate_amount' => !empty($lodging->dailyRateAmount) ?
                            StringUtils::toNumeric($lodging->dailyRateAmount) : null,
                        'lodging.charge_items' => !empty($lodgingItems) ? $lodgingItems : null
                    ];
                }
                break;
            case TransactionType::EDIT:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId .
                    '/adjustment';
                $verb = 'POST';
                $payload = [
                    'amount' => StringUtils::toNumeric($builder->amount),
                    'gratuity_amount' => StringUtils::toNumeric($builder->gratuity),                     
                    'payment_method' => [
                            'card' => ['tag' => $builder->tagData]
                        ]
                    ];
                break;
            case TransactionType::PAYLINK_UPDATE:
                $endpoint = GpApiRequest::PAYLINK_ENDPOINT . '/' . $builder->paymentLinkId;
                $verb = 'PATCH';
                $payload = [
                    'usage_mode'=> $builder->payLinkData->usageMode ?? null,
                    'usage_limit' => $builder->payLinkData->usageLimit ?? null,
                    'name' => $builder->payLinkData->name ?? null,
                    'description' => $builder->description ?? null,
                    'type' => $builder->payLinkData->type ?? null,
                    'status' => $builder->payLinkData->status ?? null,
                    'shippable' => isset($builder->payLinkData->shippable) ?
                        json_encode($builder->payLinkData->shippable) : null,
                    'shipping_amount' => !empty($builder->payLinkData->shippingAmount) ?
                        StringUtils::toNumeric($builder->payLinkData->shippingAmount) : null,
                    'transactions' => [
                        'amount' => !empty($builder->amount) ? StringUtils::toNumeric($builder->amount) : null
                    ],
                    'expiration_date' => !empty($payLink->expirationDate) ?
                        (new \DateTime($payLink->expirationDate))->format('Y-m-d\TH:i:s\Z') : null,

                    'images' => $builder->payLinkData->images ?? null,
                ];
                break;
            case TransactionType::RELEASE:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/release';
                $verb = 'POST';
                $payload = [
                    'reason_code' => EnumMapping::mapReasonCode(GatewayProvider::GP_API, $builder->reasonCode) ?? null
                ];
                break;
            case TransactionType::HOLD:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/hold';
                $verb = 'POST';
                $payload = [
                    'reason_code' => EnumMapping::mapReasonCode(GatewayProvider::GP_API, $builder->reasonCode) ?? null
                ];
                break;
            default:
                return null;
        }

        return new GpApiRequest($endpoint, $verb, $payload);
    }

    /**
     * @param DccRateData dccRateData
     * @return array
     */
    private function getDccRate($dccRateData)
    {
         return [
             'id' => $dccRateData->dccId
         ];
    }

    private function getTransactionTypeName($transactionType)
    {
        $reflector = new \ReflectionClass(TransactionType::class);

        return array_search($transactionType,$reflector->getConstants());
    }
}