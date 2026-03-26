<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Entities\GpApi\GpApiRequest;

class GpApiPayerRequestBuilder
{
    public static function buildGetPayersListRequest(array $queryParams = []): GpApiRequest
    {
        $request = new GpApiRequest(GpApiRequest::PAYERS_ENDPOINT, 'GET', '', $queryParams);
        return $request;
    }

    public static function buildGetPayerByIdRequest(string $payerId): GpApiRequest
    {
        $endpoint = GpApiRequest::PAYERS_ENDPOINT . '/' . $payerId;
        $request = new GpApiRequest($endpoint, 'GET');
        return $request;
    }
}
