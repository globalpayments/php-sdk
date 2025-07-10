<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector;

use GlobalPayments\Api\Entities\Enums\{
    Channel,
    AlternativePaymentType
};
use GlobalPayments\Api\Utils\Logging\{
    Logger,
    SampleRequestLogger,
    RequestConsoleLogger
}; 
use GlobalPayments\Api\Entities\Exceptions\{
    GatewayException,
    BuilderException
};
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServiceConfigs\Configuration;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;

use PHPUnit\Framework\TestCase;

class ApmTest extends TestCase
{
    private float $blikTranAmount = 2.02;

    protected function config()
    {
        $config = new GpEcomConfig();
        $config->merchantId = "heartlandgpsandbox";
        $config->accountId = "api";
        $config->rebatePassword = 'refund';
        $config->refundPassword = 'refund';
        $config->sharedSecret = "secret";
        $config->serviceUrl = "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    private function blikConfig() : Configuration
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->country = 'PL';
        $config->appId = 'p2GgW0PntEUiUh4qXhJHPoDqj3G5GFGI';
        $config->appKey = 'lJk4Np5LoUEilFhH';
        $config->serviceUrl = 'https://apis-sit.globalpay.com/ucp';

        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->riskAssessmentAccountName = 'EOS_RiskAssessment';
        $accessTokenInfo->transactionProcessingAccountName = 'GPECOM_BLIK_APM_Transaction_Processing';
        $config->accessTokenInfo = $accessTokenInfo;

        $config->requestLogger = new RequestConsoleLogger();

        return $config;
    }

    private function payuConfig() : Configuration
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->country = 'PL';
        $config->appId = 'ZbFY1jAz6sqq0GAyIPZe1raLCC7cUlpD';
        $config->appKey = '4NpIQJDCIDzfTKhA';
        $config->serviceUrl = 'https://apis.globalpay.com/ucp';

        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->riskAssessmentAccountName = 'EOS_RiskAssessment';
        $accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $config->accessTokenInfo = $accessTokenInfo;

        $config->requestLogger = new RequestConsoleLogger();

