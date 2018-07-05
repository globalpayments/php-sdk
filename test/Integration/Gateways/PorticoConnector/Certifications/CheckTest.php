<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector\Certifications;

use PHPUnit\Framework\TestCase;

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\Tests\Data\TestChecks;

class CheckTest extends TestCase
{
    /** @var Address */
    private $address               = null;
    private $enableCryptoUrl       = true;

    private function config()
    {
        $config = new ServicesConfig();
        $config->secretApiKey  = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }

    protected function setup()
    {
        ServicesContainer::configure($this->config());

        $this->address = new Address();
        $this->address->streetAddress1 = '123 Main St.';
        $this->address->city = 'Downtown';
        $this->address->province = 'NJ';
        $this->address->postalCode = '12345';
    }

    /// ACH Debit - Consumer

    public function test001ConsumerPersonalChecking()
    {
        $check = TestChecks::certification(
            SecCode::PPD,
            CheckType::PERSONAL,
            AccountType::CHECKING
        );

        $response = $check->charge(11.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 25
        $voidResponse = $response->void()->execute();
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    public function test002ConsumerBusinessChecking()
    {
        $check = TestChecks::certification(
            SecCode::PPD,
            CheckType::BUSINESS,
            AccountType::CHECKING
        );

        $response = $check->charge(12.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test003ConsumerPersonalSavings()
    {
        $check = TestChecks::certification(
            SecCode::PPD,
            CheckType::PERSONAL,
            AccountType::SAVINGS
        );

        $response = $check->charge(13.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test004ConsumerBusinessSavings()
    {
        $check = TestChecks::certification(
            SecCode::PPD,
            CheckType::BUSINESS,
            AccountType::SAVINGS
        );

        $response = $check->charge(14.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test005CorporatePersonalChecking()
    {
        $check = TestChecks::certification(
            SecCode::CCD,
            CheckType::PERSONAL,
            AccountType::CHECKING,
            "Heartland Pays"
        );

        $response = $check->charge(15.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // test case 26
        $voidResponse = $response->void()->execute();
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->responseCode);
    }

    public function test006CorporateBusinessChecking()
    {
        $check = TestChecks::certification(
            SecCode::CCD,
            CheckType::BUSINESS,
            AccountType::CHECKING,
            "Heartland Pays"
        );

        $response = $check->charge(16.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test007CorporatePersonalSavings()
    {
        $check = TestChecks::certification(
            SecCode::CCD,
            CheckType::PERSONAL,
            AccountType::SAVINGS,
            "Heartland Pays"
        );

        $response = $check->charge(17.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test008CorporateBusinessSavings()
    {
        $check = TestChecks::certification(
            SecCode::CCD,
            CheckType::BUSINESS,
            AccountType::SAVINGS,
            "Heartland Pays"
        );

        $response = $check->charge(18.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test009EgoldPersonalChecking()
    {
        $check = TestChecks::certification(
            SecCode::POP,
            CheckType::PERSONAL,
            AccountType::CHECKING
        );

        $response = $check->charge(11.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test010EgoldBusinessChecking()
    {
        $check = TestChecks::certification(
            SecCode::CCD,
            CheckType::BUSINESS,
            AccountType::CHECKING
        );

        $response = $check->charge(12.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test011EgoldPersonalSavings()
    {
        $check = TestChecks::certification(
            SecCode::POP,
            CheckType::PERSONAL,
            AccountType::SAVINGS
        );

        $response = $check->charge(13.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test012EgoldBusinessSavings()
    {
        $check = TestChecks::certification(
            SecCode::POP,
            CheckType::BUSINESS,
            AccountType::SAVINGS
        );

        $response = $check->charge(14.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test013EsilverPersonalChecking()
    {
        $check = TestChecks::certification(
            SecCode::POP,
            CheckType::PERSONAL,
            AccountType::CHECKING
        );

        $response = $check->charge(15.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test014EsilverBusinessChecking()
    {
        $check = TestChecks::certification(
            SecCode::CCD,
            CheckType::BUSINESS,
            AccountType::CHECKING
        );

        $response = $check->charge(16.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test015EsilverPersonalSavings()
    {
        $check = TestChecks::certification(
            SecCode::POP,
            CheckType::PERSONAL,
            AccountType::SAVINGS
        );

        $response = $check->charge(17.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test016EsilverBusinessSavings()
    {
        $check = TestChecks::certification(
            SecCode::POP,
            CheckType::BUSINESS,
            AccountType::SAVINGS
        );

        $response = $check->charge(18.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Processor Configuration error
     */
    public function test017EbronzePersonalChecking()
    {
        $check = TestChecks::certification(
            SecCode::EBRONZE,
            CheckType::PERSONAL,
            AccountType::CHECKING
        );

        $response = $check->charge(19.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Processor Configuration error
     */
    public function test018EbronzeBusinessChecking()
    {
        $check = TestChecks::certification(
            SecCode::EBRONZE,
            CheckType::BUSINESS,
            AccountType::CHECKING
        );

        $response = $check->charge(20.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Processor Configuration error
     */
    public function test019EbronzePersonalSavings()
    {
        $check = TestChecks::certification(
            SecCode::EBRONZE,
            CheckType::PERSONAL,
            AccountType::SAVINGS
        );

        $response = $check->charge(21.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Processor Configuration error
     */
    public function test020EbronzeBusinessSavings()
    {
        $check = TestChecks::certification(
            SecCode::EBRONZE,
            CheckType::BUSINESS,
            AccountType::SAVINGS
        );

        $response = $check->charge(22.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test021WebPersonalChecking()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
            CheckType::PERSONAL,
            AccountType::CHECKING
        );

        $response = $check->charge(23.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test022WebBusinessChecking()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
            CheckType::BUSINESS,
            AccountType::CHECKING
        );

        $response = $check->charge(24.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test023WebPersonalSavings()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
            CheckType::PERSONAL,
            AccountType::SAVINGS
        );

        $response = $check->charge(25.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function test024WebBusinessSavings()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
            CheckType::BUSINESS,
            AccountType::SAVINGS
        );

        $response = $check->charge(5.00)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }
}
