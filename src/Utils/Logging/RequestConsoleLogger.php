<?php

namespace GlobalPayments\Api\Utils\Logging;

use GlobalPayments\Api\Entities\IRequestLogger;
use GlobalPayments\Api\Gateways\GatewayResponse;
use GlobalPayments\Api\Utils\StringUtils;

class RequestConsoleLogger implements IRequestLogger
{
    public function requestSent($verb, $endpoint, $headers, $queryStringParams, $data)
    {
        print_r(PHP_EOL . "Request/Response START" . PHP_EOL);
        print_r("Request START" . PHP_EOL);
        print_r("Request verb: " . $verb . PHP_EOL);
        print_r("Request endpoint: " . $endpoint . PHP_EOL);

        // Sanitize sensitive headers before logging
        $sensitiveHeaders = [
            'X-GP-Version', 
            'Accept', 
            'Accept-Encoding', 
            'x-gp-sdk',
            'Content-Type',
            'Content-Length',
            'Authorization'
        ];

        foreach ($headers as $header) {
            $parts = explode(': ', $header, 2);
            if (count($parts) === 2) {
                $key = $parts[0];
                $value = $parts[1];
                $sanitizedHeaders[$key] = in_array($key, $sensitiveHeaders, true) ? 'REDACTED' : $value;
            }
        }
        print_r("Request headers: " . json_encode($sanitizedHeaders, JSON_PRETTY_PRINT) . PHP_EOL);

        if (!empty($data)) {
            // Sanitize sensitive data in the request body
            $sanitizedData = $data;
        
            // If the data is an array or object, convert it to JSON for processing
            if (is_array($sanitizedData) || is_object($sanitizedData)) {
                $sanitizedData = json_encode($sanitizedData, JSON_PRETTY_PRINT);
            }
        
            // Use a regular expression to redact sensitive values associated with specific keys
            $sensitiveKeys = ['app_id', 'nonce', 'secret', 'grant_type'];
            foreach ($sensitiveKeys as $key) {
                $sanitizedData = preg_replace(
                    '/("' . preg_quote($key, '/') . '"\s*:\s*")[^"]+(")/',
                    '$1REDACTED$2',
                    $sanitizedData
                );
            }
        
            print_r("Request body: " . $sanitizedData . PHP_EOL);
        }
        print_r("REQUEST END" . PHP_EOL);
    }

    public function responseReceived(GatewayResponse $response)
    {
        print_r("Response START" . PHP_EOL);
        print_r("Status code: " . $response->statusCode . PHP_EOL);
        $rs = clone $response;
        if (str_contains($rs->header, ': gzip')) {
            $rs->rawResponse = gzdecode($rs->rawResponse);
        }
        if (StringUtils::isJson($rs->rawResponse)) {
            $rs->rawResponse = json_encode(json_decode($rs->rawResponse), JSON_PRETTY_PRINT);
        }
        print_r("Response body: " . $rs->rawResponse . PHP_EOL);
        print_r("Response END" . PHP_EOL);
        print_r("Request/Response END" . PHP_EOL);
        print_r("=============================================");
    }

    public function responseError(\Exception $e, $headers = '')
    {
        print_r("Exception START" . PHP_EOL);
        print_r("Response headers: " . $headers  . PHP_EOL);
        print_r("Error occurred while communicating with the gateway" . PHP_EOL);
        print_r("Exception type: " . get_class($e) . PHP_EOL);
        print_r("Exception message: " . $e->getMessage() . PHP_EOL);
        print_r("Exception END" . PHP_EOL);
        print_r("=============================================");
    }
}