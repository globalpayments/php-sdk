<?php

namespace GlobalPayments\Api\Tests\Unit\Entities\HPP;

use GlobalPayments\Api\Entities\{HPPPaymentMethodConfiguration};
use GlobalPayments\Api\Entities\Enums\{HPPStorageModes};
use PHPUnit\Framework\TestCase;

class HPPPaymentMethodConfigurationTest extends TestCase
{
    private $config;

    public function setUp(): void
    {
        $this->config = new HPPPaymentMethodConfiguration();
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(HPPPaymentMethodConfiguration::class, $this->config);
        $this->assertInstanceOf(HPPAuthenticationConfiguration::class, $this->config->authentications);
        $this->assertInstanceOf(HPPApmConfiguration::class, $this->config->apm);
        $this->assertEquals(HPPStorageModes::PROMPT, $this->config->storageMode);
    }

    public function testValidateWithValidStorageMode()
    {
        $this->config->storageMode = HPPStorageModes::ON_SUCCESS;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithInvalidStorageMode()
    {
        $this->config->storageMode = 'INVALID_MODE';
        $errors = $this->config->validate();
        $this->assertStringContainsString('Invalid storage mode: INVALID_MODE', $errors[0]);
    }

    public function testValidateWithNullStorageMode()
    {
        $this->config->storageMode = null;
        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithValidSubConfigurations()
    {
        $this->config->authentications->preference = ChallengeRequestIndicator::NO_PREFERENCE;
        $this->config->apm->shippingAddressEnabled = true;
        $this->config->storageMode = HPPStorageModes::ALWAYS;

        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithInvalidSubConfigurations()
    {
        $this->config->authentications->preference = 'INVALID_PREFERENCE';
        $this->config->apm->shippingAddressEnabled = 'INVALID_VALUE';
        $this->config->storageMode = 'INVALID_MODE';

        $errors = $this->config->validate();
        $this->assertGreaterThan(2, count($errors)); // Should have multiple errors
    }

    public function testValidateWithNullSubConfigurations()
    {
        $this->config->authentications = null;
        $this->config->apm = null;

        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateAuthenticationsOnly()
    {
        $this->config->authentications->preference = ChallengeRequestIndicator::CHALLENGE_MANDATED;
        $this->config->apm = null;

        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateApmOnly()
    {
        $this->config->authentications = null;
        $this->config->apm->addressOverride = false;

        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testAllValidStorageModes()
    {
        $validModes = [
            HPPStorageModes::PROMPT,
            HPPStorageModes::ON_SUCCESS,
            HPPStorageModes::ALWAYS
        ];

        foreach ($validModes as $mode) {
            $this->config->storageMode = $mode;
            $errors = $this->config->validate();
            $this->assertEmpty($errors, "Failed for storage mode: $mode");
        }
    }

    public function testStorageModeAssignment()
    {
        $this->config->storageMode = HPPStorageModes::ALWAYS;
        $this->assertEquals(HPPStorageModes::ALWAYS, $this->config->storageMode);
    }

    public function testSubConfigurationAssignment()
    {
        $authConfig = new HPPAuthenticationConfiguration();
        $authConfig->preference = ChallengeRequestIndicator::NO_CHALLENGE_REQUESTED;

        $apmConfig = new HPPApmConfiguration();
        $apmConfig->shippingAddressEnabled = true;

        $this->config->authentications = $authConfig;
        $this->config->apm = $apmConfig;

        $this->assertEquals($authConfig, $this->config->authentications);
        $this->assertEquals($apmConfig, $this->config->apm);
    }

    public function testValidateWithComplexScenario()
    {
        // Set up a complex valid scenario
        $this->config->authentications->preference = ChallengeRequestIndicator::CHALLENGE_MANDATED;
        $this->config->authentications->billingAddressRequired = true;
        $this->config->apm->shippingAddressEnabled = false;
        $this->config->apm->addressOverride = true;
        $this->config->storageMode = HPPStorageModes::ON_SUCCESS;

        $errors = $this->config->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithComplexInvalidScenario()
    {
        // Set up a complex invalid scenario
        $this->config->authentications->preference = 'INVALID_PREF';
        $this->config->authentications->billingAddressRequired = 'INVALID_BILLING';
        $this->config->apm->shippingAddressEnabled = 'INVALID_SHIPPING';
        $this->config->apm->addressOverride = 'INVALID_OVERRIDE';
        $this->config->storageMode = 'INVALID_STORAGE';

        $errors = $this->config->validate();
        $this->assertEquals(5, count($errors));
    }

    public function testErrorMessageFormat()
    {
        $this->config->storageMode = 'WRONG_MODE';
        $errors = $this->config->validate();
        
        $this->assertStringContainsString('Invalid storage mode: WRONG_MODE', $errors[0]);
        $this->assertStringContainsString('PROMPT', $errors[0]);
        $this->assertStringContainsString('ON_SUCCESS', $errors[0]);
        $this->assertStringContainsString('ALWAYS', $errors[0]);
    }
}
