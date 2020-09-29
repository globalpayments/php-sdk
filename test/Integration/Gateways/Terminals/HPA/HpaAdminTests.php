<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\HPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\DeviceSettings;
use GlobalPayments\Api\Terminals\Enums\DownloadTime;
use GlobalPayments\Api\Terminals\Enums\DownloadEnvironment;
use GlobalPayments\Api\Terminals\Enums\DownloadType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\HPA\Entities\LineItem;
use GlobalPayments\Api\Terminals\HPA\Entities\Enums\HpaSendFileType;
use GlobalPayments\Api\Terminals\HPA\Entities\SendFileData;

class HpaAdminTests extends TestCase
{

    private $device;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
    }
    
    public function tearDown()
    {
        sleep(3);
        $this->device->reset();
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '10.138.141.5';
        $config->port = '12345';
        $config->deviceType = DeviceType::HPA_ISC250;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 300;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }
    
    public function testCancel()
    {
        $response = $this->device->cancel();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertEquals('Reset', $response->response);
    }
    
    public function testIntialize()
    {
        $this->device->closeLane();
        $response = $this->device->initialize();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->responseData['initializeResponse']);

        $deviceInformation = $response->responseData['initializeResponse'];
        $this->assertEquals('HeartSIP', $deviceInformation['application']);
    }
    
    public function testOpenLane()
    {
        $response = $this->device->openLane();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    public function testCloseLane()
    {
        $response = $this->device->closeLane();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testReset()
    {
        $response = $this->device->reset();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertEquals('Reset', $response->response);
    }

    public function testReboot()
    {
        $this->markTestSkipped('Reboot skipped');
        
        $response = $this->device->reboot();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertEquals('Reboot', $response->response);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Unexpected Gateway Response: 1502 - CANNOT PROCESS IN LANE OPEN STATE
     */
    public function testLaneOpenIntialize()
    {
        //open the lane
        $response = $this->device->openLane();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        $response = $this->device->initialize();
    }
    
    public function testEod()
    {
        $this->device->reset();
        $this->device->closeLane();
        
        $response = $this->device->eod();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $this->assertNotNull($response->reversal);
        $this->assertNotNull($response->emvOfflineDecline);
        $this->assertNotNull($response->transactionCertificate);
        $this->assertNotNull($response->attachment);
        $this->assertNotNull($response->sendSAF);
        $this->assertNotNull($response->batchClose);
        $this->assertNotNull($response->heartBeat);
        $this->assertNotNull($response->eMVPDL);
        
        $this->assertNotNull($response->responseData);
        $this->assertNotNull($response->responseData['getBatchReport']['batchSummary']);
        $this->assertNotNull($response->responseData['getBatchReport']['batchReport']);
        $this->assertNotNull($response->responseData['getBatchReport']['batchDetail']);
    }
    
    public function testStartDownload()
    {
        $this->markTestSkipped('StartDownload skipped');
        
        $deviceSettings = new DeviceSettings();
        $deviceSettings->terminalId = 'EB25033M';
        $deviceSettings->applicationId = 'PI8HD33M';
        $deviceSettings->downloadType = DownloadType::FULL;
        $deviceSettings->downloadTime = DownloadTime::NOW;
        $deviceSettings->hudsUrl = DownloadEnvironment::DEVELOPMENT;
        $deviceSettings->hudsPort = 8001;
        
        $response = $this->device->startDownload($deviceSettings);
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testPartialStartDownload()
    {
        $this->markTestSkipped('StartDownload skipped');
        
        $deviceSettings = new DeviceSettings();
        $deviceSettings->terminalId = 'EB25033M';
        $deviceSettings->applicationId = 'PI8HD33M';
        $deviceSettings->downloadType = DownloadType::PARTIAL;
        $deviceSettings->downloadTime = DownloadTime::NOW;
        $deviceSettings->hudsUrl = DownloadEnvironment::DEVELOPMENT;
        $deviceSettings->hudsPort = 8001;
        
        $response = $this->device->startDownload($deviceSettings);
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testEndOfDayStartDownload()
    {
        $this->markTestSkipped('StartDownload skipped');
        
        $deviceSettings = new DeviceSettings();
        $deviceSettings->terminalId = 'EB25033M';
        $deviceSettings->applicationId = 'PI8HD33M';
        $deviceSettings->downloadType = DownloadType::FULL;
        $deviceSettings->downloadTime = DownloadTime::EOD;
        $deviceSettings->hudsUrl = DownloadEnvironment::DEVELOPMENT;
        $deviceSettings->hudsPort = 8001;
        
        $response = $this->device->startDownload($deviceSettings);
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testCustomStartDownload()
    {
        $this->markTestSkipped('StartDownload skipped');
        
        $deviceSettings = new DeviceSettings();
        $deviceSettings->terminalId = 'EB25033M';
        $deviceSettings->applicationId = 'PI8HD33M';
        $deviceSettings->downloadType = DownloadType::FULL;
        $deviceSettings->downloadTime = date('YmdHis');
        $deviceSettings->hudsUrl = DownloadEnvironment::DEVELOPMENT;
        $deviceSettings->hudsPort = 8001;
        
        $response = $this->device->startDownload($deviceSettings);
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testLineItem()
    {
        $lineItemDetails = new LineItem();
        $lineItemDetails->leftText = 'Green Beans, canned';
        $lineItemDetails->rightText = '$0.59';
        $lineItemDetails->runningLeftText = 'TOTAL';
        $lineItemDetails->runningRightText = '$1.19';
        
        $this->device->openLane();
        $response = $this->device->lineItem($lineItemDetails);
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage Line item left text cannot be null
     */
    public function testLineItemLeftext()
    {
        $lineItemDetails = new LineItem();
        $response = $this->device->lineItem($lineItemDetails);
    }
    
    public function testEnableSafMode()
    {
        $response = $this->device->setSafMode(1);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testDisableSafMode()
    {
        $this->device->closeLane();
        $response = $this->device->setSafMode(3);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    public function testSendSaf()
    {
        $response = $this->device->sendSaf();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $this->assertNotNull($response->responseData);
        $this->assertNotNull($response->responseData['sendSAF']);
        $this->assertNotNull($response->responseData['sendSAF']['approvedSafSummary']);
        $this->assertNotNull($response->responseData['sendSAF']['pendingSafSummary']);
        $this->assertNotNull($response->responseData['sendSAF']['declinedSafSummary']);
        $this->assertNotNull($response->responseData['sendSAF']['offlineApprovedSafSummary']);
        $this->assertNotNull($response->responseData['sendSAF']['partiallyApprovedSafSummary']);
        $this->assertNotNull($response->responseData['sendSAF']['approvedSafVoidSummary']);
    }
    
    /*
     * Note: This sample banner will take 25 minutes to upload. 
     * Timeout should be handled accordingly
     */
    public function testSendFileBanner()
    {
        $sendFileInfo = new SendFileData();
        $sendFileInfo->imageLocation = dirname(__FILE__) . '/sampleimages/hpa_banner_iSC250_60_480.jpg';
        $sendFileInfo->imageType = HpaSendFileType::BANNER;
        
        $response = $this->device->sendFile($sendFileInfo);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    /*
     * Note: This sample logo will take 20 minutes to upload. 
     * Timeout should be handled accordingly
     */
    public function testSendFileIdleLogo()
    {
        $sendFileInfo = new SendFileData();
        $sendFileInfo->imageLocation = dirname(__FILE__) . '/sampleimages/hpa_logo_iSC250_272_480.jpg';
        $sendFileInfo->imageType = HpaSendFileType::IDLELOGO;
        
        $response = $this->device->sendFile($sendFileInfo);

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage Input error: Image location / type missing
     */
    public function testFileInputError()
    {
        $sendFileInfo = new SendFileData();
        $this->device->sendFile($sendFileInfo);
    }
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage Incorrect file height and width
     */
    public function testIncorrectFileSize()
    {
        $sendFileInfo = new SendFileData();
        $sendFileInfo->imageLocation = dirname(__FILE__) . '/sampleimages/image_500_500.jpg';
        $sendFileInfo->imageType = HpaSendFileType::BANNER;
        
        $this->device->sendFile($sendFileInfo);
    }
    
    public function testGetDiagnosticReport()
    {
        $response = $this->device->getDiagnosticReport(30);
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $this->assertNotNull($response->responseData);
        $this->assertNotNull($response->responseData['getDiagnosticReport']);
    }
    
    public function testPromptForSignature()
    {
        $this->device->openLane();
        $response = $this->device->promptForSignature();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->signatureData);
    }
    
    public function testGetLastResponse()
    {
        $this->device->openLane();
        $response = $this->device->getLastResponse();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertNotNull($response->lastResponse);
    }
}
