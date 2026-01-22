<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Entities\IRequestBuilder;

class GpApiMiCRequestBuilder implements IRequestBuilder
{
    /***
     * @param  $builder
     *
     * @return bool
     */
    public static function canProcess(?BaseBuilder $builder = null): bool
    {
        throw new \GlobalPayments\Api\Entities\Exceptions\NotImplementedException();
    }

    /**
     * @param BaseBuilder $builder
     * @param GpApiConfig $config
     * @return GpApiRequest|string
     */
    public function buildRequest(BaseBuilder $builder, mixed $config): mixed
    {
        throw new \GlobalPayments\Api\Entities\Exceptions\NotImplementedException();
    }
    
    public function buildRequestFromJson(mixed $jsonRequest, mixed $config): mixed
    {
        throw new \GlobalPayments\Api\Entities\Exceptions\NotImplementedException();
    }
}
