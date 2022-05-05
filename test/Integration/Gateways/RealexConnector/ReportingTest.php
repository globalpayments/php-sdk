<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

class ReportingTest extends TestCase
{
    protected function config()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "api";
        $config->refundPassword = 'refund';
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        return $config;
    }

    public function setup()
    {
        ServicesContainer::configureService($this->config());
    }

    public function testGetTransactionDetail()
    {
        $this->markTestSkipped('You need a valid transaction id to run this test!');
        $transactionId = "u2RjrtEmaU2f0pB-aGw4Eg";

        /** @var TransactionSummary $response */
        $response = ReportingService::transactionDetail($transactionId)->execute();
        $this->assertNotNull($response);
        $this->assertEquals($transactionId, $response->orderId);
    }

    public function testGetTransactionDetailWithRandomId()
    {
        $transactionId =  GenerationUtils::getGuid();
        try{
            /** @var TransactionSummary $response */
            $response = ReportingService::transactionDetail($transactionId)->execute();
        } catch (GatewayException $ex) {
            $this->assertEquals('508', $ex->responseCode);
            $this->assertEquals('Original transaction not found.', $ex->responseMessage);
        }
    }
}