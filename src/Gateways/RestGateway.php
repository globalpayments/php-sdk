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
     * @return string
     * @throws GatewayException
     */
    protected function doTransaction(
        $verb,
        $endpoint,
        $data = null,
        array $queryStringParams = null
    ) {
        if ($this->isGpApi()) {
            if (!empty($data)) {
                $data = (array) $data;
                $this->array_remove_empty($data);
                $data = json_encode($data, JSON_UNESCAPED_SLASHES);
            }
            if (!empty($queryStringParams)){
                $this->array_remove_empty($queryStringParams);
            }
        }

        $response = $this->sendRequest($verb, $endpoint, $data, $queryStringParams);

        if ($this->isGpApi()) {
            if (strpos($response->header, ': gzip') !== false) {
                $response->rawResponse = gzdecode($response->rawResponse);
            }
        }
        if (!in_array($response->statusCode, [200, 204])) {
            $parsed = json_decode($response->rawResponse);
            $error = isset($parsed->error) ? $parsed->error : $parsed;
            if ($this->isGpApi()) {
                $gatewayException = new GatewayException(
                    sprintf(
                        'Status Code: %s - %s',
                        $error->error_code,
                        isset($error->detailed_error_description) ?
                            $error->detailed_error_description :
                            (isset($error->detailed_error_code) ? $error->detailed_error_code : (string)$error)
                    ),
                    (!empty($error->detailed_error_code) ? $error->detailed_error_code : null)
                );
                if ($this->requestLogger) {
                    $this->requestLogger->responseError($gatewayException);
                }
                throw $gatewayException;
            } else {
                throw new GatewayException(
                    sprintf(
                        'Status Code: %s - %s',
                        $response->statusCode,
                        isset($error->error_description) ? $error->error_description : (isset($error->message) ? $error->message : (string)$error)
                    )
                );
            }
        }

        return $response->rawResponse;
    }

    private function isGpApi()
    {
        return $this instanceof GpApiConnector;
    }

    private function array_remove_empty(&$haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $v = (array) $haystack[$key];
                $haystack[$key] = $this->array_remove_empty($v);
            }
            if (empty($haystack[$key])) {
                if (is_null($haystack[$key]) || is_array($haystack[$key]) || $haystack[$key] === '') {
                    unset($haystack[$key]);
                }
            }
        }

        return $haystack;
    }
}
