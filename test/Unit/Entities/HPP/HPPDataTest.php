<?php

namespace GlobalPayments\Api\Tests\Unit\Entities\HPP;

use GlobalPayments\Api\Entities\{
    HPPData,
    HPPNotifications,
    HPPOrder,
    HPPPaymentMethodConfiguration,
    HPPTransactionConfiguration,
    PayerDetails
};
use GlobalPayments\Api\Entities\Enums\{HPPFunctions, HPPTypes};
use PHPUnit\Framework\TestCase;

class HPPDataTest extends TestCase
{
    private $hppData;

    public function setUp(): void
    {
        $this->hppData = new HPPData();
    }

    /**
     * Helper method to create a valid payer
     */
    private function createValidPayer(): PayerDetails
    {
        $payer = new PayerDetails();
        $payer->email = 'test@example.com';
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->status = 'NEW';
        return $payer;
    }

    /**
     * Helper method to create a valid order
     */
    private function createValidOrder(): HPPOrder
    {
        $order = new HPPOrder();
        $order->amount = '1000';
        $order->currency = 'USD';
        
        // Add valid transaction configuration
        $transactionConfig = new HPPTransactionConfiguration();
        $transactionConfig->channel = 'CNP';
        $transactionConfig->country = 'US';
        $transactionConfig->captureMode = 'AUTO';
        $transactionConfig->currencyConversionMode = 'YES';
        $transactionConfig->allowedPaymentMethods = ['CARD'];
        $transactionConfig->usageMode = 'SINGLE';
        $transactionConfig->usageLimit = '1';
        $order->HPPTransactionConfiguration = $transactionConfig;
        
        // Add valid payment method configuration
        $paymentMethodConfig = new HPPPaymentMethodConfiguration();
        $order->HPPPaymentMethodConfiguration = $paymentMethodConfig;
        
        return $order;
    }

    /**
     * Helper method to create valid notifications
     */
    private function createValidNotifications(): HPPNotifications
    {
        $notifications = new HPPNotifications();
        $notifications->returnUrl = 'https://example.com/return';
        $notifications->statusUrl = 'https://example.com/status';
        return $notifications;
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(HPPData::class, $this->hppData);
        $this->assertEquals(HPPTypes::HOSTED_PAYMENT_PAGE, $this->hppData->type);
        $this->assertEquals(false, $this->hppData->shippable);
        $this->assertNull($this->hppData->shippingAmount);
    }

    public function testPropertyAssignment()
    {
        $this->hppData->name = 'Test Payment Page';
        $this->hppData->description = 'Test Description';
        $this->hppData->reference = 'TEST-REF-123';
        $this->hppData->expirationDate = '2025-12-31';

        $this->assertEquals('Test Payment Page', $this->hppData->name);
        $this->assertEquals('Test Description', $this->hppData->description);
        $this->assertEquals('TEST-REF-123', $this->hppData->reference);
        $this->assertEquals('2025-12-31', $this->hppData->expirationDate);
    }

    public function testValidateWithMissingPayer()
    {
        $errors = $this->hppData->validate();
        $this->assertContains('Payer details are required', $errors);
    }

    public function testValidateWithMissingOrder()
    {
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertContains('Order details are required', $errors);
    }

    public function testValidateWithMissingNotifications()
    {
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        
        $errors = $this->hppData->validate();
        $this->assertContains('Notifications configuration is required', $errors);
    }

    public function testValidateWithValidType()
    {
        $this->hppData->type = HPPTypes::HOSTED_PAYMENT_PAGE;
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        // Should have no type-related errors
        $errors = $this->hppData->validate();
        $this->assertNotContains('Invalid hosted payment page type', $errors);
    }

    public function testValidateWithInvalidType()
    {
        $this->hppData->type = 'INVALID_TYPE';
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertContains('Invalid hosted payment page type', $errors);
    }

    public function testValidateWithValidFunction()
    {
        $this->hppData->function = HPPFunctions::TRANSACTION_REPORT;
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertNotContains('Invalid hosted payment function', $errors);
    }

    public function testValidateWithInvalidFunction()
    {
        $this->hppData->function = 'INVALID_FUNCTION';
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertContains('Invalid hosted payment function', $errors);
    }

    public function testValidateShippableYesWithValidAmount()
    {
        $this->hppData->shippable = true;
        $this->hppData->shippingAmount = '500';
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertNotContains('Shipping amount must be a valid positive number when shippable is YES', $errors);
    }

    public function testValidateShippableYesWithInvalidAmount()
    {
        $this->hppData->shippable = true;
        $this->hppData->shippingAmount = '-500';
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertContains('Shipping amount must be a valid positive number when shippable is YES', $errors);
    }

