<?php

namespace GlobalPayments\Api\Gateways\BillPay;

use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Entities\BillPay\Credentials;
use GlobalPayments\Api\Entities\IRequestLogger;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Gateways\BillPay\Requests\GetTransactionByOrderIDRequest;
use GlobalPayments\Api\Gateways\BillPay\Responses\TransactionByOrderIDRequestResponse;
use GlobalPayments\Api\Utils\ElementTree;

class ReportRequest extends GatewayRequestBase 
{
    public function __construct(Credentials $credentials, string $serviceUrl, int $timeout, ?IRequestLogger $requestLogger = null)
    {
        parent::__construct();
        $this->credentials = $credentials;
        $this->serviceUrl = $serviceUrl;
        $this->timeout = $timeout;

        $this->requestLogger = $requestLogger;
    }

    public function execute(ReportBuilder $builder): TransactionSummary
    {
        $et = new ElementTree();
        $envelope = $this->createSOAPEnvelope($et, "GetTransactionByOrderID");
        $getTransactionByOrderIDRequest = new GetTransactionByOrderIDRequest($et);
        $request = $getTransactionByOrderIDRequest->build(
            $envelope,
            $builder,
            $this->credentials
        );

        /** @var string */
        $response = $this->doTransaction($request, $this->publicEndpoint);
        $transactionByOrderIDRequestResponse = new TransactionByOrderIDRequestResponse();

        /** @var TransactionSummary */
        $result = $transactionByOrderIDRequestResponse
            ->withResponseTagName("GetTransactionByOrderIDResponse")
            ->withResponse($response)
            ->map();

        return $result;
    }
}