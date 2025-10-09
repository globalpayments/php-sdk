<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpApiConnector;

use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\{PayerDetails, PhoneNumber, Address};
use GlobalPayments\Api\Entities\Enums\{
    Environment,
    Channel,
    CaptureMode,
    ChallengeRequestIndicator,
    ExemptStatus,
    PhoneNumberType,
    HPPTypes,
    HPPStorageModes,
    PaymentMethodUsageMode,
    HPPAllowedPaymentMethods,
};
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for HPP URL generation via GP-API
 * 
 */
class HPPIntegrationTest extends TestCase
{
    private static $config;
    private $validPayer;
    private $validBillingAddress;
    private $validShippingAddress;
    private $validPhone;

    public static function setUpBeforeClass(): void
    {
        // Set up test configuration
        self::$config = new GpApiConfig();
        self::$config->appId = "YOUR_APP_ID";
        self::$config->appKey = 'YOUR_APP_KEY';
        self::$config->environment = Environment::TEST;
        self::$config->country = 'GB';
        self::$config->channel = Channel::CardNotPresent;

        // Configure the service container
        ServicesContainer::configureService(self::$config);
    }

    public function setUp(): void
    {
        $this->setupValidTestEntities();
    }

    private function setupValidTestEntities(): void
    {
        // Valid payer
        $this->validPayer = new PayerDetails();
        $this->validPayer->firstName = 'John';
        $this->validPayer->lastName = 'Doe';
        $this->validPayer->name = 'John Doe';
        $this->validPayer->email = 'john.doe+test@example.com';
        $this->validPayer->status = 'NEW';

        // Valid phone number
        $this->validPhone = new PhoneNumber("44", "07987654321", PhoneNumberType::MOBILE);

        // Valid billing address
        $this->validBillingAddress = new Address();
        $this->validBillingAddress->streetAddress1 = '123 Test Street';
        $this->validBillingAddress->city = 'London';
        $this->validBillingAddress->state = 'LND';
        $this->validBillingAddress->postalCode = 'SW1A 1AA';
        $this->validBillingAddress->country = 'GB';
        $this->validBillingAddress->countryCode = 'GB';

        // Valid shipping address
        $this->validShippingAddress = new Address();
        $this->validShippingAddress->streetAddress1 = '456 Shipping Street';
        $this->validShippingAddress->city = 'Manchester';
        $this->validShippingAddress->state = 'MAN';
        $this->validShippingAddress->postalCode = 'M1 1AA';
        $this->validShippingAddress->country = 'GB';
        $this->validShippingAddress->countryCode = 'GB';

        // Assign addresses and phone to payer
        $this->validPayer->billingAddress = $this->validBillingAddress;
        $this->validPayer->shippingAddress = $this->validShippingAddress;
        $this->validPayer->mobilePhone = $this->validPhone;
        $this->validPayer->shippingPhone = $this->validPhone;
    }

    /**
     * @group integration
     * @group hpp
     */
    public function testCreateBasicHPPUrl()
    {
        $reference = 'INT_TEST_BASIC_' . uniqid();
        
        $response = HPPBuilder::create()
            ->withName('Integration Test - Basic HPP')
            ->withDescription('Basic integration test for HPP URL generation')
            ->withReference($reference)
            ->withAmount('1000') // £10.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->execute();

        // Assertions
        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertStringContainsString('https://', $response->payByLinkResponse->url);
        $this->assertNotNull($response->payByLinkResponse->id);
        
        // Test URL is accessible (basic format check)
        $this->assertMatchesRegularExpression('/^https:\/\/.*\/hpp\/.*/', $response->payByLinkResponse->url);
    }

    /**
     * @group integration
     * @group hpp
     * @group 3ds
     */
    public function testCreateHPPUrlWith3DSAuthentication()
    {
        $reference = 'INT_TEST_3DS_' . uniqid();
        
        $response = HPPBuilder::create()
            ->withName('Integration Test - 3DS HPP')
            ->withDescription('3DS authentication integration test')
            ->withReference($reference)
            ->withAmount('2500') // £25.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withAuthentication(
                ChallengeRequestIndicator::CHALLENGE_PREFERRED,
                ExemptStatus::LOW_VALUE,
                true
            )
            ->withAddressMatchIndicator(true)
            ->withShippingPhone($this->validPhone)
            ->execute();

