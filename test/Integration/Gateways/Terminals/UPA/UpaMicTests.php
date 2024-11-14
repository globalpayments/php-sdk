<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\Enums\TimeZoneIdentifier;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceResponse;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Entities\MessageLines;
use GlobalPayments\Api\Terminals\Entities\PrintData;
use GlobalPayments\Api\Terminals\Entities\PromptMessages;
use GlobalPayments\Api\Terminals\Entities\ScanData;
use GlobalPayments\Api\Terminals\Entities\UDData;
use GlobalPayments\Api\Terminals\Enums\BatchReportType;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DebugLevel;
use GlobalPayments\Api\Terminals\Enums\DebugLogsOutput;
use GlobalPayments\Api\Terminals\Enums\DeviceConfigType;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\DisplayOption;
use GlobalPayments\Api\Terminals\Enums\LogFileIndicator;
use GlobalPayments\Api\Terminals\Enums\UDFileTypes;
use GlobalPayments\Api\Terminals\UPA\Entities\CancelParameters;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaSearchCriteria;
use GlobalPayments\Api\Terminals\UPA\Entities\SignatureData;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchList;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchReportResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchTransaction;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchTransactionList;
use GlobalPayments\Api\Terminals\UPA\Responses\OpenTabDetailsResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\SignatureResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\TerminalSetupResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\TransactionResponse;
use GlobalPayments\Api\Terminals\UPA\Responses\UDScreenResponse;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

class UpaMicTests extends TestCase
{
    private IDeviceInterface $device;

    /**
     * @throws ApiException
     */
    public function setup(): void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown(): void
    {
        sleep(3);
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function getConfig(): ConnectionConfig
    {
        $config = new ConnectionConfig();
        $config->deviceType = DeviceType::UPA_DEVICE;
        $config->connectionMode = ConnectionModes::MEET_IN_THE_CLOUD;
        BaseGpApiTestConfig::$appId = BaseGpApiTestConfig::MITC_UPA_APP_ID; #gitleaks:allow
        BaseGpApiTestConfig::$appKey = BaseGpApiTestConfig::MITC_UPA_APP_KEY; #gitleaks:allow
        $gpApiConfig = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
        $gpApiConfig->country = 'CA';
        $gpApiConfig->deviceCurrency = 'CAD';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = "9187";
        $gpApiConfig->accessTokenInfo = $accessTokenInfo;
        $config->gatewayConfig = $gpApiConfig;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testCreditSale()
    {
        $response = $this->device->sale(10)
            ->withEcrId('13')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
    }

    public function testPing()
    {
        $response = $this->device->ping();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
    }

    public function testCreditSaleWithTerminalRefNumber()
    {
        try {
            $this->device->sale(10)
                ->withTerminalRefNumber(GenerationUtils::getGuid())
                ->withEcrId('13')
                ->execute();
        } catch (GatewayException $e) {
            $this->assertStringContainsString('[tranNo]-UNKNOWN FIELD', $e->getMessage());
        }
    }

    public function testLineItem()
    {
        $this->device->ecrId = '12';
        $items = [
            ["Line Item #1", 10],
            ["Line Item #2", 11],
            ["Line Item #3", 13],
        ];
        foreach ($items as $item) {
            $response = $this->device->lineItem($item[0], $item[1]);
            $this->assertNotNull($response);
            $this->assertEquals('00', $response->deviceResponseCode);
            $this->assertEquals('COMPLETE', $response->deviceResponseText);
        }

        sleep(5);

        $cancelParams = new CancelParameters();
        $cancelParams->displayOption = "1";
        $cancelResponse = $this->device->cancel($cancelParams);

        $this->assertNotNull($cancelResponse);
        $this->assertEquals('00', $cancelResponse->deviceResponseCode);
        $this->assertEquals('COMPLETE', $cancelResponse->deviceResponseText);
    }

    public function testCreditAuth()
    {
        try {
            $this->device->authorize(10)
                ->withEcrId("10")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertStringContainsString('TRANSACTION CANCELLED  COMMAND NOT SUPPORTED', $e->getMessage());
        }
    }

    public function testCreditRefund()
    {
        $response = $this->device->refund(10)
            ->withEcrId('13')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
    }

    public function testCreditVerify()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->verify()
            ->withEcrId('1')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
    }

    /**
     * @throws ApiException
     */
    public function testCreditVoid()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->sale(10)
            ->withEcrId('13')
            ->withClerkId('1234')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
        $this->assertNotNull($response->transactionId);

        sleep(15);

        $response = $this->device->void()
            ->withEcrId('13')
            ->withTransactionId($response->transactionId)
            ->withClerkId($response->clerkId)
            ->withClientTransactionId($response->referenceNumber)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
    }

