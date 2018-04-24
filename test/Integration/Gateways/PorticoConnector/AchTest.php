<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class AchTest extends TestCase
{
    protected $eCheck;
    protected $address;
    private $enableCryptoUrl = true;

    public function setup()
    {
        $this->eCheck = new ECheck();
        $this->eCheck->accountNumber = '24413815';
        $this->eCheck->routingNumber = '490000018';
        $this->eCheck->checkType = CheckType::PERSONAL;
        $this->eCheck->secCode = SecCode::PPD;
        $this->eCheck->accountType = AccountType::CHECKING;
        $this->eCheck->entryMode = EntryMethod::MANUAL;
        $this->eCheck->checkHolderName = 'John Doe';
        $this->eCheck->driversLicenseNumber = '09876543210';
        $this->eCheck->driversLicenseState = 'TX';
        $this->eCheck->phoneNumber = '8003214567';
        $this->eCheck->birthYear = '1997';
        $this->eCheck->ssnLast4 = '4321';

        $this->address = new Address();
        $this->address->streetAddress1 = '123 Main St.';
        $this->address->city = 'Downtown';
        $this->address->state = 'NJ';
        $this->address->postalCode = '12345';

        ServicesContainer::configure($this->getConfig());
    }

    public function testCheckSale()
    {
        $response = $this->eCheck->charge(11)
            ->withCurrency('USD')
            ->withAddress($this->address)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    protected function getConfig()
    {
        $config = new ServicesConfig();
        $config->secretApiKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';
        $config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';
        return $config;
    }
}
