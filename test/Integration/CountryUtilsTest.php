<?php

use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class CountryUtilsTest extends TestCase
{
    public function testGetCountryCodeExact()
    {
        $result = CountryUtils::getCountryCodeByCountry('Ireland');
        $this->assertNotNull($result);
        $this->assertEquals('IE', $result);
    }

    public function testGetCountryCodeMisspelled()
    {
        $result = CountryUtils::getCountryCodeByCountry('Afganistan');
        $this->assertNotNull($result);
        $this->assertEquals('AF', $result);
    }

    public function testGetCountryCodeFromPartial()
    {
        $result = CountryUtils::getCountryCodeByCountry('Republic of Congo');
        $this->assertNotNull($result);
        $this->assertEquals('CD', $result);
    }

    public function testGetCountryCodeByExactCode()
    {
        $result = CountryUtils::getCountryCodeByCountry('IE');
        $this->assertNotNull($result);
        $this->assertEquals('IE', $result);
    }

    public function testGetCountryCodeByPartialCode()
    {
        $result = CountryUtils::getCountryCodeByCountry('USA');
        $this->assertNotNull($result);
        $this->assertEquals('US', $result);
    }

    public function testGetCountryCodeNullDoesNotError()
    {
        $result = CountryUtils::getCountryCodeByCountry(null);
        $this->assertNull($result);
    }

    public function testGetCountryCodeFakeCountry()
    {
        $result = CountryUtils::getCountryCodeByCountry('FakeCountry');
        $this->assertNull($result);
    }

    public function testGetCountryCodeFakeCountry2()
    {
        $result = CountryUtils::getCountryCodeByCountry('Fakeistan');
        $this->assertNull($result);
    }

    public function testGetCountryCodeFakeCountry3()
    {
        $result = CountryUtils::getCountryCodeByCountry('MyRussia');
        $this->assertNull($result);
    }

    public function testGetCountryByCodeExact()
    {
        $result = CountryUtils::getCountryByCode('IE');
        $this->assertNotNull($result);
        $this->assertEquals('Ireland', $result);
    }

    public function testGetCountryByThreeDigitCode()
    {
        $result = CountryUtils::getCountryByCode('USA');
        $this->assertNotNull($result);
        $this->assertEquals('United States of America', $result);
    }

    public function testGetCountryByCodeNullDoesNotError()
    {
        $result = CountryUtils::getCountryByCode(null);
        $this->assertNull($result);
    }

    public function testCheckAddressCodeFromCountryExact()
    {
        $address = new Address();
        $address->country = "United States of America";
        $this->assertNotNull($address->countryCode);
        $this->assertEquals('US', $address->countryCode);
    }

    public function testCheckAddressCountryFromCodeExact()
    {
        $address = new Address();
        $address->countryCode = "US";
        $this->assertNotNull($address->country);
        $this->assertEquals('United States of America', $address->country);
    }

    public function testCheckAddressCodeFromCountryFuzzy()
    {
        $address = new Address();
        $address->country = "Afganistan";
        $this->assertNotNull($address->countryCode);
        $this->assertEquals('AF', $address->countryCode);
    }

    public function testCheckAddressCountryFromCodeFuzzy()
    {
        $address = new Address();
        $address->countryCode = "USA";
        $this->assertNotNull($address->country);
        $this->assertEquals('United States of America', $address->country);
    }

    public function testAddressIsCountryExactMatch()
    {
        $address = new Address();
        $address->country = "United States of America";
        $this->assertTrue($address->isCountry("US"));
    }

    public function testAddressIsCountryExactMisMatch()
    {
        $address = new Address();
        $address->country = "United States of America";
        $this->assertFalse($address->isCountry("GB"));
    }

    public function testAddressIsCountryFuzzyMatch()
    {
        $address = new Address();
        $address->country = "Afganistan";
        $this->assertTrue($address->isCountry("AF"));
    }

    public function testAddressIsCountryFuzzyMisMatch()
    {
        $address = new Address();
        $address->country = "Afganistan";
        $this->assertFalse($address->isCountry("GB"));
    }

    public function testCountryIsGB_NoStreetAddress1()
    {
        $address = new Address();
        $address->country = 'GB';
        $address->postalCode = 'E77 4Qj';

        $this->assertNotNull($address->countryCode);
        $this->assertTrue($address->isCountry('GB'));

        $card = new \GlobalPayments\Api\PaymentMethods\CreditCardData();
        $card->number = "4111111111111111";
        $card->expMonth = 12;
        $card->expYear = 2025;
        $card->cvn = "123";
        $card->cardHolderName = "Joe Smith";

        $config = new GpEcomConfig();
        $config->merchantId = 'heartlandgpsandbox';
        $config->accountId = 'api';
        $config->sharedSecret = 'secret';
        $config->rebatePassword = 'rebate';
        $config->refundPassword = 'refund';

        ServicesContainer::configureService($config);

        $response = $card->charge(10)
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
    }

    public function testGetNumericCodeByCountryCode()
    {
        $result = CountryUtils::getNumericCodeByCountry('US');
        $this->assertNotNull($result);
        $this->assertEquals('840', $result);
    }

    public function testGetNumericCodeByCountry()
    {
        $result = CountryUtils::getNumericCodeByCountry('United State of America');
        $this->assertNotNull($result);
        $this->assertEquals('840', $result);
    }
    public function testGetNumericCodeByFakeCountry()
    {
        $result = CountryUtils::getNumericCodeByCountry('FakeCountry');
        $this->assertNull($result);
    }
}