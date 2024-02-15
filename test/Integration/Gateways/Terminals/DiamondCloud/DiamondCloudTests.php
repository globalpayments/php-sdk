<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\DiamondCloud;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\Region;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Services\DeviceCloudService;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Diamond\Entities\Enums\DiamondCloudSearchCriteria;
use GlobalPayments\Api\Terminals\Diamond\Responses\DiamondCloudResponse;
use GlobalPayments\Api\Terminals\DiamondCloudConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use PHPUnit\Framework\TestCase;

class DiamondCloudTests extends TestCase
{
    private IDeviceInterface $device;
    private string $posID = '1342641186174645';
    private DiamondCloudConfig $config;

    public function setup(): void
    {
        $this->config = $this->getConfig();
        $this->device = DeviceService::create($this->config);
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    protected function getConfig(): ConnectionConfig
    {
        $config = new DiamondCloudConfig();
        $config->deviceType = DeviceType::PAX_A920;
        $config->connectionMode = ConnectionModes::DIAMOND_CLOUD;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();
        $config->timeout = 15;
        $config->isvID = '154F070E3E474AB98B00D73ED81AAA93';
        $config->secretKey = '8003672638';
        $config->region = Region::EU;
        $config->posID = $this->posID;

        return $config;
    }

    public function testCreditSale()
    {
        /** @var TerminalResponse $response */
        $response = $this->device->sale(2)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testGetTransactionStatus()
    {
        /** @var DiamondCloudResponse $response */
        $response = $this->device->localDetailReport()
            ->where(DiamondCloudSearchCriteria::REFERENCE_NUMBER, 'Z6WZA38VKW4')
            ->execute();

        $this->assertEquals('sale', $response->command);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('ACCEPTED', $response->responseCode);
    }

    public function testCreditVoid()
    {
        /** @var TerminalResponse $response */
        $response = $this->device->void()
            ->withTransactionId('EXKX7WKV4QX')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testCreditReturn()
    {
        /** @var TerminalResponse $response */
        $response = $this->device->refund(1)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testLinkedRefund()
    {
        $response = $this->device->authorize(2.01)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);

        $trnId = $response->transactionId;

        sleep(15);

        /** @var TerminalResponse $response */
        $response = $this->device->refundById()
            ->withTransactionId($trnId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testTipAdjust()
    {
        $errorFound = false;
        try {
            $this->device->tipAdjust(2)
                ->withAmount(5)
                ->withTransactionId('Z6WZA38VKW4')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Tip adjust is not available on PAX devices', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testAuthorize()
    {
        /** @var TerminalResponse $response */
        $response = $this->device->authorize(2)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testCapture()
    {
        $transactionId = 'DJKK7BY4MWV';
        $captureResponse = $this->device->capture(2)
            ->withTransactionId($transactionId)
            ->execute();

        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->deviceResponseCode);
        $this->assertNotNull($captureResponse->transactionId);
    }

    public function testCancelAuth()
    {
        $transactionId = 'DJKK7BY4MWV';
        $cancelAuthResponse = $this->device->deletePreAuth()
            ->withTransactionId($transactionId)
            ->execute();

        $this->assertNotNull($cancelAuthResponse);
        $this->assertEquals('00', $cancelAuthResponse->deviceResponseCode);
        $this->assertNotNull($cancelAuthResponse->transactionId);
    }

    public function testAuthIncreasing()
    {
        $transactionId = 'DJKK7BY4MWV';
        $authIncreasingResponse = $this->device->increasePreAuth(3)
            ->withTransactionId($transactionId)
            ->execute();

        $this->assertNotNull($authIncreasingResponse);
        $this->assertEquals('00', $authIncreasingResponse->deviceResponseCode);
        $this->assertNotNull($authIncreasingResponse->transactionId);
    }

    public function testEbtPurchase()
    {
        $this->markTestSkipped('This feature is not supported in EU region!');
        $response = $this->device->sale(1)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testEbtBalance()
    {
        $this->markTestSkipped('This feature is not supported in EU region!');
        $response = $this->device->balance()
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testEbtReturn()
    {
        $this->markTestSkipped('This feature is not supported in EU region!');
        $response = $this->device->refund(5.02)
            ->withPaymentMethodType(PaymentMethodType::EBT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testGiftBalance()
    {
        $this->markTestSkipped('This feature is not supported in EU region!');
        $response = $this->device->balance()
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testGiftReload()
    {
        $this->markTestSkipped('This feature is not supported in EU region!');
        $response = $this->device->addValue('1.00')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testGiftRedeem()
    {
        $this->markTestSkipped('This feature is not supported in EU region!');

        $response = $this->device->sale('1.00')
            ->withPaymentMethodType(PaymentMethodType::GIFT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testBatchClose()
    {
        $response = $this->device->batchClose();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }

    public function testSaleStatusUrlResponse()
    {
        $service = new DeviceCloudService($this->config);
        $responseJson = '{"IsvId":"154F070E3E474AB98B00D73ED81AAA93","InvoiceId":"","CloudTxnId":"XJ98K73YJ9N","traceId":"","followId":"","Device":"1850747855_2","PosId":"1342641186174645","PaymentResponse":{"PaymentResponse":{"aosa":null,"applicationVersion":"1.6.2","authorizationCode":null,"authorizationMessage":"000023","authorizationMethod":"?","authorizationType":"?","cardBrandName":"MC CREDIT","cardSource":"P","cashbackAmount":"500","currencyExchangeRate":null,"date":"2023.09.11","dccCurrencyExponent":null,"dccText1":null,"dccText2":null,"errorMessage":null,"maskedCardNumber":"************0036","merchantId":"888880000000373","result":"1","serverMessage":null,"slipNumber":"23","terminalCurrency":null,"terminalId":"66677768","terminalPrintingIndicator":"1","time":"15:37:55","tipAmount":null,"token":null,"transactionAmount":"1000","transactionAmountInTerminalCurrency":null,"transactionCurrency":"EUR","transactionTitle":null,"type":"1","AC":null,"AID":"A0000000041010","ATC":null,"TSI":"8000","TVR":"0400000000"},"CloudInfo":{"Device":"1850747855_2","TerminalType":"eService","MqttClientId":"1bLU","Command":"sale","ApkVersion":"1.0.86.0629","TerminalModel":"PAX_A920Pro"},"ResultId":"Zvx3hYCS9tXxfAVy"}}';
        $json = json_decode($responseJson);
        /** @var DiamondCloudResponse $parsedResponse */
        $parsedResponse = $service->parseResponse($responseJson);

        $this->assertEquals($json->CloudTxnId, $parsedResponse->transactionId);
        $this->assertEquals($json->PaymentResponse->PaymentResponse->authorizationCode, $parsedResponse->authorizationCode);
        $this->assertEquals($this->posID, $parsedResponse->terminalRefNumber);
        $this->assertEquals($json->PaymentResponse->CloudInfo->Command, $parsedResponse->command);
    }

    public function testSaleACKStatusUrlResponse()
    {
        $service = new DeviceCloudService($this->config);
        $responseJson = '{"IsvId":"154F070E3E474AB98B00D73ED81AAA93","InvoiceId":"","CloudTxnId":"EXKX7WKV4QX","traceId":"","followId":"","Device":"1850747855_2","PosId":"1342641186174645","PaymentResponse":{"PaymentResponse":{"resultCode":"T03","hostMessage":"ACKNOWLEDGEEXKX7WKV4QX","transactionId":"EXKX7WKV4QX"},"CloudInfo":{"Device":"1850747855_2","TerminalType":"eService","MqttClientId":"1bLU","Command":"sale","ApkVersion":"1.0.86.0629","TerminalModel":"PAX_A920Pro"},"ResultId":"mNbmvleC3I63omNK"}}';
        $json = json_decode($responseJson);
        /** @var DiamondCloudResponse $parsedResponse */
        $parsedResponse = $service->parseResponse($responseJson);

        $this->assertEquals($json->CloudTxnId, $parsedResponse->transactionId);
        $this->assertEquals($json->PaymentResponse->CloudInfo->Command, $parsedResponse->command);
        $this->assertEquals($this->posID, $parsedResponse->terminalRefNumber);
        $this->assertEquals($json->PaymentResponse->PaymentResponse->resultCode, $parsedResponse->responseCode);
        $this->assertEquals($json->PaymentResponse->PaymentResponse->hostMessage, $parsedResponse->responseText);
    }

    public function testCreditSale_WithoutAmount()
    {
        $errorFound = false;
        try {
            $this->device->sale()
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testGetTransactionStatus_IdNotFound()
    {
        /** @var DiamondCloudResponse $response */
        $response = $this->device->localDetailReport()
            ->where(DiamondCloudSearchCriteria::REFERENCE_NUMBER, 'A49KDND5W3Z')
            ->execute();

        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('ERROR:The result does not exist', $response->status);
    }

    public function testGetTransactionStatus_NoId()
    {
        $errorFound = false;
        try {
            $this->device->localDetailReport()
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('referenceNumber cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testCreditVoid_WithoutTransactionId()
    {
        $errorFound = false;
        try {
            $this->device->void()
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('transactionId cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testCreditReturn_WithoutAmount()
    {
        $errorFound = false;
        try {
            $this->device->refund()
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testAuthorize_WithoutAmount()
    {
        $errorFound = false;
        try {
            $this->device->authorize()
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testCapture_WithoutAmount()
    {
        $errorFound = false;
        try {
            $this->device->capture()
                ->withTransactionId('BWMNKQK6EB5')
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('amount cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testCapture_WithoutTransactionId()
    {
        $errorFound = false;
        try {
            $this->device->capture('0.2')
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('transactionId cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testAuthIncreasing_WithoutTransactionId()
    {
        $errorFound = false;
        try {
            $this->device->increasePreAuth(3)
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('transactionId cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

}