    public function testValidateShippableYesWithNonNumericAmount()
    {
        $this->hppData->shippable = true;
        $this->hppData->shippingAmount = 'invalid';
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertContains('Shipping amount must be a valid positive number when shippable is YES', $errors);
    }

    public function testValidateShippableNoWithAmount()
    {
        $this->hppData->shippable = false;
        $this->hppData->shippingAmount = '500'; // Should be ignored when shippable is NO
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        
        $errors = $this->hppData->validate();
        $this->assertNotContains('Shipping amount must be a valid positive number when shippable is YES', $errors);
    }

    public function testIsValidReturnsFalseWhenInvalid()
    {
        // HPPData without required fields should be invalid
        $this->assertFalse($this->hppData->isValid());
    }

    public function testIsValidReturnsTrueWhenValid()
    {
        $this->hppData->payer = $this->createValidPayer();
        $this->hppData->order = $this->createValidOrder();
        $this->hppData->notifications = $this->createValidNotifications();
        $this->hppData->name = 'Valid Payment Page';
        // Ensure all optional fields that might cause validation issues are properly set or left empty
        $this->hppData->function = null; 
        $this->hppData->shippable = false; // Ensure shippable is properly set
        $this->hppData->shippingAmount = null; // No shipping amount when not shippable
        
        $errors = $this->hppData->validate();
        if (!empty($errors)) {
            $this->fail('HPPData validation failed with errors: ' . implode(', ', $errors));
        }
        
        $this->assertTrue($this->hppData->isValid());
    }

    public function testImagesProperty()
    {
        $imageData = ['b64_content' => 'base64encodedstring'];
        $this->hppData->images = $imageData;
        
        $this->assertEquals($imageData, $this->hppData->images);
    }

    public function testDisplayConfigurationProperty()
    {
        $displayConfig = "https://example.com/Iframe_callback.php";
        $this->hppData->HPPDisplayConfiguration = $displayConfig;
        
        $this->assertEquals($displayConfig, $this->hppData->HPPDisplayConfiguration);
    }

    public function testReferrerUrlProperty()
    {
        $referrerUrl = 'https://example.com/checkout';
        $this->hppData->referrerUrl = $referrerUrl;
        
        $this->assertEquals($referrerUrl, $this->hppData->referrerUrl);
    }

    public function testValidShippingAmounts()
    {
        $validAmounts = ['0', '000', '599', '100', '123456'];
        
        foreach ($validAmounts as $amount) {
            $this->hppData->shippable = true;
            $this->hppData->shippingAmount = $amount;
            $this->hppData->payer = $this->createValidPayer();
            $this->hppData->order = $this->createValidOrder();
            $this->hppData->notifications = $this->createValidNotifications();
            
            $errors = $this->hppData->validate();
            $this->assertNotContains('Shipping amount must be a valid positive number when shippable is YES', $errors, "Failed for amount: $amount");
        }
    }

    public function testInvalidShippingAmounts()
    {
        $invalidAmounts = ['-1', '-0.01', 'abc', ''];
        
        foreach ($invalidAmounts as $amount) {
            $this->hppData->shippable = true;
            $this->hppData->shippingAmount = $amount;
            $this->hppData->payer = $this->createValidPayer();
            $this->hppData->order = $this->createValidOrder();
            $this->hppData->notifications = $this->createValidNotifications();
            
            $errors = $this->hppData->validate();
            if ($amount !== '') { 
                $this->assertContains('Shipping amount must be a valid positive number when shippable is YES', $errors, "Should have failed for amount: '$amount'");
            }
        }
    }

    public function testCompleteHPPDataSetup()
    {
        $this->hppData->name = 'Complete Payment Page';
        $this->hppData->description = 'Complete test payment';
        $this->hppData->reference = 'COMPLETE-TEST-123';
        $this->hppData->expirationDate = '2025-12-31';
        $this->hppData->type = HPPTypes::HOSTED_PAYMENT_PAGE;
        $this->hppData->function = HPPFunctions::TRANSACTION_REPORT;
        $this->hppData->shippable = true;
        $this->hppData->shippingAmount = '1000';
        $this->hppData->referrerUrl = 'https://example.com';
        
        $this->assertEquals('Complete Payment Page', $this->hppData->name);
        $this->assertEquals('Complete test payment', $this->hppData->description);
        $this->assertEquals('COMPLETE-TEST-123', $this->hppData->reference);
        $this->assertEquals('2025-12-31', $this->hppData->expirationDate);
        $this->assertEquals(HPPTypes::HOSTED_PAYMENT_PAGE, $this->hppData->type);
        $this->assertEquals(HPPFunctions::TRANSACTION_REPORT, $this->hppData->function);
        $this->assertEquals(true, $this->hppData->shippable);
        $this->assertEquals('1000', $this->hppData->shippingAmount);
        $this->assertEquals('https://example.com', $this->hppData->referrerUrl);
    }
}
