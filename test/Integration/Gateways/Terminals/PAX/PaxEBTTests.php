<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;

class PaxEBTTests extends TestCase
{

    private $device;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown()
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

        return $config;
    }

    public function testEbtFoodstampPurchase()
    {
        $response = $this->device->ebtPurchase(10)
            ->withCurrency(CurrencyType::FOODSTAMPS)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals('F', $response->ebtType);
    }

    public function testEbtCashBenefitPurchase()
    {
        $response = $this->device->ebtPurchase(10)
            ->withCurrency(CurrencyType::CASH_BENEFITS)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertEquals('C', $response->ebtType);
    }

    public function testEbtVoucherPurchase()
    {
        $response = $this->device->ebtPurchase(10)
            ->withCurrency(CurrencyType::VOUCHER)
            ->withAllowDuplicates(true)
            ->execute();
            $this->assertNotNull($response);
            $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testEbtFoodstampBalanceInquiry()
    {
        $response = $this->device->ebtBalance()
            ->withCurrency(CurrencyType::FOODSTAMPS)
            ->execute();
            $this->assertNotNull($response);
            $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testEbtCashBenefitsBalanceInquiry()
    {
        $response = $this->device->ebtBalance()
            ->withCurrency(CurrencyType::CASH_BENEFITS)
            ->execute();
            $this->assertNotNull($response);
            $this->assertEquals("00", $response->deviceResponseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage Property `currency`is equal to the expected value `VOUCHER`
     */
    public function testEbtBalanceInquiryWithVoucher()
    {
        $this->device->ebtBalance()
            ->withCurrency(CurrencyType::VOUCHER)
            ->execute();
    }

    public function testEbtFoodStampRefund()
    {
        $response = $this->device->ebtRefund(10)
            ->withCurrency(CurrencyType::FOODSTAMPS)
            ->execute();
            $this->assertNotNull($response);
            $this->assertEquals("00", $response->deviceResponseCode);
    }

    public function testEbtCashBenefitRefund()
    {
        $response = $this->device->ebtRefund(10)
        ->withCurrency(CurrencyType::FOODSTAMPS)
            ->execute();
            $this->assertNotNull($response);
            $this->assertEquals("00", $response->deviceResponseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage amount cannot be null for this transaction type
     */
    public function testEbtRefundAllowDup()
    {
        $this->device->ebtRefund()
            ->withAllowDuplicates(true)
            ->execute();
    }

    public function testEbtCashBenefitWithdrawal()
    {
        $response = $this->device->ebtWithdrawl(10)
            ->withCurrency(CurrencyType::CASH_BENEFITS)
            ->execute();
            $this->assertNotNull($response);
            $this->assertEquals("00", $response->deviceResponseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage currency cannot be null for this transaction type
     */
    public function testEbtBenefitWithdrawalAllowDup()
    {
        $this->device->ebtWithdrawl(10)
            ->withAllowDuplicates(true)
            ->execute();
    }
}
