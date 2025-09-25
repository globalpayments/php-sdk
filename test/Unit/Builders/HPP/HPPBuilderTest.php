<?php

namespace GlobalPayments\Api\Tests\Unit\Builders\HPP;

use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\Entities\{Address, PayerDetails, PhoneNumber};
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use PHPUnit\Framework\TestCase;

class HPPBuilderTest extends TestCase
{
    private $builder;

    public function setUp(): void
    {
        $this->builder = new HPPBuilder();
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

    public function testWithName()
    {
        $name = 'Test Payment Page';
        $result = $this->builder->withName($name);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithDescription()
    {
        $description = 'Test Description';
        $result = $this->builder->withDescription($description);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithReference()
    {
        $reference = 'TEST-REF-123';
        $result = $this->builder->withReference($reference);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithExpirationDate()
    {
        $expirationDate = '2025-12-31';
        $result = $this->builder->withExpirationDate($expirationDate);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithImageValidBase64()
    {
        $validBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';
        $result = $this->builder->withImage($validBase64);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithImageEmptyContentThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Base64 image content is required');
        
        $this->builder->withImage('');
    }

    public function testWithImageInvalidBase64ThrowsException()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Invalid Base64 format');
        
        $this->builder->withImage('invalid-base64-@#$%');
    }

    public function testWithPayerValidPayer()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->email = 'john.doe@example.com';
        $payer->status = 'NEW';

        $result = $this->builder->withPayer($payer);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithPayerMissingFirstNameThrowsException()
    {
        $payer = new PayerDetails();
        $payer->lastName = 'Doe';
        $payer->email = 'john.doe@example.com';
        $payer->status = 'NEW';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('First name, last name, and email are required');
        
        $this->builder->withPayer($payer);
    }

    public function testWithPayerMissingLastNameThrowsException()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->email = 'john.doe@example.com';
        $payer->status = 'NEW';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('First name, last name, and email are required');
        
        $this->builder->withPayer($payer);
    }

    public function testWithPayerMissingEmailThrowsException()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->status = 'NEW';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('First name, last name, and email are required');
        
        $this->builder->withPayer($payer);
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

    public function testWithPayerInvalidStatusThrowsException()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->email = 'john.doe@example.com';
        $payer->status = 'INVALID_STATUS';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Payer status must be either "NEW" or "ACTIVE"');
        
        $this->builder->withPayer($payer);
    }

    public function testWithPayerNewStatusWithIdThrowsException()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->email = 'john.doe@example.com';
        $payer->status = 'NEW';
        $payer->id = 'some-id';

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Payer ID should not be provided when status is "NEW"');
        
        $this->builder->withPayer($payer);
    }

    public function testWithPayerActiveStatusWithId()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->email = 'john.doe@example.com';
        $payer->status = 'ACTIVE';
        $payer->id = 'existing-payer-id';

        $result = $this->builder->withPayer($payer);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithPayerPhone()
    {
        $phone = new PhoneNumber("44", "07900000000", PhoneNumberType::MOBILE);
        $result = $this->builder->withPayerPhone($phone);
        
        $this->assertSame($this->builder, $result);
    }

    public function testWithBillingAddress()
    {
        $address = new Address();
        $address->streetAddress1 = '123 Main St';
        $address->city = 'New York';
        $address->state = 'NY';
        $address->postalCode = '10001';
        $address->country = 'US';

        $result = $this->builder->withBillingAddress($address);
        
        $this->assertSame($this->builder, $result);
    }

    public function testFluentInterface()
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->email = 'john.doe@example.com';
        $payer->status = 'NEW';

        $address = new Address();
        $address->streetAddress1 = '123 Main St';
        $address->city = 'New York';

        $phone = new PhoneNumber("44", "07900000000", PhoneNumberType::MOBILE);
        $phone->number = '5551234567';

        $result = $this->builder
            ->withName('Test Page')
            ->withDescription('Test Description')
            ->withReference('REF-123')
            ->withExpirationDate('2025-12-31')
            ->withPayer($payer)
            ->withBillingAddress($address)
            ->withPayerPhone($phone);

        $this->assertSame($this->builder, $result);
    }

    public function testValidBase64Formats()
    {
        $validBase64Examples = [
            'VGVzdA==',  // Simple base64
            'aGVsbG8=',  // Another simple base64
            'dGVzdGluZw==',  // Testing
            '',  
        ];

        foreach ($validBase64Examples as $index => $base64) {
            if ($index === 3) continue; // Skip empty string

            $builder = new HPPBuilder();
            $result = $builder->withImage($base64);
            $this->assertSame($builder, $result, "Failed for base64: $base64");
        }
    }

    public function testPayerStatusValidation()
    {
        $validStatuses = ['NEW', 'ACTIVE'];
        
        foreach ($validStatuses as $status) {
            $payer = new PayerDetails();
            $payer->firstName = 'John';
            $payer->lastName = 'Doe';
            $payer->email = 'john.doe@example.com';
            $payer->status = $status;
            
            if ($status === 'ACTIVE') {
                $payer->id = 'test-id';
            }

            $builder = new HPPBuilder();
            $result = $builder->withPayer($payer);
            $this->assertSame($builder, $result, "Failed for status: $status");
        }
    }
}
