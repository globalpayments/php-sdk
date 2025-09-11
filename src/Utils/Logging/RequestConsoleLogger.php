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
        print_r("Request headers: " . json_encode($headers, JSON_PRETTY_PRINT) . PHP_EOL);

        if (!empty($data)) {
            print_r("Request body: " . $data . PHP_EOL);
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