<?php

namespace GlobalPayments\Api\Gateways;

use Exception;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Utils\ArrayUtils;

abstract class RestGateway extends Gateway
{
    protected ?GatewayResponse $lastGatewayResponse = null;

    public function __construct()
    {
        parent::__construct('application/json');
    }
    
    /**
     * 
     * @param mixed $verb 
     * @param mixed $endpoint 
     * @param mixed $data 
     * @param null|array $queryStringParams 
     * @return string 
     * @throws Exception 
     * @throws GatewayException 
     */
    protected function doTransaction(
        $verb,
        $endpoint,
        $data = null,
        ?array $queryStringParams = null
    ) {
        if ($this->isGpApi() || $this->isTransactionApi()) {
            if (!empty($data)) {
                $data = (array) $data;
                $data = ArrayUtils::array_remove_empty($data);
                $data = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
            }
            if (!empty($queryStringParams)) {
                $queryStringParams = ArrayUtils::array_remove_empty($queryStringParams);
            }
        }

        $this->lastGatewayResponse = $response = $this->sendRequest(
            $verb,
            $endpoint,
            $data,
            $queryStringParams
        );

        if ($this->isGpApi() || $this->isTransactionApi()) {
            if (strpos($response->header, ': gzip') !== false) {
                $response->rawResponse = gzdecode($response->rawResponse);
            }
        }

        $statusCodes = [200, 204, 201];

        if ($this->isTransactionApi()) {
            $statusCodes = [200, 204, 201, 473, 471, 470];
        }

        if (!in_array($response->statusCode, $statusCodes)) {
            $parsed = json_decode($response->rawResponse);
            $error = isset($parsed->error) ? $parsed->error : $parsed;
            if (empty($error)) {
                throw new GatewayException(sprintf('Status Code: %s', $response->statusCode));
            }

            if ($this->isGpApi()) {
                $gatewayException = new GatewayException(
                    sprintf(
                        'Status Code: %s - %s',
                        $error->error_code,
                        isset($error->detailed_error_description) ?
                            $error->detailed_error_description : (isset($error->detailed_error_code) ? $error->detailed_error_code : (string)$error)
                    ),
                    (!empty($error->detailed_error_code) ? $error->detailed_error_code : null)
                );
                $gatewayException->errorCode = $error->error_code ?? null;
                $gatewayException->detailedErrorCode = $error->detailed_error_code ?? null;
                $gatewayException->detailedErrorDescription = $error->detailed_error_description ?? null;
                $gatewayException->httpStatusCode = $response->statusCode ?? null;
                $gatewayException->responseHeaders = $this->parseHeaders($response->header ?? null);
                if ($this->requestLogger) {
                    $this->requestLogger->responseError($gatewayException, $response->header);
                }
                throw $gatewayException;
            } else if ($this->isTransactionApi()) {
                if (isset($error) && isset($error->detail)) {
                    $gatewayException = new GatewayException(
                        sprintf(
                            'Status Code: %s - %s',
                            $error->code,
                            isset($error->detail) && isset($error->detail[0]->description) ?
                                $error->detail[0]->description : (isset($error->message) ? $error->message : (string)$error)
                        ),
                        (!empty($error->code) ? $error->code : null)
                    );
                    if ($this->requestLogger) {
                        $this->requestLogger->responseError($gatewayException);
                    }
                    throw $gatewayException;
                }
            } else {
                $errMsgProperty = ['error_description', 'error_detail', 'message', 'eos_reason'];
                $errorMessage = '';
                foreach ($errMsgProperty as $propertyName) {
                    if (property_exists($error, $propertyName)) {
                        if (is_string($error->{$propertyName})) {
                            $errorMessage .= $error->{$propertyName} . ' ';
                        }
                    }
                }
                throw new GatewayException(
                    sprintf(
                        'Status Code: %s - %s',
                        $response->statusCode,
                        !empty($errorMessage) ? $errorMessage : serialize($error)
                    )
                );
            }
        }

        return $response->rawResponse;
    }

    protected function parseHeaders(?string $rawHeaders): array
    {
        if ($rawHeaders === null || trim($rawHeaders) === '') {
            return [];
        }

        $normalizedHeaders = [];
        $headerBlocks = preg_split("/\r\n\r\n|\n\n|\r\r/", trim($rawHeaders)) ?: [];
        $headerLines = preg_split("/\r\n|\n|\r/", (string) end($headerBlocks)) ?: [];

        foreach ($headerLines as $headerLine) {
            if (!str_contains($headerLine, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $headerLine, 2);
            $normalizedHeaders[strtolower(trim($name))] = trim($value);
        }

        return $normalizedHeaders;
    }

    private function isGpApi()
    {
        return $this instanceof GpApiConnector;
    }

    private function isTransactionApi()
    {
        return $this instanceof TransactionApiConnector;
    }
}
