<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;

class PaxGiftTests extends TestCase
{

    private $device;
    protected $card;
    protected $address;

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
        $config->ipAddress = '192.168.42.219';
        $config->port = '10009';
        $config->deviceType = DeviceType::PAX_S300;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();

        $this->card = new GiftCard();
        $this->card->number = '5022440000000000098';

        return $config;
    }

    public function testGiftSale()
    {
        $response = $this->device->sale(100)
            ->withPaymentMethodType(PaymentMethodType::GIFT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testGiftSaleManual()
    {
        $response = $this->device->sale(10)
            ->withPaymentMethodType(PaymentMethodType::GIFT)
            ->withPaymentMethod($this->card)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testGiftSaleWithInvoice()
    {
        $response = $this->device->sale(100)
                ->withPaymentMethod($this->card)
                ->withInvoiceNumber(123)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testSaleNoAmount()
    {
        $response = $this->device->sale()
                ->withPaymentMethod($this->card)
                ->execute();
    }

    public function testGiftAddValue()
    {
        $response = $this->device->addValue(100)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testGiftAddValueManual()
    {
        $response = $this->device->addValue(100)
                ->withPaymentMethod($this->card)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testLoyaltySaleManual()
    {
        $response = $this->device->sale(100)
                ->withPaymentMethod($this->card)
                ->withCurrency(CurrencyType::POINTS)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testGiftVoidManual()
    {
        $response = $this->device->sale(100)
                ->withPaymentMethod($this->card)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);

        $voidResponse = $this->device->void()
            ->withPaymentMethodType(PaymentMethodType::GIFT)
            ->withTransactionId($response->transactionId)
            ->execute();

        $this->assertNotNull($voidResponse);
        $this->assertEquals("00", $voidResponse->deviceResponseCode);
    }

    public function testGiftBalance()
    {
        $response = $this->device->balance()
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testGiftBalanceManual()
    {
        $response = $this->device->balance()
                ->withPaymentMethod($this->card)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testLoyaltyBalance()
    {
        $response = $this->device->balance()
                ->withCurrency(CurrencyType::POINTS)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testLoyaltyBalanceManual()
    {
        $response = $this->device->balance()
                ->withPaymentMethod($this->card)
                ->withCurrency(CurrencyType::POINTS)
                ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
}
