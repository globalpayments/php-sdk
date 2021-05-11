<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\GpApi\DTO\Card;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\StringUtils;

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
        $payload = [];
        switch ($builder->transactionType) {
            case TransactionType::TOKEN_DELETE:
                $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token;
                $verb = 'DELETE';
                break;
            case TransactionType::TOKEN_UPDATE:
                $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT . '/' . $builder->paymentMethod->token;
                $verb = 'PATCH';
                $card = new Card();
                $builderCard = $builder->paymentMethod;
                $card->expiry_month = (string)$builderCard->expMonth;
                $card->expiry_year = substr(str_pad($builderCard->expYear, 4, '0', STR_PAD_LEFT), 2, 2);
                $payload['card'] = $card;
                break;
            case TransactionType::REFUND:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/refund';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                break;
            case TransactionType::REVERSAL:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/reversal';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                break;
            case TransactionType::CAPTURE:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT . '/' . $builder->paymentMethod->transactionId . '/capture';
                $verb = 'POST';
                $payload['amount'] = StringUtils::toNumeric($builder->amount);
                $payload['gratuity'] = StringUtils::toNumeric($builder->gratuity);
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
                break;
            default:
                return null;
        }

        return new GpApiRequest($endpoint, $verb, $payload);
    }
}