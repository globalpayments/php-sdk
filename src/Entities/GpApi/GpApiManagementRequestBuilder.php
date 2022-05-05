<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\GpApi\DTO\Card;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\Lodging;
use GlobalPayments\Api\Entities\LodgingItems;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\StringUtils;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

class GpApiManagementRequestBuilder implements IRequestBuilder
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
     * @param GpApiConfig $config
     *
     * @return GpApiRequest|null
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $payload = null;
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
                if (!empty($builder->lodging)) {
                    /** @var Lodging $lodging */
                    $lodging = $builder->lodging;
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
                        'date_checked_in' => !empty($lodging->dateCheckedIn) ?
                            (new \DateTime($lodging->dateCheckedIn))->format('Y-m-d') : null,
                        'date_checked_out' => !empty($lodging->dateCheckedOut) ?
                            (new \DateTime($lodging->dateCheckedOut))->format('Y-m-d') : null,
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
}