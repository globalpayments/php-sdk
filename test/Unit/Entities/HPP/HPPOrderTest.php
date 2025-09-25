<?php

namespace GlobalPayments\Api\Tests\Unit\Entities\HPP;

use GlobalPayments\Api\Entities\{
    Address,
    HPPOrder,
    HPPPaymentMethodConfiguration,
    HPPTransactionConfiguration,
    PhoneNumber
};
use PHPUnit\Framework\TestCase;

class HPPOrderTest extends TestCase
{
    private $order;

    public function setUp(): void
    {
        $this->order = new HPPOrder();
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(HPPOrder::class, $this->order);
        $this->assertNull($this->order->amount);
        $this->assertNull($this->order->currency);
        $this->assertNull($this->order->reference);
        $this->assertNull($this->order->HPPTransactionConfiguration);
        $this->assertNull($this->order->HPPPaymentMethodConfiguration);
        $this->assertNull($this->order->shippingAddress);
        $this->assertNull($this->order->shippingPhone);
    }

    public function testValidateWithValidData()
    {
        $this->order->amount = '1000'; // $10.00
        $this->order->currency = 'USD';
        
        $errors = $this->order->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithMissingAmount()
    {
        $this->order->currency = 'USD';
        
        $errors = $this->order->validate();
        $this->assertContains('Amount is required for hosted payment order', $errors);
    }

    public function testValidateWithEmptyAmount()
    {
        $this->order->amount = '';
        $this->order->currency = 'USD';
        
        $errors = $this->order->validate();
        $this->assertContains('Amount is required for hosted payment order', $errors);
    }

    public function testValidateWithInvalidAmountNonNumeric()
    {
        $this->order->amount = 'invalid';
        $this->order->currency = 'USD';
        
        $errors = $this->order->validate();
        $this->assertContains('Amount must be a positive number', $errors);
    }

    public function testValidateWithInvalidAmountNegative()
    {
        $this->order->amount = '-100';
        $this->order->currency = 'USD';
        
        $errors = $this->order->validate();
        $this->assertContains('Amount must be a positive number', $errors);
    }

    public function testValidateWithInvalidAmountZero()
    {
        $this->order->amount = '0';
        $this->order->currency = 'USD';
        
        $errors = $this->order->validate();
        $this->assertContains('Amount must be a positive number', $errors);
    }

    public function testValidateWithMissingCurrency()
    {
        $this->order->amount = '1000';
        
        $errors = $this->order->validate();
        $this->assertContains('Currency is required for hosted payment order', $errors);
    }

    public function testValidateWithEmptyCurrency()
    {
        $this->order->amount = '1000';
        $this->order->currency = '';
        
        $errors = $this->order->validate();
        $this->assertContains('Currency is required for hosted payment order', $errors);
    }

    public function testValidateWithInvalidCurrencyLength()
    {
        $this->order->amount = '1000';
        $this->order->currency = 'US'; // Too short
        
        $errors = $this->order->validate();
        $this->assertContains('Currency must be a 3-character code', $errors);

        $this->order->currency = 'USDX'; // Too long
        
        $errors = $this->order->validate();
        $this->assertContains('Currency must be a 3-character code', $errors);
    }

    public function testValidateWithBothAmountAndCurrencyMissing()
    {
        $errors = $this->order->validate();
        $this->assertContains('Amount is required for hosted payment order', $errors);
        $this->assertContains('Currency is required for hosted payment order', $errors);
        $this->assertCount(2, $errors);
    }

    public function testValidateWithValidTransactionConfiguration()
    {
        $this->order->amount = '1000';
        $this->order->currency = 'USD';
        $this->order->HPPTransactionConfiguration = new HPPTransactionConfiguration();

		$this->order->HPPTransactionConfiguration->channel = 'CNP';
		$this->order->HPPTransactionConfiguration->country = 'US';
		$this->order->HPPTransactionConfiguration->captureMode = 'AUTO';
		$this->order->HPPTransactionConfiguration->currencyConversionMode = 'YES';
		$this->order->HPPTransactionConfiguration->allowedPaymentMethods = ['CARD', 'BANK_PAYMENT'];
		$this->order->HPPTransactionConfiguration->usageMode = 'SINGLE';
		$this->order->HPPTransactionConfiguration->usageLimit = '1';

        $errors = $this->order->validate();
        $this->assertEmpty($errors);
    }

    public function testValidateWithValidPaymentMethodConfiguration()
    {
        $this->order->amount = '1000';
        $this->order->currency = 'USD';
        $this->order->HPPPaymentMethodConfiguration = new HPPPaymentMethodConfiguration();
        
        $errors = $this->order->validate();
        $this->assertEmpty($errors);
    }

    public function testPropertyAssignment()
    {
        $amount = '2500';
        $currency = 'EUR';
        $reference = 'ORDER-123';
        
        $transactionConfig = new HPPTransactionConfiguration();
        $paymentMethodConfig = new HPPPaymentMethodConfiguration();
        $shippingAddress = new Address();
        $shippingPhone = new PhoneNumber("44", "07900000000", "MOBILE");
        
        $this->order->amount = $amount;
        $this->order->currency = $currency;
        $this->order->reference = $reference;
        $this->order->HPPTransactionConfiguration = $transactionConfig;
        $this->order->HPPPaymentMethodConfiguration = $paymentMethodConfig;
        $this->order->shippingAddress = $shippingAddress;
        $this->order->shippingPhone = $shippingPhone;
        
        $this->assertEquals($amount, $this->order->amount);
        $this->assertEquals($currency, $this->order->currency);
        $this->assertEquals($reference, $this->order->reference);
        $this->assertEquals($transactionConfig, $this->order->HPPTransactionConfiguration);
        $this->assertEquals($paymentMethodConfig, $this->order->HPPPaymentMethodConfiguration);
        $this->assertEquals($shippingAddress, $this->order->shippingAddress);
        $this->assertEquals($shippingPhone, $this->order->shippingPhone);
    }

    public function testValidCurrencyCodes()
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY'];
        
        foreach ($validCurrencies as $currency) {
            $this->order->amount = '1000';
            $this->order->currency = $currency;
            
            $errors = $this->order->validate();
            $this->assertEmpty($errors, "Failed for currency: $currency");
        }
    }

