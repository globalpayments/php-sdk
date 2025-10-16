<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType,
    StoredCredentialInitiator
};
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\PaymentMethods\DebitCardData;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Enums\{
    ConnectionModes,
    DeviceType
};
use GlobalPayments\Api\Terminals\{
    ConnectionConfig,
    TerminalResponse
};
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

class UpaDebitTests extends TestCase
{
    private IDeviceInterface $device;
    private float $amount;

   public function setup() : void
    {
        $this->device = DeviceService::create($this->getConfig());
        $this->amount = $this->generateRandomAmount(1, 10, 2);
    }

    public function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.110.111';
        $config->port = '8081';
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->deviceType = DeviceType::UPA_VERIFONE_T650P;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->logManagementProvider = new TerminalLogManagement();
        return $config;
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    private function generateRandomAmount($min, $max, $decimals): float
    {
        return round(mt_rand($min * 100, $max * 100) / 100, $decimals);
    }

    private function runBasicTests($response)
    {
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->deviceResponseText ?? 'Success');
        $this->assertTrue(strtolower($response->status ?? 'success') === 'success');
    }

    public function testDebitSaleSwipe()
    {
        $response = $this->device->sale($this->amount)
            ->withGratuity(0)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->runBasicTests($response);
    }

    public function testDebitSaleSwipe_WithTip()
    {
        $tipAmount = $this->generateRandomAmount(1, 2, 2);

        $response = $this->device->sale($this->amount)
            ->withGratuity($tipAmount)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
        $this->runBasicTests($response);
        $this->assertEquals($tipAmount, $response->tipAmount);
    }

    public function testDebitSaleChip()
    {
        $response = $this->device->sale(1)
            ->withRequestId(1202)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->runBasicTests($response);
        $this->assertEquals(1, $response->transactionAmount);
    }

        public function testDebitSaleAmountFormat()
    {
        $response = $this->device->sale(1)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->runBasicTests($response);
        $this->assertEquals(1, round($response->transactionAmount, 2));
    }

    public function testDebitSaleWithInvoiceNbr()
    {
        $response = $this->device->sale(1)
            ->withInvoiceNumber('123')
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->runBasicTests($response);
        $this->assertEquals(1, $response->transactionAmount);
    }

    public function testDebitSaleContactless()
    {
        $response = $this->device->sale(1)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->runBasicTests($response);
        $this->assertEquals(1, $response->transactionAmount);
    }

    public function testDebitSaleSwipe_withoutHSAFSA()
    {
        $substantiation = new AutoSubstantiation();
        $substantiation->setPrescriptionSubTotal(10);
        $substantiation->setVisionSubTotal(10);

        $response = $this->device->sale(1)
            ->withGratuity(0)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->withAutoSubstantiation($substantiation)
            ->execute();

        $this->runBasicTests($response);
        $this->assertEquals(1, $response->transactionAmount);
    }

    public function testVoidDebitWithReferenceNoOfSale()
    {
        $response1 = $this->device->sale(1)
            ->withGratuity(0)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
        $this->runBasicTests($response1);

        $response2 = $this->device->void()
            ->withTransactionId($response1->transactionId)
            ->execute();
        $this->runBasicTests($response2);
    }

    public function testDebitRefund_Linked()
    {
        $response = $this->device->sale(1)
            ->withGratuity(0)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->assertNotNull($response);

        sleep(15);

        $refundResponse = $this->device->refund(1)
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($refundResponse);
    }
    
    public function testDebit_refundByRefNo_withTip()
    {
        $response = $this->device->sale(1)
            ->withGratuity(1)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
        $this->runBasicTests($response);

        $refundResponse = $this->device->refund($response->baseAmount)
            ->withTransactionId($response->transactionId)
            ->execute();
        $this->runBasicTests($refundResponse);
    }

    public function testDebitReverse()
    {
        $response1 = $this->device->sale(1)
            ->withGratuity(0)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
        $this->runBasicTests($response1);

        $response2 = $this->device->reverse()
            ->withTerminalRefNumber($response1->terminalRefNumber)
            ->execute();
        $this->runBasicTests($response2);
    }

        public function testTipAdjust_withTerminalRefNumber()
    {
        $response1 = $this->device->sale(1)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->runBasicTests($response1);

        $response2 = $this->device->tipAdjust(5)
            ->withTerminalRefNumber($response1->terminalRefNumber)
            ->execute();

        $this->runBasicTests($response2);
        $this->assertEquals(1, $response2->tipAmount);
        $this->assertEquals(7, $response2->transactionAmount);
    }
   
    public function testTipAdjust_withReferenceNo()
    {
        $saleResponse = $this->device->sale(1)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->runBasicTests($saleResponse);

        $tipAdjustResponse = $this->device->tipAdjust(0.50)
            ->withTransactionId($saleResponse->transactionId)
            ->execute();

        $this->runBasicTests($tipAdjustResponse);
        $this->assertEquals(0.50, $tipAdjustResponse->tipAmount);
        $this->assertEquals(2.50, $tipAdjustResponse->transactionAmount);
    }
     
    public function testIncrementalAuths()
    {
        $response1 = $this->device->authorize(10.00)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $response2 = $this->device->authorize(5.00)
            ->withTransactionId($response1->transactionId)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->assertNotNull($response2);
        $this->assertEquals('00', $response2->deviceResponseCode);
    }
    
    public function testPreAuths_capture()
    {
        // LodgingData implementation assumed
        $lodgingData = new \GlobalPayments\Api\Entities\LodgingData();
        $lodgingData->checkedInDate = date('mdY');
        $lodgingData->dailyRateAmount = 12.50;
        $lodgingData->folioNumber = 10;
        $lodgingData->durationDays = 30;

        $preAuthResponse = $this->device->authorize(1.00)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->withDirectMarketInvoiceNumber("12345")
            ->withDirectMarketShipDay(12)
            ->withDirectMarketShipMonth(10)
            ->withTokenRequest(1)
            ->withTokenValue('test')
            ->execute();

        $this->assertNotNull($preAuthResponse);
        $this->assertEquals('00', $preAuthResponse->deviceResponseCode);
        $this->assertTrue(strtolower($preAuthResponse->status ?? 'success') === 'success');

        sleep(10);

        $captureResponse = $this->device->capture(1.00)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->withTransactionId($preAuthResponse->transactionId)
            ->withPreAuthAmount($preAuthResponse->transactionAmount)
            ->execute();

        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->deviceResponseCode);
    }
    
    public function testDeletePreAuth()
    {
        $response1 = $this->device->authorize(10.00)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();

        $this->assertNotNull($response1);
        $this->assertTrue(strtolower($response1->status ?? 'success') === 'success');

        $response2 = $this->device->deletePreAuth()
            ->withTransactionId($response1->transactionId)
            ->execute();
        $this->assertNotNull($response2);
        $this->assertTrue(strtolower($response2->status ?? 'success') === 'success');
    }

    public function testCardVerify()
    {
        $response = $this->device->verify()
            ->withCardBrandStorage(StoredCredentialInitiator::MERCHANT)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->withRequestMultiUseToken(1)
            ->withClerkId(1234)
            ->execute();

        $this->runBasicTests($response);
    }

    public function testPreAuthIncrementCompletion()
    {
        $preAuthResponse = $this->device->authorize(10.00)
            ->withClerkId(123)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
        $this->assertNotNull($preAuthResponse);
        $this->assertEquals('00', $preAuthResponse->deviceResponseCode);

        $lodgingData = new \GlobalPayments\Api\Entities\LodgingData();
        $lodgingData->checkedInDate = date('mdY');
        $lodgingData->dailyRateAmount = 12.50;
        $lodgingData->folioNumber = 10;
        $lodgingData->durationDays = 30;

        $incrementalAuthResponse = $this->device->authorize(5.00)
            ->withLodgingData($lodgingData)
            ->withPreAuthAmount($preAuthResponse->transactionAmount)
            ->withTransactionId($preAuthResponse->transactionId)
            ->withClerkId(123)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
        $this->assertNotNull($incrementalAuthResponse);
        $this->assertEquals('00', $incrementalAuthResponse->deviceResponseCode);

        $completionResponse = $this->device->capture(15.00)
            ->withTransactionId($preAuthResponse->transactionId)
            ->withPreAuthAmount($preAuthResponse->transactionAmount)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
        $this->assertNotNull($completionResponse);
        $this->assertEquals('00', $completionResponse->deviceResponseCode);
    }
}