<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\Genius;

use GlobalPayments\Api\Entities\{Address, AutoSubstantiation};
use GlobalPayments\Api\Entities\Enums\{Environment, PaymentMethodType, TransactionType};
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\{DeviceType, ConnectionModes};
use GlobalPayments\Api\Terminals\Genius\ServiceConfigs\MitcConfig;
use PHPUnit\Framework\{ExpectationFailedException, TestCase};
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class GeniusTests extends TestCase
{
    /**
     * 
     * @var GeniusInterface
     */
    public $device;

    public function setup() : void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown() : void
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->deviceType = DeviceType::GENIUS_VERIFONE_P400;
        $config->connectionMode = ConnectionModes::MEET_IN_THE_CLOUD;
        $config->meetInTheCloudConfig = new MitcConfig(
            "800000052971",
            "80040245",
            "kBySHIAkhL4UBFkVokFEUmDKWY1WGWUv",
            "u1cYb2xoGONWkGfSxp8js1BGgMOkO0tyMUP732qbAWM",
            "uITbt4dHj0f6Q2EVDwuWWA9cGiDAQnyD",
            "cedevice::at63jh"
        );
        $config->meetInTheCloudConfig->environment = Environment::TEST;

        return $config;
    }

    public function testSale() : void
    {
        $autoSubAmounts = new AutoSubstantiation();
        $autoSubAmounts->realTimeSubstantiation = true;
        $autoSubAmounts->setDentalSubTotal(5.00);
        $autoSubAmounts->setClinicSubTotal(5);
        $autoSubAmounts->setVisionSubTotal(5);
        $autoSubAmounts->setCopaySubTotal(5);

        $address = new Address();
        $address->postalCode;

        $response = $this->device->sale(28526)
            ->withClientTransactionId("mapsToReference_id" . $this->randNum(6))
            ->withInvoiceNumber($this->randNum(8))
            ->withAutoSubstantiation($autoSubAmounts)
            ->withAddress($address)
            ->withAllowPartialAuth(true)
            ->execute();
            
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * Refund a card directly; doesn't target a previous transaction
     * 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws ExpectationFailedException 
     */
    public function testIndependentRefund() : void
    {
        $response = $this->device->refund('100.28')
            ->withClientTransactionId("mapsToReference_id" . $this->randNum(6))
            ->withInvoiceNumber($this->randNum(8))
            ->execute();
            
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testRefundPrevSale() : void
    {
        $clientTransId = "mapsToReference_id" . $this->randNum(6);

        $saleResponse = $this->device->sale(100.00)
            ->withClientTransactionId($clientTransId)
            ->withInvoiceNumber($this->randNum(8))
            ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->responseCode);

        sleep(2); // refund will sometimes fail if attempted too quickly

        $refundResponse = $this->device->refundById('50.00')
            ->withClientTransactionId($clientTransId)
            ->withAllowDuplicates(true)
            ->execute();
            
        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->responseCode);
    }

    /**
     * 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws ExpectationFailedException 
     */
    public function testVoidPrevSale() : void
    {
        $clientTransId = "mapsToReference_id" . $this->randNum(6);

        $saleResponse = $this->device->sale(100.00)
            ->withClientTransactionId($clientTransId)
            ->withInvoiceNumber($this->randNum(8))
            ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->responseCode);

        sleep(3);

        $voidResponse = $this->device->void()
            ->withClientTransactionId($clientTransId)
            ->execute();
            
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    /**
     * You have to use an EMV debit card in a proper debit transaction for this to succeed.
     * If you you do not, the debitVoid attempt will fail.
     * 
     * MITC Gateway requires intgrations to indicate which payment method type was
     * used in the original transaction.
     * 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws ExpectationFailedException 
     */
    public function testVoidPrevDebitSale() : void
    {
        $clientTransId = "mapsToReference_id" . $this->randNum(6);

        $saleResponse = $this->device->sale(10.00)
            ->withClientTransactionId($clientTransId)
            ->withInvoiceNumber($this->randNum(8))
            ->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->responseCode);

        $voidResponse = $this->device->void()
            ->withClientTransactionId($clientTransId)
            ->withPaymentMethodType(PaymentMethodType::DEBIT)
            ->execute();
            
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    public function testReportSale() : void
    {
        $device = $this->device;

        $saleResponse = $device->sale(28526)
            ->withClientTransactionId("mapsToReference_id" . $this->randNum(6))
            ->withInvoiceNumber($this->randNum(8))
            ->withAllowDuplicates(true)
            ->execute();

        $transactionReport = $device->getTransactionDetail(
            TransactionType::SALE,
            $saleResponse->clientTransactionId
        )->execute();
            
        $this->assertNotNull($transactionReport);
        $this->assertEquals($saleResponse->responseCode, $transactionReport->responseCode);
    }

    /**
     * Void a refund
     * 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws ExpectationFailedException 
     */
    public function testVoidPrevRefund() : void
    {
        $clientTransId = "mapsToReference_id" . $this->randNum(6);

        $response = $this->device->refund('100.99')
            ->withClientTransactionId($clientTransId)
            ->withInvoiceNumber($this->randNum(8))
            ->execute();

        $voidResponse = $this->device->voidRefund()
            ->withClientTransactionId($clientTransId)
            ->execute();
            
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    public function testRefundReport() : void
    {
        $device = $this->device;
        $clientTransId = "mapsToReference_id" . $this->randNum(6);

        $response = $this->device->refund('100.99')
            ->withClientTransactionId($clientTransId)
            ->withInvoiceNumber($this->randNum(8))
            ->execute();

        $transactionReport = $device->getTransactionDetail(
            TransactionType::SALE,
            $response->clientTransactionId
        )->execute();
            
        $this->assertNotNull($transactionReport);
    }

    /**
     * 
     * @param int $digits 
     * @return string 
     */
    private function randNum(int $digits) : string
    {
        $beginning = (int) str_pad('1', $digits, '0');
        $end = (int) str_pad('9', $digits, '9');
        return (string) rand($beginning, $end);
    }
}
