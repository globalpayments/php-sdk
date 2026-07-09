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
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
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
    private const NOT_SET = 'NOT SET';

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
        $this->validPayer->reference = 'PAYER_REF_' . uniqid();

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
     * @group integration
     * @group hpp
     * @group eraty
     */
    public function testCreateHPPUrlWithEratyRedirectUrl(): void
    {
        $reference = 'INT_TEST_ERATY_HPP_' . uniqid();

        $hppData = HPPBuilder::create()
            ->withName('Integration Test - HPP eRaty Redirect URL')
            ->withDescription('HPP eRaty redirect URL integration test')
            ->withReference($reference)
            ->withAmount('210000')
            ->withCurrency('PLN')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withBillingAddress($this->validBillingAddress)
            ->withShippingAddress($this->validShippingAddress)
            ->withTransactionConfig(
                Channel::CardNotPresent,
                'GB',
                CaptureMode::AUTO,
                [HPPAllowedPaymentMethods::CARD],
                PaymentMethodUsageMode::SINGLE,
                '1'
            )
            ->build();

        $requestBody = $this->getDccRequestBody($hppData);
        $this->assertArrayHasKey('payer', $requestBody);
        $this->assertSame($this->validPayer->reference, $requestBody['payer']['reference']);
        
        // Verify ERATY is serialized in allowed_payment_methods
        $allowedMethods = $requestBody['order']['transaction_configuration']['allowed_payment_methods'] ?? [];
        $this->assertSame(
            [HPPAllowedPaymentMethods::CARD],
            $allowedMethods,
            'allowed_payment_methods must include ERATY'
        );

        $response = HPPService::create($hppData)->execute();

        $this->assertValidPayByLinkResponse($response);
        $this->assertNotSame('', trim((string) $response->payByLinkResponse->url));
        $this->assertStringStartsWith('https://', (string) $response->payByLinkResponse->url);
    }

    /**
     * @group integration
     * @group hpp
     * @group eraty
     */
    public function testInvalidAllowedPaymentMethodThrows(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessageMatches('/Validation failed: .*Invalid payment method: INVALID_METHOD/');

        HPPBuilder::create()
            ->withName('Integration Test - invalid allowed method')
            ->withReference('INT_TEST_ERATY_INVALID_' . uniqid())
            ->withAmount('210000')
            ->withCurrency('PLN')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withTransactionConfig(
                Channel::CardNotPresent,
                'PL',
                CaptureMode::AUTO,
                ['INVALID_METHOD'],
                PaymentMethodUsageMode::SINGLE,
                '1'
            )
            ->build();
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
        $this->assertDccModeWithValue(true, 'YES', 'bool true');
    }

    /**
     * @group integration
     * @group hpp
     * @group dcc
     */
    public function testDccModeDisabled(): void
    {
        $this->assertDccModeWithValue(false, 'NO', 'bool false');
    }

    /**
     * Test DCC mode scenarios using mixed input types.
     * @group integration
     * @group hpp
     * @group dcc
     * @group scenarios
     * @dataProvider provideDccModeScenarios
     */
    public function testDccModeScenarios(mixed $value, string $expectedYesNo, string $scenario): void
    {
        $this->assertDccModeWithValue($value, $expectedYesNo, $scenario);
    }

    /**
     * @return array<string, array{0:mixed, 1:string, 2:string}>
     */
    public function provideDccModeScenarios(): array
    {
        return [
            'bool true' => [true, 'YES', 'bool true'],
            'bool false' => [false, 'NO', 'bool false'],
            'string YES' => ['YES', 'YES', 'string YES'],
            'string NO' => ['NO', 'NO', 'string NO'],
            'integer 1' => [1, 'YES', 'integer 1'],
            'integer 0' => [0, 'NO', 'integer 0'],
            'string yes lowercase' => ['yes', 'YES', 'string yes (lowercase)'],
            'string Yes mixedcase' => ['Yes', 'YES', 'string Yes (mixedcase)'],
            'string no lowercase' => ['no', 'NO', 'string no (lowercase)'],
            'string No mixedcase' => ['No', 'NO', 'string No (mixedcase)'],
        ];
    }

    /**
     * Invalid string values must throw ArgumentException at build time.
     * @group integration
     * @group hpp
     * @group dcc
     * @group scenarios-negative
     * @dataProvider provideInvalidDccModeInputs
     */
    public function testDccModeWithInvalidStringThrowsException(string $value): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessageMatches('/Validation failed/');

        $this->createDccHppDataWithValue($value);
    }

    /**
     * @return array<string, array{0:string}>
     */
    public function provideInvalidDccModeInputs(): array
    {
        return [
            'empty string'      => [''],
            'whitespace string' => ['   '],
            'string Y'          => ['Y'],
            'string N'          => ['N'],
            'string ENABLE'     => ['ENABLE'],
            'string DISABLE'    => ['DISABLE'],
            'string 1'          => ['1'],
            'string 0'          => ['0'],
        ];
    }

    /**
     * @group integration
     * @group hpp
     * @group address-match
     */
    public function testAddressMatchIndicatorEnabled(): void
    {
        $this->assertAddressMatchIndicator(true, 'YES');
    }

    /**
     * @group integration
     * @group hpp
     * @group address-match
     */
    public function testAddressMatchIndicatorDisabled(): void
    {
        $this->assertAddressMatchIndicator(false, 'NO');
    }

    /**
     * @group integration
     * @group hpp
     * @group address-match
     * @dataProvider provideAddressMatchIndicatorSerializationCases
     */
    public function testAddressMatchIndicatorSerializationVariants(mixed $input, string $expected): void
    {
        $hppData = $this->createAddressMatchHppDataFromRawValue($input);
        $this->assertSerializedAddressMatchIndicator(
            $hppData,
            $expected,
            'address_match_indicator variant must serialize correctly'
        );
    }

    /**
     * @return array<string, array{0:mixed, 1:string}>
     */
    public function provideAddressMatchIndicatorSerializationCases(): array
    {
        return [
            'bool true' => [true, 'YES'],
            'bool false' => [false, 'NO'],
            'string YES uppercase' => ['YES', 'YES'],
            'string NO uppercase' => ['NO', 'NO'],
            'string yes lowercase' => ['yes', 'YES'],
            'string no lowercase' => ['no', 'NO'],
            'string Yes camelcase' => ['Yes', 'YES'],
            'string No camelcase' => ['No', 'NO'],
            'string yEs mixedcase' => ['yEs', 'YES'],
            'string nO mixedcase' => ['nO', 'NO'],
            'null value not serialized' => [null, self::NOT_SET],
        ];
    }

    /**
     * Verify the mapper's normalization path: 'yes' injected directly (bypassing the builder)
     * must be normalized to 'YES' by the request mapper.
     *
     * @group integration
     * @group hpp
     * @group address-match
     */
    public function testAddressMatchIndicatorDirectStringYesNormalized(): void
    {
        $hppData = $this->createAddressMatchHppData(false);
        $hppData->payer->addressMatchIndicator = 'yes';
        $this->assertSerializedAddressMatchIndicator(
            $hppData,
            'YES',
            'lowercase yes directly injected must be normalized to YES by mapper'
        );
    }

    /**
     * Verify the mapper's normalization path: 'no' injected directly (bypassing the builder)
     * must be normalized to 'NO' by the request mapper.
     *
     * @group integration
     * @group hpp
     * @group address-match
     */
    public function testAddressMatchIndicatorDirectStringNoNormalized(): void
    {
        $hppData = $this->createAddressMatchHppData(true);
        $hppData->payer->addressMatchIndicator = 'no';
        $this->assertSerializedAddressMatchIndicator(
            $hppData,
            'NO',
            'lowercase no directly injected must be normalized to NO by mapper'
        );
    }

    /**
     * @group integration
     * @group hpp
     * @group address-match
     */
    public function testAddressMatchIndicatorEmptyStringThrowsException(): void
    {
        $hppData = $this->createAddressMatchHppData(true);
        $hppData->payer->addressMatchIndicator = '';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessageMatches('/address_match_indicator must not be empty/');

        $this->getDccRequestBody($hppData);
    }

    /**
     * @group integration
     * @group hpp
     * @group address-match
     */
    public function testAddressMatchIndicatorWhitespaceThrowsException(): void
    {
        $hppData = $this->createAddressMatchHppData(true);
        $hppData->payer->addressMatchIndicator = '   ';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessageMatches('/address_match_indicator must not be empty/');

        $this->getDccRequestBody($hppData);
    }

    private function assertDccModeWithValue(mixed $value, string $expectedYesNo, string $scenario): void
    {
        $hppData = $this->createDccHppDataWithValue($value);
        $this->assertSerializedDccMode($hppData, $expectedYesNo, $scenario);

        $response = $this->executeWithDccConfig($hppData);
        $this->assertValidPayByLinkResponse($response);
    }

    private function assertSerializedDccMode(HPPData $hppData, string $expectedYesNo, string $scenario): void
    {
        $requestBody = $this->getDccRequestBody($hppData);
        $serialized = $requestBody['order']['transaction_configuration']['currency_conversion_mode'] ?? self::NOT_SET;

        $this->assertSame(
            $expectedYesNo,
            $serialized,
            "Scenario '{$scenario}' failed: currency_conversion_mode must serialize to {$expectedYesNo}"
        );
    }

    private function executeWithDccConfig(HPPData $hppData): Transaction
    {
        $dccConfig = $this->createDccConfig();
        ServicesContainer::configureService($dccConfig);
        try {
            return HPPService::create($hppData)->execute();
        } finally {
            ServicesContainer::configureService(self::$config);
        }
    }

    private function assertAddressMatchIndicator(bool $indicator, string $expected): void
    {
        $hppData = $this->createAddressMatchHppData($indicator);
        $this->assertSerializedAddressMatchIndicator(
            $hppData,
            $expected,
            'address_match_indicator must serialize correctly'
        );
    }

    private function assertSerializedAddressMatchIndicator(HPPData $hppData, string $expected, string $message): void
    {
        $requestBody = $this->getDccRequestBody($hppData);
        $serialized = $requestBody['payer']['address_match_indicator'] ?? self::NOT_SET;

        $this->assertSame($expected, $serialized, $message);
    }

    private function createDccHppDataWithValue(mixed $value): HPPData
    {
        $reference = 'INT_TEST_DCC_SCENARIO_' . uniqid();

        return HPPBuilder::create()
            ->withName('DCC Scenario Test')
            ->withReference($reference)
            ->withAmount('1000')
            ->withCurrency('USD')
            ->withPayer($this->validPayer)
            ->withTransactionConfig('CNP', 'US', CaptureMode::AUTO)
            ->withCurrencyConversionMode($value)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status'
            )
            ->build();
    }

    private function createAddressMatchHppData(bool $indicator): HPPData
    {
        $reference = $indicator
            ? 'INT_TEST_ADDR_MATCH_ON_' . uniqid()
            : 'INT_TEST_ADDR_MATCH_OFF_' . uniqid();

        return HPPBuilder::create()
            ->withName('Address Match Integration Test')
            ->withDescription('Address match indicator integration serialization test')
            ->withReference($reference)
            ->withAmount('1200')
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
            ->withAddressMatchIndicator($indicator)
            ->build();
    }

    private function createAddressMatchHppDataFromRawValue(mixed $value): HPPData
    {
        if (is_bool($value)) {
            return $this->createAddressMatchHppData($value);
        }

        $hppData = $this->createAddressMatchHppData(false);
        $hppData->payer->addressMatchIndicator = $value;

        return $hppData;
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
        if (!is_object($gpApiRequest) || !property_exists($gpApiRequest, 'requestBody') || !is_array($gpApiRequest->requestBody)) {
            throw new \RuntimeException('Failed to build DCC request payload');
        }

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
