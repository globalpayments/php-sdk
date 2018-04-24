<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector\Certifications;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\TestChecks;
use PHPUnit\Framework\TestCase;

class EcommerceCheckTest extends TestCase
{
    /** @var Address */
    private $address = null;
    private $enableCryptoUrl = true;

    private function config()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
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

    public function test001ConsumerPersonalChecking()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
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

    public function test002ConsumerBusinessChecking()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
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

    public function test003ConsumerPersonalSavings()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
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

    public function test004ConsumerBusinessSavings()
    {
        $check = TestChecks::certification(
            SecCode::WEB,
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
}
