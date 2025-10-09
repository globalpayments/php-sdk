<?php

namespace GlobalPayments\Api\Tests\Unit\Services\HPP;

use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\{Address, PayerDetails, PhoneNumber};
use GlobalPayments\Api\Entities\Enums\{
    CaptureMode,
    ChallengeRequestIndicator,
    Channel,
    Environment,
    ExemptStatus,
    HPPAllowedPaymentMethods,
    HPPStorageModes,
    HPPTypes,
    PaymentMethodUsageMode,
    PhoneNumberType,
};
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use PHPUnit\Framework\TestCase;

class HPPUrlGenerationTest extends TestCase
{
    private $config;
    private $validPayer;
    private $validBillingAddress;
    private $validShippingAddress;
    private $validPhone;

    public function setUp(): void
    {
        // Set up test configuration
        $this->config = new GpApiConfig();
        $this->config->appId = 'YOUR_APP_ID';
        $this->config->appKey = 'YOUR_APP_KEY';
        $this->config->environment = Environment::TEST;
        $this->config->country = 'GB';
        $this->config->channel = Channel::CardNotPresent;

        // Configure the service container
        ServicesContainer::configureService($this->config);

        // Set up valid test entities
        $this->setupValidTestEntities();
    }

    private function setupValidTestEntities(): void
    {
        // Valid payer
        $this->validPayer = new PayerDetails();
        $this->validPayer->firstName = 'John';
        $this->validPayer->lastName = 'Doe';
        $this->validPayer->name = 'John Doe';
        $this->validPayer->email = 'john.doe@example.com';
        $this->validPayer->status = 'NEW';

        // Valid phone number
        $this->validPhone = new PhoneNumber("44", "01234567890", PhoneNumberType::MOBILE);

        // Valid billing address
        $this->validBillingAddress = new Address();
        $this->validBillingAddress->streetAddress1 = '123 Test Street';
        $this->validBillingAddress->city = 'Test City';
        $this->validBillingAddress->state = 'TST';
        $this->validBillingAddress->postalCode = 'TS1 2AB';
        $this->validBillingAddress->country = 'GB';
        $this->validBillingAddress->countryCode = 'GB';

        // Valid shipping address
        $this->validShippingAddress = new Address();
        $this->validShippingAddress->streetAddress1 = '456 Shipping Street';
        $this->validShippingAddress->city = 'Shipping City';
        $this->validShippingAddress->state = 'SHP';
        $this->validShippingAddress->postalCode = 'SH3 4CD';
        $this->validShippingAddress->country = 'GB';
        $this->validShippingAddress->countryCode = 'GB';

        // Assign addresses and phone to payer
        $this->validPayer->billingAddress = $this->validBillingAddress;
        $this->validPayer->shippingAddress = $this->validShippingAddress;
        $this->validPayer->mobilePhone = $this->validPhone;
        $this->validPayer->shippingPhone = $this->validPhone;
    }

    public function testBasicHPPUrlGeneration()
    {
        $reference = 'TEST_REF_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('Basic HPP Test')
            ->withDescription('Basic test payment')
            ->withReference($reference)
            ->withAmount('1000') // £10.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO);

        $result = $builder->execute();
        
        // Verify the result structure
        $this->assertNotNull($result);
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $this->assertObjectHasProperty('url', $result->payByLinkResponse);
        
        // Verify the URL is valid
        $url = $result->payByLinkResponse->url;
        $this->assertIsString($url);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false, 'Generated URL should be valid');
        $this->assertStringStartsWith('https://', $url, 'HPP URL should use HTTPS');
        
        // Verify URL contains expected parameters from our request
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
        