    public function testValidAmountFormats()
    {
        $validAmounts = ['100', '1000', '9999', '001', '123456789'];
        
        foreach ($validAmounts as $amount) {
            $this->order->amount = $amount;
            $this->order->currency = 'USD';
            
            $errors = $this->order->validate();
            $this->assertEmpty($errors, "Failed for amount: $amount");
        }
    }

    public function testInvalidAmountFormats()
    {
        $invalidAmounts = ['abc', '0', '-1', '-100', '', ' '];
        
        foreach ($invalidAmounts as $amount) {
            $this->order->amount = $amount;
            $this->order->currency = 'USD';
            
            $errors = $this->order->validate();
            $this->assertNotEmpty($errors, "Should have failed for amount: '$amount'");
        }
    }

    public function testCompleteValidOrder()
    {
        $this->order->amount = '2500';
        $this->order->currency = 'USD';
        $this->order->reference = 'ORDER-REF-123';
        
        $transactionConfig = new HPPTransactionConfiguration();
        $transactionConfig->channel = 'CNP';
        $transactionConfig->country = 'US';
        $transactionConfig->captureMode = 'AUTO';
        $transactionConfig->currencyConversionMode = 'YES';
        $transactionConfig->allowedPaymentMethods = ['CARD', 'BANK_PAYMENT'];
        $transactionConfig->usageMode = 'SINGLE';
        $transactionConfig->usageLimit = '1';
        
        $paymentMethodConfig = new HPPPaymentMethodConfiguration();
        
        $this->order->HPPTransactionConfiguration = $transactionConfig;
        $this->order->HPPPaymentMethodConfiguration = $paymentMethodConfig;
        
        $shippingAddress = new Address();
        $shippingAddress->streetAddress1 = '123 Shipping St';
        $shippingAddress->city = 'Shipping City';
        
        $shippingPhone = new PhoneNumber("44", "07900000000", "MOBILE");
        
        $this->order->shippingAddress = $shippingAddress;
        $this->order->shippingPhone = $shippingPhone;
        
        $errors = $this->order->validate();
        $this->assertEmpty($errors);
    }
}
