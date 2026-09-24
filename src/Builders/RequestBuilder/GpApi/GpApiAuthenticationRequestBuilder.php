<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\AuthenticationListBuilder;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;

class GpApiAuthenticationRequestBuilder
{
    public static function buildGetAuthenticationsListRequest(AuthenticationListBuilder $builder): GpApiRequest
    {
        $queryParams = array_merge(
            [
                'page' => $builder->page,
                'page_size' => $builder->pageSize,
            ],
            $builder->queryParams
        );

        $queryParams = array_filter(
            $queryParams,
            static fn ($value) => $value !== null && $value !== ''
        );

        return new GpApiRequest(GpApiRequest::AUTHENTICATIONS_ENDPOINT, 'GET', null, $queryParams);
    }
}