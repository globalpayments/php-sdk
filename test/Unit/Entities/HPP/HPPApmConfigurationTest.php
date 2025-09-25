<?php

namespace GlobalPayments\Api\Tests\Unit\Entities\HPP;

use GlobalPayments\Api\Entities\HPPApmConfiguration;
use GlobalPayments\Api\Entities\Enums\YesNoEnum;
use PHPUnit\Framework\TestCase;

class HPPApmConfigurationTest extends TestCase
{
    private $config;

    public function setUp(): void
    {
        $this->config = new HPPApmConfiguration();
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(HPPApmConfiguration::class, $this->config);
        $this->assertEquals(YesNoEnum::NO, $this->config->shippingAddressEnabled);
        $this->assertEquals(YesNoEnum::NO, $this->config->addressOverride);
    }

    public function testValidateWithValidShippingAddressEnabled()
    {
        $this->config->shippingAddressEnabled = YesNoEnum::YES;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithInvalidShippingAddressEnabled()
    {
        $this->config->shippingAddressEnabled = 'INVALID_VALUE';
        $errors = $this->config->validate();
        $this->assertContains('Invalid shippingAddressEnabled value', $errors);
    }

    public function testValidateWithValidAddressOverride()
    {
        $this->config->addressOverride = YesNoEnum::YES;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithInvalidAddressOverride()
    {
        $this->config->addressOverride = 'INVALID_VALUE';
        $errors = $this->config->validate();
        $this->assertContains('Invalid addressOverride value', $errors);
    }

    public function testValidateWithBothValid()
    {
        $this->config->shippingAddressEnabled = YesNoEnum::YES;
        $this->config->addressOverride = YesNoEnum::NO;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithBothInvalid()
    {
        $this->config->shippingAddressEnabled = 'INVALID1';
        $this->config->addressOverride = 'INVALID2';
        $errors = $this->config->validate();
        $this->assertCount(2, $errors);
        $this->assertContains('Invalid shippingAddressEnabled value', $errors);
        $this->assertContains('Invalid addressOverride value', $errors);
    }

    public function testValidateWithEmptyValues()
    {
        $this->config->shippingAddressEnabled = '';
        $this->config->addressOverride = '';
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testToArrayWithAllValues()
    {
        $this->config->shippingAddressEnabled = YesNoEnum::YES;
        $this->config->addressOverride = YesNoEnum::NO;

        $result = $this->config->toArray();

        $expected = [
            'shipping_address_enabled' => YesNoEnum::YES,
            'address_override' => YesNoEnum::NO
        ];

        $this->assertEquals($expected, $result);
    }

    public function testToArrayWithPartialValues()
    {
        $this->config->shippingAddressEnabled = YesNoEnum::YES;
        $this->config->addressOverride = ''; // Empty value should not appear in array

        $result = $this->config->toArray();

        $expected = [
            'shipping_address_enabled' => YesNoEnum::YES
        ];

        $this->assertEquals($expected, $result);
    }

    public function testToArrayWithEmptyValues()
    {
        $this->config->shippingAddressEnabled = '';
        $this->config->addressOverride = '';

        $result = $this->config->toArray();
        $this->assertEmpty($result);
    }

    public function testToArrayWithDefaultValues()
    {
        $result = $this->config->toArray();

        $expected = [
            'shipping_address_enabled' => YesNoEnum::NO,
            'address_override' => YesNoEnum::NO
        ];

        $this->assertEquals($expected, $result);
    }

    public function testAllValidYesNoValues()
    {
        $validValues = [YesNoEnum::YES, YesNoEnum::NO];

        foreach ($validValues as $value) {
            $this->config->shippingAddressEnabled = $value;
            $this->config->addressOverride = $value;
            $errors = $this->config->validate();
            $this->assertEmpty($errors, "Failed for value: $value");
        }
    }

    public function testPropertyAssignment()
    {
        $this->config->shippingAddressEnabled = YesNoEnum::YES;
        $this->config->addressOverride = YesNoEnum::NO;

        $this->assertEquals(YesNoEnum::YES, $this->config->shippingAddressEnabled);
        $this->assertEquals(YesNoEnum::NO, $this->config->addressOverride);
    }
}
