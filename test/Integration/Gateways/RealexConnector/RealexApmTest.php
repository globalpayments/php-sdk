<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

class RealexApmTest extends TestCase
{

    protected function config()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "api";
        $config->rebatePassword = 'refund';
        $config->refundPassword = 'refund';
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
//        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    public function setup()
    {
        ServicesContainer::configureService($this->config());
    }

    public function testApmForCharge()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::SOFORTUBERWEISUNG);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'DE';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge(10)
                ->withCurrency("EUR")
                ->withDescription('New APM')
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        // get the reponse details to save to the DB for future transaction management requests
        $orderId = $response->orderId;
        $authCode = $response->authorizationCode;
        $paymentsReference = $response->transactionId; // pasref
        $apmResponse = $response->alternativePaymentResponse;

        $this->assertNotNull($response);
        $this->assertEquals("01", $response->responseCode);
        $this->assertNotNull($response->alternativePaymentResponse);
    }
    
    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  amount cannot be null for this transaction type
     */
    public function testApmWithoutAmount()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::SOFORTUBERWEISUNG);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'DE';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge()
                ->withCurrency("EUR")
                ->withDescription('New APM')
                ->execute();
    }
    
    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  currency cannot be null for this transaction type
     */
    public function testApmWithoutCurrency()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::SOFORTUBERWEISUNG);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'DE';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge(10)
                ->withDescription('New APM')
                ->execute();
    }
    
    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  returnUrl cannot be null for this transaction type
     */
    public function testApmWithoutReturnUrl()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::SOFORTUBERWEISUNG);

        $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'DE';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge(1001)
                ->withCurrency("EUR")
                ->withDescription('New APM')
                ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\BuilderException
     * @expectedExceptionMessage  statusUpdateUrl cannot be null for this transaction type
     */
    public function testApmWithoutstatusUpdateUrl()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::SOFORTUBERWEISUNG);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'DE';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge(1001)
                ->withCurrency("EUR")
                ->withDescription('New APM')
                ->execute();
    }

    /**
     * @expectedException \GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage  FAILED
     */
    public function testAPMRefundPendingTransaction()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::TEST_PAY);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'DE';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge(10)
                ->withCurrency("EUR")
                ->withDescription('New APM')
                ->execute();
        
        $this->assertNotEquals(null, $response);
        $this->assertEquals("01", $response->responseCode);

        // send the settle request, we must specify the amount and currency
        $response = $response->refund(10)
                ->withCurrency("EUR")
                ->withAlternativePaymentType(AlternativePaymentType::TEST_PAY)
                ->execute();
    }

    public function testAPMPayByBankApp()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::PAYBYBANKAPP);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'GB';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge(10)
            ->withCurrency("GBP")
            ->withDescription('New APM')
            ->execute();

        $this->assertNotEquals(null, $response);
        $this->assertEquals("01", $response->responseCode);
    }

    public function testAPMPaypal()
    {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::PAYPAL);
        $paymentMethod->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $paymentMethod->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $paymentMethod->cancelUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'US';
        $paymentMethod->accountHolderName = 'James Mason';
        $amount = 10; $currency = 'USD';
        $transaction = $paymentMethod->charge($amount)
            ->withCurrency($currency)
            ->withDescription('New APM')
            ->execute();

        $this->assertNotEquals(null, $transaction);
        $this->assertEquals("00", $transaction->responseCode);
        $this->assertNotNull($transaction->alternativePaymentResponse->sessionToken);

        $redirectUrl = "Open link in browser and confirm PAYPAL payment: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token={$transaction->alternativePaymentResponse->sessionToken}";
        fwrite(STDERR, print_r($redirectUrl, TRUE));
        sleep(30);

        $transaction->alternativePaymentResponse->providerReference = 'SMKGK7K2BLEUA';
        $exceptionCaught = false;
        try {
            $response = $transaction->confirm($amount)
                ->withCurrency($currency)
                ->withAlternativePaymentType(AlternativePaymentType::PAYPAL)
                ->execute();
            $this->assertNotNull($response);
            $this->assertEquals("00", $transaction->responseCode);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Unexpected Gateway Response: 101 - Payment has not been authorized by the user.', $e->getMessage());
        }
    }

    public function testApmForRefund()
    {
        $this->markTestSkipped('You need a valid values for orderId and payment refrence to run this test!');
        // a settle request requires the original order id
        $orderId = "20180912050207-5b989dcfc9433";
        // and the payments reference (pasref) from the authorization response
        $paymentsReference = "15367285279651634";
        // and the auth code transaction response
        $authCode = "12345";

        // create the rebate transaction object
        $transaction = Transaction::fromId($paymentsReference, $orderId);
        $transaction->authorizationCode = $authCode;

        // send the settle request, we must specify the amount and currency
        $response = $transaction->refund(10)
                ->withCurrency("EUR")
                ->withAlternativePaymentType(AlternativePaymentType::TEST_PAY)
                ->execute();

        $responseCode = $response->responseCode; // 00 == Success
        $message = $response->responseMessage; // [ test system ] AUTHORISED
        
        $this->assertNotEquals(null, $response);
        $this->assertEquals("00", $responseCode);
    }
}