        return $config;
    }

    public function setup() : void
    {
        ServicesContainer::configureService($this->config());
        ServicesContainer::configureService($this->blikConfig(), 'blikConfig');
        ServicesContainer::configureService($this->payuConfig(), 'payuConfig');
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

    public function testApmWithoutAmount()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("amount cannot be null for this transaction type");
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

    public function testApmWithoutCurrency()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("currency cannot be null for this transaction type");
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

    public function testApmWithoutReturnUrl()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("returnUrl cannot be null for this transaction type");
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

    public function testApmWithoutStatusUpdateUrl()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("statusUpdateUrl cannot be null for this transaction type");
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

    public function testApmWithoutCountry()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("country cannot be null for this transaction type");
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::SOFORTUBERWEISUNG);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge(1001)
            ->withCurrency("EUR")
            ->withDescription('New APM')
            ->execute();
    }

    public function testApmWithoutAccountHolderName()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\BuilderException::class);
        $this->expectExceptionMessage("accountHolderName cannot be null for this transaction type");
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::SOFORTUBERWEISUNG);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'DE';

        $response = $paymentMethod->charge(1001)
            ->withCurrency("EUR")
            ->withDescription('New APM')
            ->execute();
    }

    public function testAPMRefundPendingTransaction()
    {
        $this->expectException(\GlobalPayments\Api\Entities\Exceptions\GatewayException::class);
        $this->expectExceptionMessage("FAILED");
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

    /* Validates a successful sale transaction using Blik APM with all required fields provided */
    public function testBlikSale_WhenRequestIsValid_ShouldSucceed() {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::BLIK);

        $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
        $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->country = 'PL';
        $paymentMethod->accountHolderName = 'James Mason';

        $response = $paymentMethod->charge($this->blikTranAmount)
            ->withCurrency("PLN")
            ->withDescription('New APM')
            ->execute('blikConfig');

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotNull($response->alternativePaymentResponse);
        $this->assertNotNull($response->alternativePaymentResponse->redirectUrl);
        $this->assertEquals("BLIK", strtoupper($response->alternativePaymentResponse->providerName));
    }

    /* verify that a sale transaction using Blik APM throws an exception when the ReturnUrl is missing. */
    public function testBlikSale_WhenReturnUrlMissing_ShouldThrowException() {
        $errorFound = false;
        try {
            $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::BLIK);
            $paymentMethod->statusUpdateUrl = 'https://www.example.com/statusUrl';
            $paymentMethod->descriptor = 'Test Transaction';
            $paymentMethod->country = 'PL';
            $paymentMethod->accountHolderName = 'James Mason';

            $response = $paymentMethod->charge($this->blikTranAmount)
                ->withCurrency("PLN")
                ->withDescription('New APM')
                ->execute('blikConfig');

            $this->assertNotNull($response);
        } catch (BuilderException $e) {
            $errorFound = true;
                $this->assertEquals('returnUrl cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    /* verify that a sale transaction using Blik APM throws an exception when the statusUpdateUrl is missing. */
    public function testBlikSale_WhenStatusUpdateUrlMissing_ShouldThrowException() {

        $errorFound = false;
        try {
            $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::BLIK);
            $paymentMethod->returnUrl = 'https://www.example.com/returnUrl';
            $paymentMethod->descriptor = 'Test Transaction';
            $paymentMethod->country = 'PL';
            $paymentMethod->accountHolderName = 'James Mason';

            $response = $paymentMethod->charge($this->blikTranAmount)
                ->withCurrency("PLN")
                ->withDescription('New APM')
                ->execute('blikConfig');

            $this->assertNotNull($response);
        } catch (BuilderException $e) {
            $errorFound = true;
                $this->assertEquals('statusUpdateUrl cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    /* Validates that the first refund attempt on a Blik APM transaction is approved successfully. */
    public function testBlikRefund_WhenFirstAttempt_ShouldSucceed() {

        $this->markTestSkipped('To run this test you need to follow below steps for Refund transaction.');

        /**
         * 1. For refund we have to run sale test and get Transaction ID from that response and paste here in transactionId.
         * 2. Also go to redirect_url from response of sale and approve by entering the code.
         * 3. After some time when status changed to "Captured" run the refund test.
         */
        $transactionId = "TRN_QabwK3KyGNauPdHTsOQcScUxVksN7u_fe28cfcf1aa4";

        /** create the rebate transaction object */
        $transaction = Transaction::fromId($transactionId);

        /** @var TransactionSummary $response */
        $transactionDetails = ReportingService::transactionDetail($transactionId)->execute('blikConfig');

        $transaction->alternativePaymentResponse = $transactionDetails->alternativePaymentResponse;

        $response = $transaction->refund($this->blikTranAmount)
                ->withCurrency("PLN")
                ->withAlternativePaymentType(AlternativePaymentType::BLIK)
                ->execute('blikConfig');
    
        $this->assertNotNull($response);
        $this->assertEquals("blik", $response->alternativePaymentResponse->providerName);
        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    /* Ensures that a second refund attempt on the same Blik APM transaction returns a "Declined" response. */
    public function testBlikRefund_WhenSecondAttempt_ShouldBeDeclined() {

        /* Run Refund with same transaction Id given in first time blik apm refund */
        $transactionId = "TRN_QabwK3KyGNauPdHTsOQcScUxVksN7u_fe28cfcf1aa4";

        /* Create the rebate transaction object */
        $transaction = Transaction::fromId($transactionId);

        /** @var TransactionSummary $response */
        $transactionDetails = ReportingService::transactionDetail($transactionId)->execute('blikConfig');

        $transaction->alternativePaymentResponse = $transactionDetails->alternativePaymentResponse;

        $response = $transaction->refund($this->blikTranAmount)
                ->withCurrency("PLN")
                ->withAlternativePaymentType(AlternativePaymentType::BLIK)
                ->execute('blikConfig');

        $this->assertNotNull($response);
        $this->assertEquals("blik", $response->alternativePaymentResponse->providerName);
        $this->assertEquals('DECLINED', $response->responseCode);
    }

    /* Validates a successful sale transaction using Payu APM with all required fields provided */
    public function testPayUSale_WhenRequestIsValid_ShouldSucceed() {
        $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::OB);
        $paymentMethod->bank = 'mbank';
        $paymentMethod->country = 'PL';
        $paymentMethod->accountHolderName = 'Jane';
        $paymentMethod->descriptor = 'Test Transaction';
        $paymentMethod->returnUrl = 'https://webhook.site/b4d275cd-42af-48c4-a89b-7a21cbb071c8';
        $paymentMethod->statusUpdateUrl = 'https://webhook.site/b4d275cd-42af-48c4-a89b-7a21cbb071c8';
        $paymentMethod->cancelUrl = 'https://webhook.site/b4d275cd-42af-48c4-a89b-7a21cbb071c8';

        $response = $paymentMethod->charge(0.01)
            ->withCurrency("PLN")
            ->withDescription('New APM')
            ->execute('payuConfig');

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotNull($response->alternativePaymentResponse);
        $this->assertEquals("BANK_PAYMENT", strtoupper($response->alternativePaymentResponse->providerName));
    }
    
    /* verify that a sale transaction using PayU APM throws an exception when the ReturnUrl is missing. */
    public function testPayuSale_WhenReturnUrlMissing_ShouldThrowException() {
        $errorFound = false;
        try {
            $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::OB);
            $paymentMethod->country = 'PL';
            $paymentMethod->accountHolderName = 'Jane';
            $paymentMethod->descriptor = 'Test Transaction';
            $paymentMethod->statusUpdateUrl = 'https://webhook.site/b4d275cd-42af-48c4-a89b-7a21cbb071c8';
            $paymentMethod->cancelUrl = 'https://webhook.site/b4d275cd-42af-48c4-a89b-7a21cbb071c8';

            $response = $paymentMethod->charge(0.01)
                ->withCurrency("PLN")
                ->withDescription('New APM')
                ->execute('payuConfig');

            $this->assertNotNull($response);
        } catch (BuilderException $e) {
            $errorFound = true;
                $this->assertEquals('returnUrl cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    /* verify that a sale transaction using PayU APM throws an exception when the statusUpdateUrl is missing. */
    public function testPayuSale_WhenStatusUpdateUrlMissing_ShouldThrowException() {

        $errorFound = false;
        try {
            $paymentMethod = new AlternativePaymentMethod(AlternativePaymentType::OB);
            $paymentMethod->country = 'PL';
            $paymentMethod->accountHolderName = 'Jane';
            $paymentMethod->descriptor = 'Test Transaction';
            $paymentMethod->returnUrl = 'https://webhook.site/b4d275cd-42af-48c4-a89b-7a21cbb071c8';
            $paymentMethod->cancelUrl = 'https://webhook.site/b4d275cd-42af-48c4-a89b-7a21cbb071c8';

            $response = $paymentMethod->charge(0.01)
                ->withCurrency("PLN")
                ->withDescription('New APM')
                ->execute('payuConfig');

            $this->assertNotNull($response);
        } catch (BuilderException $e) {
            $errorFound = true;
                $this->assertEquals('statusUpdateUrl cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }
}