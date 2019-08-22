<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;

abstract class RestGateway extends Gateway
{
    public function __construct()
    {
        parent::__construct('application/json');
    }

    /**
     * @param string $verb
     * @param string $endpoint
     * @param string|null $data
     * @param array<string,string>|null $queryStringParams
     *
     * @throws GatewayException
     * @return string
     */
    protected function doTransaction(
        $verb,
        $endpoint,
        $data = null,
        array $queryStringParams = null,
        $headers = []
    ) {
        $response = $this->sendRequest($verb, $endpoint, $data, $queryStringParams, $headers);

        if (!in_array($response->statusCode, [200, 204])) {
            $parsed = json_decode($response->rawResponse);
            $error = isset($parsed->error) ? $parsed->error : $parsed;
            throw new GatewayException(
                sprintf(
                    'Status Code: %s - %s',
                    $response->statusCode,
                    isset($error->error_description) ? $error->error_description : (isset($error->message) ? $error->message : (string) $error)
                )
            );
        }

        return $response->rawResponse;
    }
}
