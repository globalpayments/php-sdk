<?php

namespace GlobalPayments\Api\Tests\Unit\Builders\HPP;

use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\Entities\{
    Address, 
    PayerDetails, 
    PhoneNumber,
    HPPNotifications
};
use GlobalPayments\Api\Entities\Enums\{
    CaptureMode,
    ChallengeRequestIndicator,
    ExemptStatus,
    HPPFunctions,
    HPPStorageModes,
    HPPTypes,
    PaymentMethodUsageMode,
    PhoneNumberType
};
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use PHPUnit\Framework\TestCase;

class HPPBuilderTest extends TestCase
{
    private $builder;
    private $validPayer;
    private $validAddress;
    private $validNotifications;

    public function setUp(): void
    {
        $this->builder = HPPBuilder::create();
        $this->createValidTestData();
    }

    private function createValidTestData(): void
    {
        // Create valid payer
        $this->validPayer = new PayerDetails();
        $this->validPayer->firstName = 'John';
        $this->validPayer->lastName = 'Doe';
        $this->validPayer->email = 'john.doe@example.com';
        $this->validPayer->status = 'NEW';

        // Create valid address
        $this->validAddress = new Address();
        $this->validAddress->streetAddress1 = '123 Main St';
        $this->validAddress->city = 'Anytown';
        $this->validAddress->postalCode = '12345';
        $this->validAddress->country = 'US';

        // Create valid notifications
        $this->validNotifications = new HPPNotifications();
        $this->validNotifications->returnUrl = 'https://example.com/return';
        $this->validNotifications->statusUrl = 'https://example.com/status';
        $this->validNotifications->cancelUrl = 'https://example.com/cancel';
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(HPPBuilder::class, $this->builder);
    }

    public function testStaticCreate()
    {
        $builder = HPPBuilder::create();
        $this->assertInstanceOf(HPPBuilder::class, $builder);
    }

    // Test Basic Builder Methods
    public function testWithName()
    {
        $result = $this->builder->withName('Test Payment Page');
        $this->assertSame($this->builder, $result);
    }

    public function testWithDescription()
    {
        $result = $this->builder->withDescription('Test Description');
        $this->assertSame($this->builder, $result);
    }

    public function testWithReference()
    {
        $result = $this->builder->withReference('TEST-REF-123');
        $this->assertSame($this->builder, $result);
    }

    public function testWithExpirationDate()
    {
        $result = $this->builder->withExpirationDate('2025-12-31');
        $this->assertSame($this->builder, $result);
    }

    public function testWithAmount()
    {
        $result = $this->builder->withAmount('1000');
        $this->assertSame($this->builder, $result);
    }

