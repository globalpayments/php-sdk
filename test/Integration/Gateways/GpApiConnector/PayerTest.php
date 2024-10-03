<?php

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\BNPLShippingMethod;
use GlobalPayments\Api\Entities\Enums\BNPLType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Product;
use GlobalPayments\Api\PaymentMethods\BNPL;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class PayerTest extends TestCase
{
    private Customer $newCustomer;
    private CreditCardData $card;
    private Address $billingAddress;
    private Address $shippingAddress;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->newCustomer = new Customer();
        $this->newCustomer->key = GenerationUtils::getGuid();
        $this->newCustomer->firstName = "James";
        $this->newCustomer->lastName = "Mason";

        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cvn = "131";
        $this->card->cardHolderName = "James Mason";

        // billing address
        $this->billingAddress = new Address();
        $this->billingAddress->streetAddress1 = '10 Glenlake Pkwy NE';
        $this->billingAddress->streetAddress2 = 'no';
        $this->billingAddress->city = 'Birmingham';
        $this->billingAddress->postalCode = '50001';
        $this->billingAddress->countryCode = 'US';
        $this->billingAddress->state = 'IL';

        // shipping address
        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = 'Apartment 852';
        $this->shippingAddress->streetAddress2 = 'Complex 741';
        $this->shippingAddress->streetAddress3 = 'no';
        $this->shippingAddress->city = 'Birmingham';
        $this->shippingAddress->postalCode = '50001';
        $this->shippingAddress->state = 'IL';
        $this->shippingAddress->countryCode = 'US';
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig(): GpApiConfig
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testCreatePayer()
    {
        $tokenizeResponse = $this->card->tokenize()->execute();

        $this->assertNotNull($tokenizeResponse);
        $this->assertEquals('SUCCESS', $tokenizeResponse->responseCode);
        $this->assertEquals('ACTIVE', $tokenizeResponse->responseMessage);

        $this->card->token = $tokenizeResponse->token;
        $this->newCustomer->addPaymentMethod($tokenizeResponse->token, $this->card);

        $card2 = new CreditCardData();
        $card2->number = "4012001038488884";
        $card2->expMonth = date('m');
        $card2->expYear = date('Y', strtotime('+1 year'));
        $card2->cvn = "131";
        $card2->cardHolderName = "James Mason";

        $tokenize2 = $card2->tokenize()->execute();
        $card2->token = $tokenize2->token;

        $this->assertNotNull($tokenize2);
        $this->assertEquals('SUCCESS', $tokenize2->responseCode);
        $this->assertEquals('ACTIVE', $tokenize2->responseMessage);

        $this->newCustomer->addPaymentMethod($card2->token, $card2);

        /** @var \GlobalPayments\Api\Entities\Customer $payer */
        $payer = $this->newCustomer->create();

        $this->assertNotNull($payer->id);
        $this->assertEquals($this->newCustomer->firstName, $payer->firstName);
        $this->assertEquals($this->newCustomer->lastName, $payer->lastName);
        $this->assertNotEmpty($payer->paymentMethods);
        foreach ($payer->paymentMethods as $paymentMethod) {
            $this->assertContains($paymentMethod->id, [$card2->token, $this->card->token]);
        }
    }

    public function testCreatePayer_WithoutPaymentMethods()
    {
        /** @var \GlobalPayments\Api\Entities\Customer $payer */
        $payer = $this->newCustomer->create();

        $this->assertNotNull($payer->id);
        $this->assertEquals($this->newCustomer->firstName, $payer->firstName);
        $this->assertEquals($this->newCustomer->lastName, $payer->lastName);
        $this->assertEmpty($payer->paymentMethods);
    }

    public function testCreatePayer_WithoutFirstName()
    {
        $this->newCustomer = new Customer();
        $this->newCustomer->key = GenerationUtils::getGuid();
        $this->newCustomer->lastName = "Mason";

        $exceptionCaught = false;
        try {
            $this->newCustomer->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: first_name', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreatePayer_WithoutLastName()
    {
        $this->newCustomer = new Customer();
        $this->newCustomer->key = GenerationUtils::getGuid();
        $this->newCustomer->firstName = "James";

        $exceptionCaught = false;
        try {
            $this->newCustomer->create();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields: last_name', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayer()
    {
        $this->newCustomer->id = 'PYR_b2d3b367fcf141dcbd03cd9ccfa60519';

        $tokenizeResponse = $this->card->tokenize()->execute();

        $this->assertNotNull($tokenizeResponse);
        $this->assertEquals('SUCCESS', $tokenizeResponse->responseCode);
        $this->assertEquals('ACTIVE', $tokenizeResponse->responseMessage);

        $this->card->token = $tokenizeResponse->token;
        $this->newCustomer->addPaymentMethod($tokenizeResponse->token, $this->card);
        /** @var Customer $payer */
        $payer = $this->newCustomer->saveChanges();

        $this->assertEquals($this->newCustomer->key, $payer->key);

        $this->assertNotEmpty($payer->paymentMethods);
        $this->assertEquals($this->card->token, reset($payer->paymentMethods)->id);
    }

    public function testEditPayer_WithoutCustomerId()
    {
        $this->newCustomer->key = 'payer-123';

        $exceptionCaught = false;
        try {
            $this->newCustomer->saveChanges();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testEditPayer_RandomCustomerId()
    {
        $this->newCustomer->id = 'PYR_' . GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            $this->newCustomer->saveChanges();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: RESOURCE_NOT_FOUND - Payer ' . $this->newCustomer->id . ' not found at this location', $e->getMessage());
            $this->assertEquals('40008', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardTokenization()
    {
        $payer = $this->newCustomer->create();

        $response = $this->card->tokenize()
            ->withCustomerId($payer->id)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);
    }

    public function testBNPLInitiateStep()
    {
        $this->newCustomer->email = 'james@example.com';
        $this->newCustomer->phone = new PhoneNumber('41', '57774873', PhoneNumberType::HOME);
        $this->newCustomer->key = '12345678';
        $payer = $this->newCustomer->create();

        $product = new Product();
        $product->productId = GenerationUtils::getGuid();
        $product->productName = 'iPhone 13';
        $product->description = 'iPhone 13';
        $product->quantity = 1;
        $product->unitPrice = 550;
        $product->netUnitPrice = 550;
        $product->taxAmount = 0;
        $product->discountAmount = 0;
        $product->taxPercentage = 0;
        $product->url = "https://www.example.com/iphone.html";
        $product->imageUrl = "https://www.example.com/iphone.png";

        $paymentMethod = new BNPL(BNPLType::AFFIRM);
        $paymentMethod->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $paymentMethod->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $paymentMethod->cancelUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';

        $transaction = $paymentMethod->authorize(5.6)
            ->withCurrency('USD')
            ->withProductData([$product])
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withAddress($this->billingAddress)
            ->withPhoneNumber('41', '57774873', PhoneNumberType::SHIPPING)
            ->withCustomerData($payer)
            ->withBNPLShippingMethod(BNPLShippingMethod::DELIVERY)
            ->withOrderId('12365')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $transaction->responseMessage);
        $this->assertNotNull($transaction->bnplResponse->redirectUrl);
    }
}