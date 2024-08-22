<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\Enums\TimeZoneIdentifier;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceResponse;
use GlobalPayments\Api\Terminals\Abstractions\ISAFResponse;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Entities\PrintData;
use GlobalPayments\Api\Terminals\Entities\PromptMessages;
use GlobalPayments\Api\Terminals\Entities\ScanData;
use GlobalPayments\Api\Terminals\Entities\UDData;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DebugLevel;
use GlobalPayments\Api\Terminals\Enums\DebugLogsOutput;
use GlobalPayments\Api\Terminals\Enums\DeviceConfigType;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\DisplayOption;
use GlobalPayments\Api\Terminals\Enums\LogFileIndicator;
use GlobalPayments\Api\Terminals\Enums\UDFileTypes;
use GlobalPayments\Api\Terminals\UPA\Entities\CancelParameters;
use GlobalPayments\Api\Terminals\UPA\Entities\POSData;
use GlobalPayments\Api\Terminals\UPA\Entities\SignatureData;
use GlobalPayments\Api\Terminals\UPA\Responses\SignatureResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\TerminalSetupResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\TransactionResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\UDScreenResponse;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

class UpaAdminTests extends TestCase
{
    private IDeviceInterface $device;

    public function setup(): void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    protected function getConfig(): ConnectionConfig
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.8.181';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_VERIFONE_T650P;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testCancel()
    {
        $cancelParams = new CancelParameters();
        $cancelParams->displayOption = "1";
        $response = $this->device->cancel($cancelParams);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }

    public function testReboot()
    {
        $response = $this->device->reboot();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        sleep(60);
    }

    public function testLineItem()
    {
        $response = $this->device->lineItem("Line Item #1", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $response = $this->device->lineItem("Line Item #2", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $response = $this->device->lineItem("Line Item #3", "10.00");
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        $cancelParams = new CancelParameters();
        $cancelParams->displayOption = "1";
        $this->device->cancel($cancelParams);
    }

    public function testPing()
    {
        $response = $this->device->ping();
        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testRestart()
    {
        $response = $this->device->reset();

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testGetAppInfo()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->getAppInfo();

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotNull($response->deviceSerialNum);
    }

    public function testClearDataLake()
    {
        $response = $this->device->clearDataLake();

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testSetTimeZone()
    {
        $response = $this->device->setTimeZone(TimeZoneIdentifier::AMERICA_LOS_ANGELES);

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testGetParam()
    {
        $params = ["TerminalLanguage", "PinBypassIsSupported"];
        /** @var TransactionResponse $response */
        $response = $this->device->getParam($params);

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testGetSignature()
    {
        $data = new SignatureData();
        $data->prompts = new PromptMessages();
        $data->prompts->prompt1 = 'Please sign';
        $data->displayOption = DisplayOption::RETURN_TO_IDLE_SCREEN;
        /** @var SignatureResponse $response */
        $response = $this->device->getSignatureFile($data);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotNull($response->signatureData);

        /*Save image to file*/
        $imageType = explode('/', getimagesizefromstring(base64_decode($response->signatureData))['mime'])[1];
        file_put_contents("signature.$imageType", base64_decode($response->signatureData));
    }

    public function testRegisterPOS()
    {
        $data = new POSData();
        $data->appName = 'com.global.testapp';
        $data->launchOrder = 1;
        $data->remove = false;
        $data->silent = 0;

        $this->device->ecrId = '1';
        $response = $this->device->registerPOS($data);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testBroadcastConfiguration()
    {
        $response = $this->device->broadcastConfiguration(false);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testSetDebugLevel()
    {
        $this->device->ecrId = '1';
        $response = $this->device->setDebugLevel([DebugLevel::PACKETS, DebugLevel::DATA], DebugLogsOutput::FILE);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testGetDebugLevel()
    {
        $this->device->ecrId = '1';
        $response = $this->device->getDebugLevel();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertEquals('PACKETS|DATA', $response->debugLevel);
    }

    public function testGetDebugInfo()
    {
        $this->device->ecrId = '1';
        /** @var TransactionResponse $response */
        $response = $this->device->getDebugInfo("logs/DebugLogs", LogFileIndicator::DEBUG_FILE_1,);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotNull($response->debugFileContents);
        $this->assertNotNull($response->debugFileLength);
    }

    public function testReturnToIdle()
    {
        $this->device->ecrId = '12';
        /** @var TransactionResponse $response */
        $response = $this->device->returnToIdle();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testLoadUDDataFile()
    {
        $screen = new UDData();
        $screen->fileType = UDFileTypes::HTML5;
        $screen->slotNum = '22';
        $screen->file = 'samples/UDDataFile.html';
        /** @var UDScreenResponse $response */
        $response = $this->device->loadUDData($screen);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testRemoveUDDataFile()
    {
        $screen = new UDData();
        $screen->fileType = UDFileTypes::HTML5;
        $screen->slotNum = '1';

        $this->device->ecrId = '1';
        /** @var UDScreenResponse $response */
        $response = $this->device->removeUDData($screen);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testExecuteUDDataFile()
    {
        $screen = new UDData();
        $screen->fileType = UDFileTypes::HTML5;
        $screen->slotNum = '1';
        $screen->displayOption = DisplayOption::RETURN_TO_IDLE_SCREEN;

        $this->device->ecrId = '1';
        /** @var UDScreenResponse $response */
        $response = $this->device->executeUDData($screen);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testInjectUDDataFile()
    {
        $screen = new UDData();
        $screen->fileType = UDFileTypes::HTML5;
        $screen->fileName = "index.html";
        // this is the file with the content you want to inject
        $screen->localFile = __DIR__ . '/samples/UDDataFile.html';

        $this->device->ecrId = '1';
        /** @var UDScreenResponse $response */
        $response = $this->device->injectUDData($screen);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testScanQR()
    {
        $scanData = new ScanData();
        $scanData->header = 'SCAN';
        $scanData->prompts = new PromptMessages();
        $scanData->prompts->prompt1 = 'SCAN QR CODE';
        $scanData->prompts->prompt2 = 'ALIGN THE QR CODE WITHIN THE FRAME TO SCAN';

        $this->device->ecrId = '12';
        /** @var TransactionResponse $response */
        $response = $this->device->scan($scanData);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotNull($response->scanData);
    }

    public function testPrint()
    {
        $printData = new PrintData();
        $printData->line1 = 'Printing...';
        $printData->line2 = 'Please Wait...';
        $printData->filePath = __DIR__ . "/samples/download.png";

        $this->device->ecrId = '12';
        /** @var TransactionResponse $response */
        $response = $this->device->print($printData);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testGetConfigContents()
    {
        /** @var TerminalSetupResponse $response */
        $response = $this->device->getDeviceConfig(DeviceConfigType::CONTACT_TERMINAL_CONFIG);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertEquals(DeviceConfigType::CONTACT_TERMINAL_CONFIG, $response->configType);
        $this->assertNotNull($response->fileContent);
        file_put_contents("configuration.txt", $response->fileContent);
    }

    public function testCommunicationCheck()
    {
        /** @var  TransactionResponse $response */
        $response = $this->device->communicationCheck();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertEquals('Success', $response->gatewayResponseMessage);
    }

    public function testSendSAF()
    {
        /** @var ISAFResponse $response */
        $response = $this->device->sendStoreAndForward();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }
}