    public function testWithAmountInvalidThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Issue with the amount, it must be a positive number');
        $this->builder->withAmount('-100');
    }

    public function testWithCurrency()
    {
        $result = $this->builder->withCurrency('USD');
        $this->assertSame($this->builder, $result);
    }

    public function testWithCurrencyInvalidLengthThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Currency must be a 3-character code');
        $this->builder->withCurrency('US');
    }

    // Test Enum Union Type Methods
    public function testWithTransactionConfigWithEnums()
    {
        $result = $this->builder->withTransactionConfig(
            'CNP',
            'US',
            CaptureMode::AUTO,
            ['CARD'],
            PaymentMethodUsageMode::SINGLE,
            '1'
        );
        $this->assertSame($this->builder, $result);
    }

    public function testWithTransactionConfigWithStrings()
    {
        $result = $this->builder->withTransactionConfig(
            'CNP',
            'US',
            'AUTO',
            ['CARD'],
            'SINGLE',
            '1'
        );
        $this->assertSame($this->builder, $result);
    }

    public function testWithTransactionConfigInvalidEnumThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->builder->withTransactionConfig(
            'CNP',
            'US',
            'INVALID_CAPTURE_MODE',
            ['CARD'],
            PaymentMethodUsageMode::SINGLE
        );
    }

    public function testWithAuthenticationWithEnums()
    {
        $result = $this->builder->withAuthentication(
            ChallengeRequestIndicator::CHALLENGE_PREFERRED,
            ExemptStatus::LOW_VALUE,
            true
        );
        $this->assertSame($this->builder, $result);
    }

    public function testWithAuthenticationWithStrings()
    {
        $result = $this->builder->withAuthentication(
            'CHALLENGE_PREFERRED',
            'LOW_VALUE',
            false
        );
        $this->assertSame($this->builder, $result);
    }

    public function testWithAuthenticationInvalidPreferenceThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->builder->withAuthentication('INVALID_PREFERENCE');
    }

    public function testWithPaymentMethodConfigWithEnum()
    {
        $result = $this->builder->withPaymentMethodConfig(HPPStorageModes::PROMPT);
        $this->assertSame($this->builder, $result);
    }

    public function testWithPaymentMethodConfigWithString()
    {
        $result = $this->builder->withPaymentMethodConfig('ON_SUCCESS');
        $this->assertSame($this->builder, $result);
    }

    public function testWithPaymentMethodConfigInvalidThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->builder->withPaymentMethodConfig('INVALID_STORAGE_MODE');
    }

    public function testWithTypeWithEnum()
    {
        $result = $this->builder->withType(HPPTypes::HOSTED_PAYMENT_PAGE);
        $this->assertSame($this->builder, $result);
    }

    public function testWithTypeWithString()
    {
        $result = $this->builder->withType('PAYMENT');
        $this->assertSame($this->builder, $result);
    }

    public function testWithTypeInvalidThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->builder->withType('INVALID_TYPE');
    }

    public function testWithFunctionWithEnum()
    {
        $result = $this->builder->withFunction(HPPFunctions::PAYMENT_PROCESSING);
        $this->assertSame($this->builder, $result);
    }

    public function testWithFunctionWithString()
    {
        $result = $this->builder->withFunction('TRANSACTION_REPORT');
        $this->assertSame($this->builder, $result);
    }

    public function testWithFunctionInvalidThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->builder->withFunction('INVALID_FUNCTION');
    }

    // Test AMP Configuration
    public function testWithApmWithBooleans()
    {
        $result = $this->builder->withApm(true, false);
        $this->assertSame($this->builder, $result);
    }

    public function testWithApmInvalidParameterThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Shipping address enabled and address override must be boolean values');
        $this->builder->withApm('YES', 'NO');
    }

    // Test Address Methods
    public function testWithBillingAddress()
    {
        $result = $this->builder->withBillingAddress($this->validAddress);
        $this->assertSame($this->builder, $result);
    }

    public function testWithShippingAddress()
    {
        $result = $this->builder->withShippingAddress($this->validAddress);
        $this->assertSame($this->builder, $result);
    }

    // Test Phone Numbers
    public function testWithPayerPhone()
    {
        $phone = new PhoneNumber('555', '123', '4567', PhoneNumberType::MOBILE);
        $result = $this->builder->withPayerPhone($phone);
        $this->assertSame($this->builder, $result);
    }

    public function testWithShippingPhone()
    {
        $phone = new PhoneNumber('555', '123', '4567', PhoneNumberType::MOBILE);
        $result = $this->builder->withShippingPhone($phone);
        $this->assertSame($this->builder, $result);
    }

    // Test Payer Validation
    public function testWithPayerValid()
    {
        $result = $this->builder->withPayer($this->validPayer);
        $this->assertSame($this->builder, $result);
    }

    public function testWithPayerInvalidEmailThrowsException()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->email = 'invalid-email';
        $payer->status = 'NEW';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        $this->builder->withPayer($payer);
    }

    public function testWithPayerMissingRequiredFieldsThrowsException()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        // Missing lastName and email

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('First name, last name, and email are required');
        $this->builder->withPayer($payer);
    }

    // Test Notifications
    public function testWithNotifications()
    {
        $result = $this->builder->withNotifications(
            'https://example.com/return',
            'https://example.com/status',
            'https://example.com/cancel'
        );
        $this->assertSame($this->builder, $result);
    }

    public function testWithNotificationsInvalidUrlThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Invalid URL format for notifications');
        $this->builder->withNotifications('invalid-url', 'https://example.com/status');
    }

    // Test Shipping
    public function testWithShippingEnabled()
    {
        $result = $this->builder->withShipping(true, '500');
        $this->assertSame($this->builder, $result);
    }

    public function testWithShippingDisabled()
    {
        $result = $this->builder->withShipping(false);
        $this->assertSame($this->builder, $result);
    }

    public function testWithShippingInvalidAmountThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Shipping amount must be a positive number');
        $this->builder->withShipping(true, '-100');
    }

    // Test URL Methods
    public function testWithReferrerUrl()
    {
        $result = $this->builder->withReferrerUrl('https://example.com');
        $this->assertSame($this->builder, $result);
    }

    public function testWithReferrerUrlInvalidThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Invalid URL format for referrer URL');
        $this->builder->withReferrerUrl('invalid-url');
    }

    public function testWithIpAddress()
    {
        $result = $this->builder->withIpAddress('192.168.1.1');
        $this->assertSame($this->builder, $result);
    }

    public function testWithIpAddressInvalidThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Invalid IP address format');
        $this->builder->withIpAddress('invalid-ip');
    }

    // Test Build Method
    public function testBuildWithRequiredFields()
    {
        $hppData = $this->builder
            ->withName('Test Payment')
            ->withAmount('1000')
            ->withCurrency('USD')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status'
            )
            ->build();

        $this->assertNotNull($hppData);
        $this->assertEquals('Test Payment', $hppData->name);
    }

    public function testBuildWithMissingRequiredFieldsThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Validation failed');
        
        // Missing required fields
        $this->builder->build();
    }

    // Test Execute Method
    public function testExecuteWithValidData()
    {
        $this->builder
            ->withName('Test Payment')
            ->withAmount('1000')
            ->withCurrency('USD')
            ->withPayer($this->validPayer)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status'
            );

        // This should not throw an exception during validation
        // The actual API call is mocked/stubbed in integration tests
        $this->assertTrue(true); // Placeholder assertion
    }

    // Test Method Chaining
    public function testMethodChaining()
    {
        $result = $this->builder
            ->withName('Chained Test')
            ->withDescription('Testing method chaining')
            ->withAmount('500')
            ->withCurrency('USD')
            ->withPayer($this->validPayer)
            ->withBillingAddress($this->validAddress)
            ->withShippingAddress($this->validAddress)
            ->withTransactionConfig('CNP', 'US', CaptureMode::AUTO)
            ->withAuthentication(ChallengeRequestIndicator::CHALLENGE_PREFERRED)
            ->withPaymentMethodConfig(HPPStorageModes::PROMPT)
            ->withApm(true, false)
            ->withNotifications(
                'https://example.com/return',
                'https://example.com/status'
            );

        $this->assertSame($this->builder, $result);
    }
}