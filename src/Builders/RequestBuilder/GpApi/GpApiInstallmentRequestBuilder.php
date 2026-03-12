<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\{
    BaseBuilder, 
    InstallmentBuilder
};
use GlobalPayments\Api\Entities\{ 
    IRequestBuilder, 
    Request
};
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\PaymentMethods\Installment;  
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;

class GpApiInstallmentRequestBuilder implements IRequestBuilder
{
    /**
     * @param InstallmentBuilder $builder
     * @return bool
    */
    public static function canProcess(?BaseBuilder $builder = null) : bool
    {
        if ($builder instanceof InstallmentBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param InstallmentBuilder $builder
     * @param GpApiConfig $config
     *
     * @return Request
     */
    public function buildRequest(BaseBuilder $builder, mixed $config) : GpApiRequest
    {
        $installmentRequest = null;
        
        switch ($builder->transactionType) {
            case TransactionType::CREATE:
                $endpoint = GpApiRequest::INSTALLMENT_ENDPOINT;
                $verb = 'POST';
                if ($builder->entity instanceof Installment)
                    $installmentRequest = $this->prepareInstallmentRequest($builder);
                break;
            
            case TransactionType::FETCH:
                $endpoint = GpApiRequest::INSTALLMENT_ENDPOINT . '/' . $builder->installmentId;
                $verb = 'GET';
                break;
                
            default:
                throw new \GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException(
                    'Unsupported transaction type for installment requests'
                );
        }
        
        return new GpApiRequest($endpoint, $verb, $installmentRequest);
    }

    private function prepareInstallmentRequest($installment) : array
    {
        $requestData = [];
        $requestData['account_name'] = $installment->entity->accountName;
        $requestData['amount'] = $installment->entity->amount;
        $requestData['channel'] = $installment->entity->channel;
        $requestData['currency'] = $installment->entity->currency;
        $requestData['country'] = $installment->entity->country;
        $requestData['reference'] = $installment->entity->reference;
        $requestData['program'] = $installment->entity->program;

        // Add Visa installment specific fields
        if (!empty($installment->entity->funding_mode)) {
            $requestData['funding_mode'] = $installment->entity->funding_mode;
        }
        
        if (!empty($installment->entity->terms)) {
            $termsData = [];
            if (!empty($installment->entity->terms->time_unit)) {
                $termsData['time_unit'] = $installment->entity->terms->time_unit;
            }
            if (!empty($installment->entity->terms->max_time_unit_number)) {
                $termsData['max_time_unit_number'] = $installment->entity->terms->max_time_unit_number;
            }
            if (!empty($installment->entity->terms->max_amount)) {
                $termsData['max_amount'] = $installment->entity->terms->max_amount;
            }
            if (!empty($termsData)) {
                $requestData['terms'] = $termsData;
            }
        }
        
        if (!empty($installment->entity->eligible_plans)) {
            $requestData['eligible_plans'] = $installment->entity->eligible_plans;
        }

        $cardData = $installment->entity->cardDetails;

        $paymentMethod = new PaymentMethod();
        $paymentMethod->entry_mode = $installment->entity->entryMode;
        $paymentMethod->usage_mode = $installment->entity->usage_mode ?? 'USE_CARD_NUMBER';

        $cardBrand = null;
        if (!empty($cardData?->number)) {
            $cardBrand = strtoupper((string)$cardData->getCardType());
        }

        $expMonth = $cardData->expMonth ?? '';
        $expYear = !empty($cardData->expYear) ?
            substr(str_pad($cardData->expYear, 4, '0', STR_PAD_LEFT), 2, 2) : '';

        $paymentMethod->card = (object) [
                'brand' => $cardBrand,
                'number' => $cardData->number ?? '',
                'expiry_month' => $expMonth,
                'expiry_year' => $expYear
            ];
        $requestData['payment_method'] = $paymentMethod;
    
        return $requestData;
    }

    /**
     * @param mixed $jsonRequest
     * @param mixed $config
     */
    public function buildRequestFromJson(mixed $jsonRequest, mixed $config): mixed
    {
        throw new \GlobalPayments\Api\Entities\Exceptions\NotImplementedException();
    }
}
