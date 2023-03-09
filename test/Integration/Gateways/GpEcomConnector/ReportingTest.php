<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector;

use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

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

    public function setup(): void
    {
        ServicesContainer::configureService($this->config());
    }

    public function testGetTransactionDetail()
    {
        $transactionId = "rMuOHxP5SCyCzOYE8mKCsA";

        /** @var TransactionSummary $response */
        $response = ReportingService::transactionDetail($transactionId)->execute();
        $this->assertNotNull($response);
        $this->assertEquals($transactionId, $response->orderId);
        $this->assertEquals('5CoDxmuV5efGltP9', $response->schemeReferenceData);
        $this->assertEquals('U', $response->avsResponseCode);
        $this->assertEquals('M', $response->cvnResponseCode);
        $this->assertEquals('00', $response->gatewayResponseCode);
        $this->assertEquals('(00)[ test system ] Authorised', $response->gatewayResponseMessage);
        $this->assertEquals('PASS', $response->fraudRuleInfo);
    }

    public function testGetTransactionDetailWithRandomId()
    {
        $transactionId = GenerationUtils::getGuid();
        try {
            /** @var TransactionSummary $response */
            ReportingService::transactionDetail($transactionId)->execute();
        } catch (GatewayException $ex) {
            $this->assertEquals('508', $ex->responseCode);
            $this->assertEquals('Original transaction not found.', $ex->responseMessage);
        }
    }

    public function testGetTransactionDetailWithNullId()
    {
        try {
            /** @var TransactionSummary $response */
            ReportingService::transactionDetail(null)->execute();
        } catch (BuilderException $ex) {
            $this->assertEquals('transactionId cannot be null for this transaction type.', $ex->getMessage());
        }
    }

}