<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Transaction;
use PHPUnit\Framework\TestCase;

class RealexApmTest extends TestCase {

    protected function config() {
        $config = new ServicesConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "hpp";
        $config->rebatePassword = 'refund';
        $config->refundPassword = 'refund';
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        return $config;
    }

    public function setup() {
        ServicesContainer::configure($this->config());
    }

    public function testApmForCharge() {
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
    public function testApmWithoutAmount() {
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
    public function testApmWithoutCurrency() {
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
    public function testApmWithoutReturnUrl() {
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
    public function testApmWithoutstatusUpdateUrl() {
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
    public function testAPMRefundPendingTransaction() {
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

    public function testApmForRefund() {
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
