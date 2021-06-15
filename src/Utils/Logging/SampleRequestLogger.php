<?php


namespace GlobalPayments\Api\Utils\Logging;


use GlobalPayments\Api\Entities\IRequestLogger;
use GlobalPayments\Api\Gateways\GatewayResponse;
use Psr\Log\LoggerInterface;

class SampleRequestLogger implements IRequestLogger
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function requestSent($verb, $endpoint, $headers, $queryStringParams, $data)
    {
        $this->logger->info("Request/Response START");
        $this->logger->info("Request START");
        $this->logger->info("Request verb: " . $verb);
        $this->logger->info("Request endpoint: " . $endpoint);
        $this->logger->info("Request headers: ", $headers);
        $this->logger->info("Request body: " . $data);
        $this->logger->info("REQUEST END");
    }


    public function responseReceived(GatewayResponse $response)
    {
        $this->logger->info("Response START");
        $this->logger->info("Status code: " . $response->statusCode);
        $rs = clone $response;
        if (strpos($rs->header, ': gzip') !== false) {
            $rs->rawResponse = gzdecode($rs->rawResponse);
        }
        $this->logger->info("Response body: " . $rs->rawResponse);
        $this->logger->info("Response END");
        $this->logger->info("Request/Response END");
        $this->logger->info("=============================================");
    }

    public function responseError(\Exception $e)
    {
        $this->logger->info("Exception START");
        $this->logger->info("Error occurred while communicating with the gateway");
        $this->logger->info("Exception type: " . get_class($e));
        $this->logger->info("Exception message: " . $e->getMessage());
        $this->logger->info("Exception END");
        $this->logger->info("=============================================");
    }


}