<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;

class GpApiMiCRequestBuilder implements IRequestBuilder
{
    /**
     * Always returns false — MiC requests are handled directly via GpApiConnector,
     * not through the RequestBuilderFactory loop.
     *
     * @param BaseBuilder|null $builder
     * @return bool
     */
    public static function canProcess(?BaseBuilder $builder = null): bool
    {
        return false;
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
        return new GpApiRequest(GpApiRequest::DEVICE_ENDPOINT, "POST", $jsonRequest);
    }
}
