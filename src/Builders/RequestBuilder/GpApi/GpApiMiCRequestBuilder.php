<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\RequestBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\PayFac\BankAccountData;
use GlobalPayments\Api\Entities\PayFac\UserPersonalData;
use GlobalPayments\Api\Entities\Person;
use GlobalPayments\Api\Entities\Product;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\GenerationUtils;

class GpApiMiCRequestBuilder implements IRequestBuilder
{
    /***
     * @param  $builder
     *
     * @return bool
     */
    public static function canProcess($builder = null)
    {
        // TODO: Implement buildRequest() method.
    }

    /**
     * @param BaseBuilder $builder
     * @param GpApiConfig $config
     * @return GpApiRequest|string
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        // TODO: Implement buildRequest() method.
    }
    
    public function buildRequestFromJson($jsonRequest, $config)
    {
        $endpoint = GpApiRequest::DEVICE_ENDPOINT;
        $verb = 'POST';

        return new GpApiRequest($endpoint, $verb, $jsonRequest);
    }
}