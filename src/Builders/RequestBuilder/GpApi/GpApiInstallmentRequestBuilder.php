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
    public static function canProcess($builder = null) : bool
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
    public function buildRequest(BaseBuilder $builder, $config) : Request
    {
        switch ($builder->transactionType) {
            case TransactionType::CREATE:
                $endpoint = GpApiRequest::INSTALLMENT_ENDPOINT;
                $verb = 'POST';
                if ($builder->entity instanceof Installment)
                    $installmentRequest = $this->prepareInstallmentRequest($builder);
            
                break;
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

        $cardData = $installment->entity->cardDetails;

        $paymentMethod = new PaymentMethod();
        $paymentMethod->entry_mode = $installment->entity->entryMode;

        $expMonth = $cardData->expMonth ?? '';
        $expYear = !empty($cardData->expYear) ?
            substr(str_pad($cardData->expYear, 4, '0', STR_PAD_LEFT), 2, 2) : '';

        $paymentMethod->card = (object) [
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
    public function buildRequestFromJson(mixed $jsonRequest, mixed $config){}
}
