<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Entities\GpApi\GpApiRequest;

/**
 * Request builder for GPAPI payer operations
 */
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

    /**
     * Build request to create new payer
     *
     * @param array $payerDetails The payer details to create
     * @return GpApiRequest The configured request object
     */
    public static function buildCreatePayerRequest(array $payerDetails): GpApiRequest
    {
        $request = new GpApiRequest(GpApiRequest::PAYERS_ENDPOINT, 'POST', $payerDetails);
        return $request;
    }

    /**
     * Build a request to update an existing payer
     *
     * @param string $payerId The unique identifier of the payer
     * @param array $payerDetails The updated payer details
     * @return GpApiRequest The configured request object
     */
    public static function buildEditPayerRequest(string $payerId, array $payerDetails): GpApiRequest
    {
        $endpoint = GpApiRequest::PAYERS_ENDPOINT . '/' . $payerId;
        if (isset($payerDetails["payment_methods"]) && empty($payerDetails["payment_methods"])) {
            // As per the documentation, in certain circumstances we need empty array included
            // in the request
            $payerDetails['__PRESERVE_EMPTY__'] = ["payment_methods"];
        }
        $request = new GpApiRequest($endpoint, 'PATCH', $payerDetails);
        return $request;
    }
}
