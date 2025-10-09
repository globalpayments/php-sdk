<?php

namespace GlobalPayments\Api\Tests\Unit\Entities\HPP;

use GlobalPayments\Api\Entities\HPPAuthenticationConfiguration;
use GlobalPayments\Api\Entities\Enums\{ChallengeRequestIndicator, ExemptStatus};
use PHPUnit\Framework\TestCase;

class HPPAuthenticationConfigurationTest extends TestCase
{
    private $config;

    public function setUp(): void
    {
        $this->config = new HPPAuthenticationConfiguration();
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(HPPAuthenticationConfiguration::class, $this->config);
        $this->assertNull($this->config->preference);
        $this->assertNull($this->config->exemptStatus);
        $this->assertNull($this->config->billingAddressRequired);
    }

    public function testValidateWithValidPreference()
    {
        $this->config->preference = ChallengeRequestIndicator::NO_PREFERENCE;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithInvalidPreference()
    {
        $this->config->preference = 'INVALID_PREFERENCE';
        $errors = $this->config->validate();
        $this->assertContains('Invalid authentication preference value', $errors);
    }

    public function testValidateWithValidExemptStatus()
    {
        $this->config->exemptStatus = ExemptStatus::LOW_VALUE;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithInvalidExemptStatus()
    {
        $this->config->exemptStatus = 'INVALID_STATUS';
        $errors = $this->config->validate();
        $this->assertContains('Invalid exempt status value, can only be "LOW_VALUE" in Hosted Payment Pages', $errors);
    }

    public function testValidateWithNonLowValueExemptStatus()
    {
        $this->config->exemptStatus = 'HIGH_VALUE';
        $errors = $this->config->validate();
        $this->assertContains('Invalid exempt status value, can only be "LOW_VALUE" in Hosted Payment Pages', $errors);
    }

    public function testValidateWithValidBillingAddressRequired()
    {
        $this->config->billingAddressRequired = true;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithInvalidBillingAddressRequired()
    {
        $this->config->billingAddressRequired = 'MAYBE';
        $errors = $this->config->validate();
        $this->assertContains('billingAddressRequired must be a boolean value', $errors);
    }

    public function testValidateWithMultipleErrors()
    {
        $this->config->preference = 'INVALID';
        $this->config->exemptStatus = 'INVALID';
        $this->config->billingAddressRequired = 'INVALID';
        
        $errors = $this->config->validate();
        $this->assertCount(3, $errors);
    }

    public function testValidateWithEmptyValues()
    {
        // Empty values should not cause validation errors 
        $this->config->preference = '';
        $this->config->exemptStatus = '';
        $this->config->billingAddressRequired = '';
        
        $errors = $this->config->validate();
        // Empty strings will fail enum validation and boolean validation
        $this->assertCount(2, $errors); // preference and billingAddressRequired should fail
    }

    public function testValidateWithNullValues()
    {
        // Null values should not cause validation errors
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testToArrayWithAllValues()
    {
        $this->config->preference = ChallengeRequestIndicator::NO_PREFERENCE;
        $this->config->exemptStatus = ExemptStatus::LOW_VALUE;
        $this->config->billingAddressRequired = true;

        $result = $this->config->toArray();

        $expected = [
            'preference' => ChallengeRequestIndicator::NO_PREFERENCE,
            'exempt_status' => ExemptStatus::LOW_VALUE,
            'billing_address_required' => true
        ];

        $this->assertEquals($expected, $result);
    }

    public function testToArrayWithPartialValues()
    {
        $this->config->preference = ChallengeRequestIndicator::CHALLENGE_MANDATED;

        $result = $this->config->toArray();

        $expected = [
            'preference' => ChallengeRequestIndicator::CHALLENGE_MANDATED
        ];

        $this->assertEquals($expected, $result);
    }

    public function testToArrayWithEmptyValues()
    {
        $result = $this->config->toArray();
        $this->assertEmpty($result);
    }

    public function testAllValidPreferenceValues()
    {
        $validPreferences = [
            ChallengeRequestIndicator::NO_PREFERENCE,
            ChallengeRequestIndicator::CHALLENGE_MANDATED,
            ChallengeRequestIndicator::NO_CHALLENGE_REQUESTED
        ];

        foreach ($validPreferences as $preference) {
            $this->config->preference = $preference;
            $errors = $this->config->validate();
            $this->assertEmpty($errors, "Failed for preference: $preference");
        }
    }

    public function testAllValidBillingAddressRequiredValues()
    {
        $validValues = [
            true,
            false
        ];

        foreach ($validValues as $value) {
            $this->config->billingAddressRequired = $value;
            $errors = $this->config->validate();
            $this->assertEmpty($errors, "Failed for billingAddressRequired: $value");
        }
    }
}