        // Test that we can extract useful information from the response
        $this->assertObjectHasProperty('id', $result->payByLinkResponse);
        $hppId = $result->payByLinkResponse->id;
        $this->assertIsString($hppId);
        $this->assertNotEmpty($hppId);
    }

    public function testHPPUrlGenerationWithAuthentication()
    {
        $reference = '3DS_TEST_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('3DS Authentication Test')
            ->withDescription('Test payment with 3DS authentication')
            ->withReference($reference)
            ->withAmount('2500') // £25.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withAuthentication(
                ChallengeRequestIndicator::CHALLENGE_PREFERRED,
                ExemptStatus::LOW_VALUE,
                true
            );

        // Execute and test URL generation
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $this->assertObjectHasProperty('url', $result->payByLinkResponse);
        
        $url = $result->payByLinkResponse->url;
        $this->assertIsString($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        $this->assertStringContainsString('globalpay', $url);
        $this->assertStringContainsString('hpp', $url);
    }

   

    public function testComprehensiveHPPUrlGenerationWithAllOptions()
    {
        $reference = 'COMPREHENSIVE_' . uniqid();
        
        $builder = HPPBuilder::create()
            // Basic configuration
            ->withName('Comprehensive HPP Test')
            ->withDescription('Complete test with all HPP options')
            ->withReference($reference)
            ->withAmount('10000') // £100.00 in pence
            ->withCurrency('GBP')
            ->withType(HPPTypes::HOSTED_PAYMENT_PAGE)
            
            // Payer configuration
            ->withPayer($this->validPayer)
            ->withPayerPhone($this->validPhone)
            
            // Address configuration
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withAddressMatchIndicator(true)
            ->withShippingPhone($this->validPhone)
            
            // Notification configuration
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            
            // Transaction configuration
            ->withTransactionConfig(
                Channel::CardNotPresent,
                'GB',
                CaptureMode::AUTO,
                [HPPAllowedPaymentMethods::CARD],
                PaymentMethodUsageMode::SINGLE
            )
            
            // Authentication configuration
            ->withAuthentication(
                ChallengeRequestIndicator::CHALLENGE_PREFERRED,
                ExemptStatus::LOW_VALUE,
                true
            )
            
            // Shipping configuration
            ->withShipping(true, '999') // £9.99 shipping
            
            // Display configuration
            ->withHPPDisplayConfiguration(
                'https://example.com/iframe_callback',
                'https://example.com/iframe_callback'
            )
            
            // Additional configuration
            ->withReferrerUrl('https://example.com/checkout')
            ->withIpAddress('192.168.1.1')
            ->withCurrencyConversionMode(true)
            ->withExpirationDate(date('Y-m-d\TH:i:s\Z', strtotime('+7 days')))
            ->withOrderReference('ORDER_' . uniqid())
            ->withPaymentMethodConfig(HPPStorageModes::PROMPT)
            ->withApm(true, false);

        // Execute and test comprehensive URL generation
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $this->assertObjectHasProperty('url', $result->payByLinkResponse);
        $this->assertObjectHasProperty('id', $result->payByLinkResponse);
        
        $url = $result->payByLinkResponse->url;
        $hppId = $result->payByLinkResponse->id;
        
        // Test URL structure and validity
        $this->assertIsString($url);
        $this->assertNotEmpty($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        $this->assertStringStartsWith('https://', $url);
        
        // Test HPP ID is present
        $this->assertIsString($hppId);
        $this->assertNotEmpty($hppId);
        
        // Test URL contains expected parameters
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
        
        // Test that URL is accessible (basic structure check)
        $urlParts = parse_url($url);
        $this->assertArrayHasKey('scheme', $urlParts);
        $this->assertArrayHasKey('host', $urlParts);
        $this->assertEquals('https', $urlParts['scheme']);
    }

    public function testHPPResponseStructureAndUrlValidation()
    {
        $reference = 'STRUCTURE_TEST_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('Response Structure Test')
            ->withDescription('Test to validate HPP response structure')
            ->withReference($reference)
            ->withAmount('1500') // £15.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO);

        // Execute the builder
        $result = $builder->execute();
        
        // Test overall response structure (based on example: $first_example->payByLinkResponse->url)
        $this->assertNotNull($result, 'HPP response should not be null');
        $this->assertIsObject($result, 'HPP response should be an object');
        
        // Test payByLinkResponse property exists
        $this->assertObjectHasProperty('payByLinkResponse', $result, 'Response should have payByLinkResponse property');
        $payByLinkResponse = $result->payByLinkResponse;
        
        // Test payByLinkResponse structure
        $this->assertIsObject($payByLinkResponse, 'payByLinkResponse should be an object');
        $this->assertObjectHasProperty('url', $payByLinkResponse, 'payByLinkResponse should have url property');
        $this->assertObjectHasProperty('id', $payByLinkResponse, 'payByLinkResponse should have id property');
        
        // Test URL properties
        $url = $payByLinkResponse->url;
        $this->assertIsString($url, 'URL should be a string');
        $this->assertNotEmpty($url, 'URL should not be empty');
        $this->assertGreaterThan(10, strlen($url), 'URL should be a reasonable length');
        
        // Test URL is valid HTTP/HTTPS
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false, 'URL should be valid');
        $this->assertMatchesRegularExpression('/^https?:\/\//', $url, 'URL should start with http:// or https://');
        
        // Test ID properties
        $id = $payByLinkResponse->id;
        $this->assertIsString($id, 'ID should be a string');
        $this->assertNotEmpty($id, 'ID should not be empty');
        
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
        
        // Test URL has payment-related keywords (typical of payment URLs)
        $hasPaymentKeywords = (
            stripos($url, 'pay') !== false || 
            stripos($url, 'payment') !== false || 
            stripos($url, 'checkout') !== false ||
            stripos($url, 'hpp') !== false
        );
        $this->assertTrue($hasPaymentKeywords, 'URL should contain payment-related keywords');
    }

    public function testHPPUrlGenerationWithShippingCharges()
    {
        $reference = 'SHIPPING_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('Shipping Test')
            ->withDescription('Test payment with shipping charges')
            ->withReference($reference)
            ->withAmount('3000') // £30.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withShipping(true, '750'); // £7.50 shipping

        // Execute and validate shipping URL generation
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $url = $result->payByLinkResponse->url;
        
        $this->assertIsString($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
        // Note: Shipping charges might be included in total or as separate parameter
    }

    public function testHPPUrlGenerationWithMultiplePaymentMethods()
    {
        $reference = 'MULTI_PAY_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('Multiple Payment Methods Test')
            ->withDescription('Test with multiple payment method options')
            ->withReference($reference)
            ->withAmount('7500') // £75.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(
                Channel::CardNotPresent,
                'GB',
                CaptureMode::AUTO,
                [HPPAllowedPaymentMethods::CARD, HPPAllowedPaymentMethods::BANK_PAYMENT],
                PaymentMethodUsageMode::SINGLE
            );

        // Execute and validate multiple payment methods URL generation
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $url = $result->payByLinkResponse->url;
        
        $this->assertIsString($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
    }

    public function testHPPUrlGenerationWithExpirationDate()
    {
        $expirationDate = date('Y-m-d\TH:i:s\Z', strtotime('+30 days'));
        $reference = 'EXPIRY_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('Expiration Test')
            ->withDescription('Test payment with expiration date')
            ->withReference($reference)
            ->withAmount('2000') // £20.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withExpirationDate($expirationDate);

        // Execute and validate expiration date URL generation
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $url = $result->payByLinkResponse->url;
        
        $this->assertIsString($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
        
        // Test that expiration date is properly set in response
        if (property_exists($result->payByLinkResponse, 'expiration_date')) {
            $this->assertNotEmpty($result->payByLinkResponse->expiration_date);
        }
    }

    public function testHPPBuilderValidationWithMissingRequiredFields()
    {
        $this->expectException(ArgumentException::class);
        
        // Create payer with missing required fields to trigger validation
        $invalidPayer = new PayerDetails();
        $invalidPayer->firstName = 'John';
        // Missing lastName and email which should be required
        
        HPPBuilder::create()
            ->withName('Invalid Test')
            ->withPayer($invalidPayer)
            ->execute(); // This should throw an exception
    }

    public function testHPPUrlGenerationWithMinimalValidData()
    {
        // Test with minimal required data to ensure URL generation works
        $reference = 'MINIMAL_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('Minimal Test')
            ->withDescription('Minimal required data test')
            ->withReference($reference)
            ->withAmount('100') // £1.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO);

        // Execute with minimal data
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $this->assertObjectHasProperty('url', $result->payByLinkResponse);
        
        $url = $result->payByLinkResponse->url;
        $this->assertIsString($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
    }

    public function testHPPBuilderFluentInterface()
    {
        $builder = HPPBuilder::create();
        
        // Test that all methods return the builder instance for chaining
        $result = $builder
            ->withName('Fluent Test')
            ->withDescription('Testing fluent interface')
            ->withReference('FLUENT_' . uniqid())
            ->withAmount('1000')
            ->withCurrency('GBP');
            
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(HPPBuilder::class, $result);
    }

    public function testHPPBuilderStaticCreateMethod()
    {
        $builder1 = HPPBuilder::create();
        $builder2 = HPPBuilder::create();
        
        $this->assertInstanceOf(HPPBuilder::class, $builder1);
        $this->assertInstanceOf(HPPBuilder::class, $builder2);
        $this->assertNotSame($builder1, $builder2); // Should be different instances
    }

    public function testHPPUrlGenerationWithAPMConfiguration()
    {
        $reference = 'APM_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('APM Test')
            ->withDescription('Test payment with APM configuration')
            ->withReference($reference)
            ->withAmount('4000') // £40.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withApm(true, false); // Enable shipping address, disable address override

        // Execute and validate APM configuration URL generation
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $url = $result->payByLinkResponse->url;
        
        $this->assertIsString($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
    }

    public function testHPPUrlGenerationWithCurrencyConversion()
    {
        $reference = 'CURRENCY_' . uniqid();
        
        $builder = HPPBuilder::create()
            ->withName('Currency Conversion Test')
            ->withDescription('Test payment with currency conversion')
            ->withReference($reference)
            ->withAmount('6000') // £60.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status',
                'https://example.com/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withCurrencyConversionMode(true);

        // Execute and validate currency conversion URL generation
        $result = $builder->execute();
        
        $this->assertNotNull($result);
        $this->assertObjectHasProperty('payByLinkResponse', $result);
        $url = $result->payByLinkResponse->url;
        
        $this->assertIsString($url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
        
        // Test URL contains GlobalPay domain and HPP indicators
        $this->assertStringContainsString('globalpay', $url, 'URL should be from GlobalPay domain');
        $this->assertStringContainsString('hpp', $url, 'URL should contain HPP identifier');
    }

    public function tearDown(): void
    {
        // Clean up service container
        ServicesContainer::configureService(null);
    }
}
