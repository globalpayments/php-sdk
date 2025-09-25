<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\Request;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;

class GpApiRecurringRequestBuilder implements IRequestBuilder
{
    private $builder;
    /***
     * @param RecurringBuilder $builder
     *
     * @return bool
     */
    public static function canProcess($builder = null)
    {
        if ($builder instanceof RecurringBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param GpApiConfig $config
     *
     * @return Request
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        /** @var RecurringBuilder $builder */
        $this->builder = $builder;
        $requestData = [];
        /**
         * @var RecurringBuilder $builder
         */
        switch ($builder->transactionType) {
            case TransactionType::CREATE:
                $endpoint = GpApiRequest::PAYERS_ENDPOINT;
                $verb = 'POST';
                if ($builder->entity instanceof Customer) {
                    $this->preparePayerRequest($requestData);
                }
                break;
            case TransactionType::EDIT:
                $endpoint = GpApiRequest::PAYERS_ENDPOINT . '/' . $this->builder->entity->id;
                $verb = 'PATCH';
                if ($builder->entity instanceof Customer) {
                    $this->preparePayerRequest($requestData);
                }
                break;
        }
        return new GpApiRequest($endpoint, $verb, $requestData);
    }

    private function preparePayerRequest(array &$requestData)
    {
        /** @var Customer $customer */
        $customer = $this->builder->entity;
        $requestData['first_name'] = $customer->firstName;
        $requestData['last_name'] = $customer->lastName;
        $requestData['reference'] = $customer->key;
        if (!empty($customer->paymentMethods)) {
            foreach ($customer->paymentMethods as $index => $paymentMethod) {
                $requestData['payment_methods'][] =  [
                    'id' => $index,
                    'default' =>  $index === array_key_first($customer->paymentMethods) ? 'YES' : 'NO',
                ];
            }
        }
    }

    public function buildRequestFromJson($jsonRequest, $config)
    {
        // TODO: Implement buildRequestFromJson() method.
    }
}