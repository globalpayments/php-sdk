<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;

abstract class XmlGateway extends Gateway
{
    public function __construct()
    {
        parent::__construct('text/xml');
    }

    /**
     * @param string $request Raw request XML
     *
     * @throws GatewayException
     * @return string
     */
    protected function doTransaction($request)
    {
        $response = $this->sendRequest('POST', '', $request);

        if (200 !== $response->statusCode) {
            throw new GatewayException(
                sprintf(
                    'Unexpected HTTP status code [%s]',
                    $response->statusCode
                )
            );
        }

        return $response->rawResponse;
    }
}