    public function testCreditSale_WithoutAmount()
    {
        $exceptionCaught = false;
        try {
            $this->device->sale()
                ->withEcrId('13')
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditRefund_WithoutAmount()
    {
        $exceptionCaught = false;
        try {
            $this->device->refund()
                ->withEcrId('13')
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEndOfDay()
    {
        $this->device->ecrId = '13';
        /** @var TransactionResponse $response */
        $response = $this->device->endOfDay();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
        $this->assertNotEmpty($response->batchId);
        $this->assertNotEmpty($response->batchSeqNbr);
    }

    public function testCancel()
    {
        $cancelParams = new CancelParameters();
        $cancelParams->displayOption = "1";

        $cancelResponse = $this->device->cancel($cancelParams);

        $this->assertNotNull($cancelResponse);
        $this->assertEquals('00', $cancelResponse->deviceResponseCode);
        $this->assertEquals('COMPLETE', $cancelResponse->deviceResponseText);
    }

    public function testCreditTipAdjust()
    {
        $response = $this->device->sale(10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        sleep(3);

        try {
            $this->device->tipAdjust(1.05)
                ->withTerminalRefNumber($response->terminalRefNumber)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertStringContainsString('TRANSACTION CANCELLED TIP NOT SUPPORTED', $e->getMessage());
        }
    }

    public function testGetOpenTabDetails()
    {
        $this->device->ecrId = '1';

        /** @var OpenTabDetailsResponse $response */
        $response = $this->device->getOpenTabDetails()
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotEmpty($response->openTabs);
    }

    public function testReboot()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->reboot();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotNull($response->transactionId);
        sleep(60);
    }

    public function testGetAppInfo()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->getAppInfo();

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotNull($response->deviceSerialNum);
    }

    public function testGetParam()
    {
        $params = ["TerminalLanguage", "PinBypassIsSupported"];
        /** @var TransactionResponse $response */
        $response = $this->device->getParam($params);

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testSetTimeZone()
    {
        $response = $this->device->setTimeZone(TimeZoneIdentifier::AMERICA_LOS_ANGELES);

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testClearDataLake()
    {
        $response = $this->device->clearDataLake();

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testRestart()
    {
        $response = $this->device->reset();

        $this->assertNotNull($response);
        $this->assertInstanceOf(IDeviceResponse::class, $response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testReturnToIdle()
    {
        $this->device->ecrId = '12';
        /** @var TransactionResponse $response */
        $response = $this->device->returnToIdle();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotNull($response->transactionId);
    }

    public function testGetConfigContents()
    {
        /** @var TerminalSetupResponse $response */
        $response = $this->device->getDeviceConfig(DeviceConfigType::CONTACT_TERMINAL_CONFIG);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertEquals(DeviceConfigType::CONTACT_TERMINAL_CONFIG, $response->configType);
        $this->assertNotNull($response->fileContent);
        file_put_contents("configuration.txt", $response->fileContent);
    }

    public function testPrint()
    {
        $printData = new PrintData();
        $printData->line1 = 'Printing...';
        $printData->line2 = 'Please Wait...';
        $printData->displayOption = DisplayOption::NO_SCREEN_CHANGE;
        $printData->filePath = __DIR__ . "/samples/download.png";

        $this->device->ecrId = '12';
        /** @var TransactionResponse $response */
        $response = $this->device->print($printData);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
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
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotNull($response->scanData);
    }

    public function testGetDebugInfo()
    {
        $this->device->ecrId = '1';
        /** @var TransactionResponse $response */
        $response = $this->device->getDebugInfo("logs/DebugLogs", LogFileIndicator::DEBUG_FILE_1);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotNull($response->debugFileContents);
        $this->assertNotNull($response->debugFileLength);
    }

    public function testSetDebugLevel()
    {
        $this->device->ecrId = '1';
        $response = $this->device->setDebugLevel([DebugLevel::PACKETS, DebugLevel::DATA], DebugLogsOutput::FILE);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testGetDebugLevel()
    {
        $this->device->ecrId = '1';
        /** @var TransactionResponse $response */
        $response = $this->device->getDebugLevel();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertEquals('PACKETS|DATA', $response->debugLevel);
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
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotNull($response->signatureData);

        /*Save image to file*/
        $imageType = explode('/', getimagesizefromstring(base64_decode($response->signatureData))['mime'])[1];
        file_put_contents("signature.$imageType", base64_decode($response->signatureData));
    }

    public function testCommunicationCheck()
    {
        /** @var  TransactionResponse $response */
        $response = $this->device->communicationCheck();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertEquals('Success', $response->gatewayResponseMessage);
    }

    public function testLogon()
    {
        /** @var  TransactionResponse $response */
        $response = $this->device->logon();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertEquals(UpaMessageId::LOGON, $response->command);
    }

    public function testReturnDefaultScreen()
    {
        $this->markTestSkipped("APP005 - COMMAND NOT ALLOWED IN THE CURRENT APP MODE");
        $response = $this->device->returnDefaultScreen(DisplayOption::RETURN_TO_IDLE_SCREEN);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testGetEncryptionType()
    {
        $this->markTestSkipped("APP005 - COMMAND NOT ALLOWED IN THE CURRENT APP MODE");
        /** @var TransactionResponse $response */
        $response = $this->device->getEncryptionType();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotEmpty($response->dataEncryptionType);
    }

    public function testLoadUDDataFile()
    {
        $this->markTestSkipped("UD006 - CANNOT LOAD USER-DEFINED FILE");
        $screen = new UDData();
        $screen->fileType = UDFileTypes::HTML5;
        $screen->slotNum = '22';
        $screen->file = 'samples/UDDataFile.html';
        /** @var UDScreenResponse $response */
        $response = $this->device->loadUDData($screen);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }
    public function testRemoveUDDataFile()
    {
        $this->markTestSkipped("UD005 - CANNOT DELETE USER-DEFINED FILE");
        $screen = new UDData();
        $screen->fileType = UDFileTypes::HTML5;
        $screen->slotNum = '1';

        $this->device->ecrId = '1';
        /** @var UDScreenResponse $response */
        $response = $this->device->removeUDData($screen);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testExecuteUDDataFile()
    {
        $this->markTestSkipped("UD001 - INVALID SLOT NUMBER");
        $screen = new UDData();
        $screen->fileType = UDFileTypes::HTML5;
        $screen->slotNum = 1;
        $screen->displayOption = DisplayOption::RETURN_TO_IDLE_SCREEN;

        $this->device->ecrId = '1';
        /** @var UDScreenResponse $response */
        $response = $this->device->executeUDData($screen);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testInjectUDDataFile()
    {
        $this->markTestSkipped("INVALID_REQUEST_DATA - Request contains unexpected data");
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
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testFindAvailableBatches()
    {
        $this->device->ecrId = '12';
        /** @var BatchList $response */
        $response = $this->device->findBatches()->execute();

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
        $this->assertIsArray($response->batchIds);
        $this->assertGreaterThan(0, $this->count($response->batchIds));
    }

    public function testGetBatchDetails()
    {
        /** @var BatchReportResponse $response */
        $response = $this->device->getBatchDetails(printReport: true);

        $this->assertNotNull($response);
        $this->assertEquals('COMPLETE', $response->status);
        $this->assertNotNull($response->batchRecord);
        $this->assertNotNull($response->batchRecord->transactionDetails);
        $this->assertNotEmpty($response->batchRecord->batchId);
    }

    public function testGetBatchReport()
    {
        $this->device->ecrId = '13';
        /** @var BatchReportResponse $response */
        $response = $this->device->getBatchReport()
            ->where(UpaSearchCriteria::BATCH, "1035184")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('COMPLETE', $response->status);
    }

    public function testCreditSaleFullParams()
    {
        $autoSubAmounts = new AutoSubstantiation();
        $autoSubAmounts->setDentalSubTotal(5.00);
        $autoSubAmounts->setClinicSubTotal(5);
        $autoSubAmounts->setVisionSubTotal(5);
        $autoSubAmounts->setPrescriptionSubTotal(12.50);

        $response = $this->device->sale(5)
            ->withEcrId('13')
            ->withClerkId('1234')
            ->withCardOnFileIndicator(StoredCredentialInitiator::CARDHOLDER)
            ->withCardBrandTransId("transId")
            ->withInvoiceNumber('123A10')
            ->withShippingDate(new \DateTime())
            ->withTaxAmount(2.18)
            ->withGratuity(12.56)
            ->withTaxType(TaxType::TAX_EXEMPT)
            ->withCashBack(0.1)
            ->withConfirmationAmount(true)
            ->withProcessCPC(true)
//            ->withAutoSubstantiation($autoSubAmounts)
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->deviceResponseText);
    }

    public function testDisplayMessage()
    {
        $messageLines = new MessageLines();
        $messageLines->line1 = 'Please wait...';
        $messageLines->timeout = 0;

        $response = $this->device->displayMessage($messageLines);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('COMPLETE', $response->status);
    }
}