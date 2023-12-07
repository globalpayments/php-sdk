<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\FileProcessor;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\FileProcessingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\GpApiConnector\FileProcessing\FileProcessingClient;
use PHPUnit\Framework\TestCase;

class FileProcessingTest  extends TestCase
{
    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig(): GpApiConfig
    {
        BaseGpApiTestConfig::$appId = 'fWkEqBHQNyLrWCAtp1vCWDbo10kf5jr6';
        BaseGpApiTestConfig::$appKey = 'EkOH93AQKuGlj8Ty';

        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
        $config->statusUrl = 'https://eo9faqlbl8wkwmx.m.pipedream.net/';

        return $config;
    }

    public function testCreateUploadUrl()
    {
        /** @var FileProcessor $response */
        $response = FileProcessingService::initiate();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('INITIATED', $response->responseMessage);
        $this->assertNotNull($response->uploadUrl);

        $client = new FileProcessingClient($response->uploadUrl);
        $result = $client->uploadFile(__DIR__ . '/FileProcessing/FilesToUpload/20231127Tokenization100records.csv.encrypted.txt');
        $this->assertTrue($result);

        sleep(60);

        $fp = FileProcessingService::getDetails($response->resourceId);

        $this->assertEquals('SUCCESS', $fp->responseCode);
        $this->assertEquals($response->resourceId, $fp->resourceId);
    }

    public function testGetFileUploadDetails()
    {
        $resourceId = 'FPR_7e6c35eb65814996af5d7ba2357906d5';
        $response = FileProcessingService::getDetails($resourceId);

        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals($response->resourceId, $resourceId);
    }
}