<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\OrderDetails;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use PHPUnit\Framework\TestCase;

class GpApiApmTest extends TestCase
{
    private $paymentMethod;
    private $currency;
    private $shippingAddress;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::PAYPAL);

        $this->paymentMethod->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->cancelUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $this->paymentMethod->descriptor = 'Test Transaction';
        $this->paymentMethod->country = 'GB';
        $this->paymentMethod->accountHolderName = 'James Mason';

        $this->currency = 'USD';

        // shipping address
        $this->shippingAddress = new Address();
        $this->shippingAddress->streetAddress1 = 'Apartment 852';
        $this->shippingAddress->streetAddress2 = 'Complex 741';
        $this->shippingAddress->streetAddress3 = 'no';
        $this->shippingAddress->city = 'Chicago';
        $this->shippingAddress->postalCode = '5001';
        $this->shippingAddress->state = 'IL';
        $this->shippingAddress->countryCode = 'US';
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    /**
     * How to have a success running test. When you will run the test in the console it will be printed the
     * paypal redirect url. You need to copy the link and open it in a browser, do the login wih your paypal
     * credentials and authorize the payment in the paypal form. You will be redirected to a blank page with a
     * printed message like this: { "success": true }. This has to be done within a 25 seconds timeframe.
     * In case you need more time update the sleep() to what you need.
     */
    public function testPayPalCharge_fullCycle()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $response = $this->paymentMethod->charge(1.34)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);

        fwrite(STDERR, print_r($response->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new \DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($response->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var \GlobalPayments\Api\Entities\Reporting\TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testPayPalCapture_fullCycle()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $response = $this->paymentMethod->authorize(1.34)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);

        fwrite(STDERR, print_r($response->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new \DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($response->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var \GlobalPayments\Api\Entities\Reporting\TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertNotEmpty($transactionSummary->transactionId);
        $this->assertNotNull($transactionSummary->transactionId);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;
        $response = $transaction->confirm()->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $response->responseMessage);

        $capture = $transaction->capture()->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);
    }

    public function testPayPalFullCycle_Refund()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $trn = $this->paymentMethod->charge(1.22)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals('SUCCESS', $trn->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $trn->responseMessage);

        fwrite(STDERR, print_r($trn->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new \DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($trn->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var \GlobalPayments\Api\Entities\Reporting\TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        /** @var Transaction $trnRefund */
        $trnRefund = $transaction->refund()->withCurrency($this->currency)->execute();
        $this->assertNotNull($trnRefund);
        $this->assertEquals('SUCCESS', $trnRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trnRefund->responseMessage);

    }

    public function testPayPalFullCycle_Reverse()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $trn = $this->paymentMethod->charge(1.22)
            ->withCurrency($this->currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals('SUCCESS', $trn->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $trn->responseMessage);

        fwrite(STDERR, print_r($trn->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new \DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($trn->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var \GlobalPayments\Api\Entities\Reporting\TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);

        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        $trnReverse = $transaction->reverse()->withCurrency($this->currency)->execute();

        $this->assertNotNull($trnReverse);
        $this->assertEquals('SUCCESS', $trnReverse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $trnReverse->responseMessage);
    }

    public function testPayPalMultiCapture_fullCycle()
    {
        $this->markTestSkipped('To run this test you need to login to your Paypal account and access the link printed and continue the transaction');
        $response = $this->paymentMethod->authorize(3)
            ->withCurrency($this->currency)
            ->withMultiCapture(true)
            ->withDescription('PayPal Multicapture')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);

        fwrite(STDERR, print_r($response->alternativePaymentResponse->redirectUrl, TRUE));

        sleep(25);
        $startDate = new \DateTime();
        $response = ReportingService::findTransactionsPaged(1, 1)
            ->withTransactionId($response->transactionId)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var TransactionSummary $transactionSummary */
        $transactionSummary = reset($response->result);
        $this->assertTrue($transactionSummary->alternativePaymentResponse instanceof AlternativePaymentResponse);
        $this->assertEquals(AlternativePaymentType::PAYPAL, $transactionSummary->alternativePaymentResponse->providerName);
        $this->assertEquals(TransactionStatus::PENDING, $transactionSummary->transactionStatus);
        $this->assertNotNull($transactionSummary->alternativePaymentResponse->providerReference);

        $transaction = Transaction::fromId($transactionSummary->transactionId, null, PaymentMethodType::APM);
        $transaction->alternativePaymentResponse = $transactionSummary->alternativePaymentResponse;

        $response = $transaction->confirm()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $response->responseMessage);

        $capture = $transaction->capture(1)->execute();
        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);

        $capture2 = $transaction->capture(2)->execute();
        $this->assertNotNull($capture2);
        $this->assertEquals('SUCCESS', $capture2->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture2->responseMessage);
    }

    /** unit_amount is actually the total amount for the item; waiting info about the shipping_discount */
    public function testPayPalChargeWithoutConfirm()
    {
        $products = [
            [
                'reference' => 'SKU251584',
                'label' => 'Magazine Subscription',
                'description' => 'Product description 1',
                'quantity' => '1',
                'unit_amount' => '7',
                'unit_currency' => $this->currency,
                'tax_amount' => '0.5'
            ],
            [
                'reference' => 'SKU8884784',
                'label' => 'Charger',
                'description' => 'Product description 2',
                'quantity' => '2',
                'unit_amount' => '6',
                'unit_currency' => $this->currency,
                'tax_amount' => '0.5'
            ]
        ];
        $order = new OrderDetails;
        $order->insuranceAmount = 10;
        $order->handlingAmount = 2;
        $order->hasInsurance = true;
        $order->description = 'Order description';

        $response = $this->paymentMethod->charge(29)
            ->withCurrency($this->currency)
            ->withDescription('New APM Uplift')
            ->withAddress($this->shippingAddress, AddressType::SHIPPING)
            ->withCustomerId('REF123456789')
            ->withProductData($products)
            ->withPhoneNumber('44', '124 445 556', PhoneNumberType::WORK)
            ->withPhoneNumber('44', '124 444 333', PhoneNumberType::HOME)
            ->withPhoneNumber('1', '258 3697 144', PhoneNumberType::SHIPPING)
            ->withOrderId('124214-214221')
            ->withShippingAmount(3)
//            ->withShippingDiscount(1)
            ->withOrderDetails($order)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::INITIATED, $response->responseMessage);
    }
}