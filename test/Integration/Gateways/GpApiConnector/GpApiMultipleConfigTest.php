<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class GpApiMultipleConfigTest extends TestCase
{

    public function testMultipleConfig()
    {
        $firstConfig = 'firstConfig';
        $secondConfig = 'secondConfig';
        $config = new GpApiConfig();
        $config->appId = 'oDVjAddrXt3qPJVPqQvrmgqM2MjMoHQS';
        $config->appKey = 'DHUGdzpjXfTbjZeo';

        ServicesContainer::configureService($config, $firstConfig);

        $firstResponse = ReportingService::findTransactionsPaged(1, 1)
            ->execute($firstConfig);

        $this->assertNotNull($firstResponse);
        $this->assertTrue(is_array($firstResponse->result));
        $this->assertCount(1, $firstResponse->result);

        $config2 = new GpApiConfig();
        $config2->appId = 'AzcKJwI7SzGGtd9IXCEir5VFPZ6kU8kH';
        $config2->appKey = 'xv1bZxbRxFQtzhAo';

        ServicesContainer::configureService($config2, $secondConfig);

        $secondResponse = ReportingService::findTransactionsPaged(1, 1)
            ->execute($secondConfig);

        $this->assertNotNull($secondResponse);
        $this->assertTrue(is_array($secondResponse->result));
        $this->assertCount(1, $secondResponse->result);
        $this->assertNotEquals($config->accessTokenInfo->accessToken, $config2->accessTokenInfo->accessToken);

        $thirdResponse = ReportingService::findTransactionsPaged(1, 1)
            ->execute($firstConfig);

        $this->assertNotNull($thirdResponse);
        $this->assertTrue(is_array($thirdResponse->result));
        $this->assertCount(1, $thirdResponse->result);
        $this->assertNotSame($thirdResponse, $secondResponse);
        $this->assertEquals($firstResponse->result[0]->transactionId, $thirdResponse->result[0]->transactionId);
    }
}