        // Assertions
        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertStringContainsString('https://', $response->payByLinkResponse->url);
        $this->assertNotNull($response->payByLinkResponse->id);
    }

    /**
     * @group integration
     * @group hpp
     * @group shipping
     */
    public function testCreateHPPUrlWithShipping()
    {
        $reference = 'INT_TEST_SHIPPING_' . uniqid();
        
        $response = HPPBuilder::create()
            ->withName('Integration Test - Shipping HPP')
            ->withDescription('Shipping charges integration test')
            ->withReference($reference)
            ->withAmount('5000') // £50.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withShipping(true, '999') // £9.99 shipping
            ->execute();

        // Assertions
        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertStringContainsString('https://', $response->payByLinkResponse->url);
    }

    /**
     * @group integration
     * @group hpp
     * @group iframe
     */
    public function testCreateHPPUrlWithIframeConfiguration()
    {
        $reference = 'INT_TEST_IFRAME_' . uniqid();
        
        $response = HPPBuilder::create()
            ->withName('Integration Test - Iframe HPP')
            ->withDescription('Iframe configuration integration test')
            ->withReference($reference)
            ->withAmount('1500') // £15.00 in pence
            ->withCurrency('GBP')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'GB', CaptureMode::AUTO)
            ->withHPPDisplayConfiguration(
                'https://webhook.site/iframe_callback',
                'https://webhook.site/iframe_success'
            )
            ->withReferrerUrl('https://example.com/checkout')
            ->execute();

        // Assertions
        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertStringContainsString('https://', $response->payByLinkResponse->url);
    }

    /**
     * @group integration
     * @group hpp
     * @group comprehensive
     */
    public function testCreateComprehensiveHPPUrl()
    {
        $reference = 'INT_TEST_COMPREHENSIVE_' . uniqid();
        $orderReference = 'ORDER_' . uniqid();
        $expirationDate = date('Y-m-d\TH:i:s\Z', strtotime('+7 days'));
        
        $response = HPPBuilder::create()
            ->withName('Integration Test - Comprehensive HPP')
            ->withDescription('Comprehensive integration test with all features')
            ->withReference($reference)
            ->withAmount('10000') // £100.00 in pence
            ->withCurrency('GBP')
            ->withType(HPPTypes::HOSTED_PAYMENT_PAGE)
            ->withPayer($this->validPayer)
            ->withPayerPhone($this->validPhone)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withAddressMatchIndicator(true)
            ->withShippingPhone($this->validPhone)
            ->withTransactionConfig(
                Channel::CardNotPresent,
                'GB',
                CaptureMode::AUTO,
                [HPPAllowedPaymentMethods::CARD],
                PaymentMethodUsageMode::SINGLE
            )
            ->withAuthentication(
                ChallengeRequestIndicator::CHALLENGE_PREFERRED,
                ExemptStatus::LOW_VALUE,
                true
            )
            ->withShipping(true, '750') // £7.50 shipping
            ->withHPPDisplayConfiguration(
                'https://webhook.site/iframe_callback',
                'https://webhook.site/iframe_success'
            )
            ->withReferrerUrl('https://example.com/integration-test')
            ->withIpAddress('192.168.1.100')
            ->withCurrencyConversionMode(true)
            ->withExpirationDate($expirationDate)
            ->withOrderReference($orderReference)
            ->withPaymentMethodConfig(HPPStorageModes::PROMPT)
			//Note: in the documentation ON_SUCCESS is valid, API reports and error
            // ->withPaymentMethodConfig(HPPStorageModes::ON_SUCCESS)
            ->withApm(true, false)
            ->execute();

        // Comprehensive assertions
        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertNotNull($response->payByLinkResponse->id);
        $this->assertStringContainsString('https://', $response->payByLinkResponse->url);
        
        // Test URL format
        $this->assertMatchesRegularExpression('/^https:\/\/.*\/hpp\/.*/', $response->payByLinkResponse->url);
        
        // Test that the URL contains expected parameters (this may vary by implementation)
        $urlParts = parse_url($response->payByLinkResponse->url);
        $this->assertNotEmpty($urlParts['host']);
        $this->assertNotEmpty($urlParts['path']);
    }

   

    /**
     * Test error handling for invalid configurations
     * 
     * @group integration
     * @group hpp
     * @group error-handling
     */
    public function testHPPUrlGenerationErrorHandling()
    {
        $this->expectException(\Exception::class);
        
        // Create HPP with missing required fields to test error handling
        HPPBuilder::create()
            ->withName('Error Test')
            ->withDescription('Test error handling')
            // Missing required fields like amount, currency, payer
            ->execute();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up service container
        ServicesContainer::configureService(null);
    }
}
