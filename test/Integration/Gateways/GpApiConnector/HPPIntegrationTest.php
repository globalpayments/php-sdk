<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpApiConnector;

use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiAuthorizationRequestBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\{PayerDetails, PhoneNumber, Address, HPPData, Transaction};
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
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
use GlobalPayments\Api\Services\HPPService;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
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
        self::$config->appId  = BaseGpApiTestConfig::APP_ID;
        self::$config->appKey = BaseGpApiTestConfig::APP_KEY;
        self::$config->environment = Environment::TEST;
        self::$config->country = 'GB';
        self::$config->channel = Channel::CardNotPresent;
        self::$config->requestLogger = new RequestConsoleLogger();

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
    public function testCreateBasicHPPUrl(): void
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

        $this->assertValidPayByLinkResponse($response);
    }

    /**
     * @group integration
     * @group hpp
     * @group 3ds
     */
    public function testCreateHPPUrlWith3DSAuthentication(): void
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

        $this->assertValidPayByLinkResponse($response);
    }

    /**
     * @group integration
     * @group hpp
     * @group shipping
     */
    public function testCreateHPPUrlWithShipping(): void
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

        $this->assertValidPayByLinkResponse($response);
    }

    /**
     * @group integration
     * @group hpp
     * @group iframe
     */
    public function testCreateHPPUrlWithIframeConfiguration(): void
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

        $this->assertValidPayByLinkResponse($response);
    }

    /**
     * @group integration
     * @group hpp
     * @group comprehensive
     */
    public function testCreateComprehensiveHPPUrl(): void
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

        $this->assertValidPayByLinkResponse($response);
        
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
    public function testHPPUrlGenerationErrorHandling(): void
    {
        $this->expectException(\Exception::class);
        
        // Create HPP with missing required fields to test error handling
        HPPBuilder::create()
            ->withName('Error Test')
            ->withDescription('Test error handling')
            // Missing required fields like amount, currency, payer
            ->execute();
    }

    /**
     * @group integration
     * @group hpp
     * @group dcc
     */
    public function testDccModeEnabled(): void
    {
        $this->assertDccMode(true, 'YES');
    }

    /**
     * @group integration
     * @group hpp
     * @group dcc
     */
    public function testDccModeDisabled(): void
    {
        $this->assertDccMode(false, 'NO');
    }

    private function assertDccMode(bool $modeEnabled, string $expected): void
    {
        $hppData = $this->createDccHppData($modeEnabled);
        $requestBody = $this->getDccRequestBody($hppData);
        $serialized = 'NOT SET';
        if (is_array($requestBody)) {
            $serialized = $requestBody['order']['transaction_configuration']['currency_conversion_mode'] ?? 'NOT SET';
        }

        $this->assertSame($modeEnabled, $hppData->order->HPPTransactionConfiguration->currencyConversionMode);
        $this->assertSame($expected, $serialized, 'currency_conversion_mode must serialize correctly');

        $dccConfig = $this->createDccConfig();
        ServicesContainer::configureService($dccConfig);
        try {
            $response = HPPService::create($hppData)->execute();
        } finally {
            ServicesContainer::configureService(self::$config);
        }
        $this->assertValidPayByLinkResponse($response);
    }

    private function createDccHppData(bool $modeEnabled): HPPData
    {
        $name = $modeEnabled ? 'DCC Enabled' : 'DCC Disabled';
        $reference = $modeEnabled ? 'INT_TEST_DCC_ON_' . uniqid() : 'INT_TEST_DCC_OFF_' . uniqid();

        return HPPBuilder::create()
            ->withName($name)
            ->withReference($reference)
            ->withAmount('1000')
            ->withCurrency('USD')
            ->withPayer($this->validPayer)
            ->withTransactionConfig('CNP', 'US', CaptureMode::AUTO)
            ->withCurrencyConversionMode($modeEnabled)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status'
            )
            ->build();
    }

    private function createDccConfig(): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->appId = BaseGpApiTestConfig::APP_ID;
        $config->appKey = BaseGpApiTestConfig::APP_KEY;
        $config->environment = Environment::TEST;
        $config->country = 'US';
        $config->channel = Channel::CardNotPresent;
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->transactionProcessingAccountName = 'dcc';
        $config->requestLogger = self::$config->requestLogger;

        return $config;
    }

    private function getDccRequestBody(HPPData $hppData): array
    {
        $authBuilder = HPPService::create($hppData);
        $gpApiRequest = (new GpApiAuthorizationRequestBuilder())->buildRequest($authBuilder, $this->createDccConfig());
        return $gpApiRequest->requestBody;
    }
    
    private function assertValidPayByLinkResponse(Transaction $response): void
    {
        $this->assertNotNull($response);
        $this->assertNotNull($response->payByLinkResponse);
        $this->assertNotNull($response->payByLinkResponse->url);
        $this->assertNotNull($response->payByLinkResponse->id);
        $this->assertStringContainsString('https://', $response->payByLinkResponse->url);
        $this->assertMatchesRegularExpression('/^https:\/\/.*\/hpp\/.*/', $response->payByLinkResponse->url);
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up service container
        ServicesContainer::configureService(null);
    }
}
