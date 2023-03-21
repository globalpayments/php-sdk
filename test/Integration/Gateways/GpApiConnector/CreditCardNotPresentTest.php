<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\ManualEntryMethod;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\StoredCredentialReason;
use GlobalPayments\Api\Entities\Enums\StoredCredentialSequence;
use GlobalPayments\Api\Entities\Enums\StoredCredentialType;
use GlobalPayments\Api\Entities\Enums\StoredPaymentMethodSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Tests\Data\GpApiAvsCheckTestCards;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class CreditCardNotPresentTest extends TestCase
{
    /**
     * @var CreditCardData $card
     */
    private $card;

    /**
     * @var string
     */
    private $idempotencyKey;

    private $currency = 'GBP';

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cvn = "131";
        $this->card->cardHolderName = "James Mason";
        $this->idempotencyKey = GenerationUtils::getGuid();
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function testCreditSale()
    {
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->country = "US";
        $address->postalCode = "12345";

        $response = $this->card->charge(69)
            ->withCurrency($this->currency)
            ->withAddress($address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNull($response->payerDetails);
    }

    public function testCreditSaleWithFingerPrint()
    {
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->country = "US";
        $address->postalCode = "12345";

        $customer = new Customer();
        $customer->deviceFingerPrint = "ALWAYS";

        $response = $this->card->charge(69)
            ->withCurrency($this->currency)
            ->withAddress($address)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->fingerprint);
        $this->assertNotNull($response->fingerprintIndicator);
    }

    public function testCreditSaleWith_FingerPrintSuccess()
    {
        $customer = new Customer();
        $customer->deviceFingerPrint = "ON_SUCCESS";

        $response = $this->card->charge(69)
            ->withCurrency($this->currency)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->fingerprint);
        $this->assertNotNull($response->fingerprintIndicator);
    }

    public function testCreditAuthorization()
    {
        $transaction = $this->card->authorize(42)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
    }

    public function testAuthorizationThenCapture()
    {
        $transaction = $this->card->authorize(42)
            ->withCurrency($this->currency)
            ->withOrderId('123456-78910')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        try {
            $capture = $transaction->capture(30)
                ->withGratuity(12)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Transaction capture failed");
        }

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);
    }

    public function testAuthorizationThenCapture_WithFingerPrint()
    {
        $customer = new Customer();
        $customer->deviceFingerPrint = "ON_SUCCESS";

        $transaction = $this->card->authorize(42)
            ->withCurrency($this->currency)
            ->withOrderId('123456-78910')
            ->withAllowDuplicates(true)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
        $this->assertNotNull($transaction->fingerprint);
        $this->assertNotNull($transaction->fingerprintIndicator);

        try {
            $capture = $transaction->capture(30)
                ->withGratuity(12)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Transaction capture failed");
        }

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);
    }

    public function testAuthorizationThenCaptureWithIdempotencyKey()
    {
        $transaction = $this->card->authorize(42)
            ->withCurrency($this->currency)
            ->withIdempotencyKey($this->idempotencyKey)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        try {
            $transaction->capture(30)
                ->withIdempotencyKey($this->idempotencyKey)
                ->withGratuity(12)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCreditRefund()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->refund(16)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card Refund failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditRefund_WithFingerPrint()
    {
        $customer = new Customer();
        $customer->deviceFingerPrint = "ALWAYS";

        try {
            // process an auto-capture authorization
            $response = $this->card->refund(16)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->withCustomerData($customer)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card Refund failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->fingerprint);
        $this->assertNotNull($response->fingerprintIndicator);
    }

    public function testCreditDefaultRefund()
    {
        try {
            $transaction = $this->card->charge(50)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $response = $transaction->refund()
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card Refund failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditRefund_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();
        try {
            $transaction = $this->card->charge(50)
                ->withCurrency($this->currency)
                ->withIdempotencyKey($idempotencyKey)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $transaction->refund(50)
                ->withCurrency($this->currency)
                ->withIdempotencyKey($idempotencyKey)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCreditSale_WithoutPermissions()
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->permissions = ["TRN_POST_Capture"];

        ServicesContainer::configureService($config);

        $exceptionCaught = false;
        try {
            $this->card->charge(50)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40212', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - Permission not enabled to execute action', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testTransactionThenRefund()
    {
        try {
            $transaction = $this->card->charge(50)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        $partialAmount = '4.75';
        $partialRefund = $transaction->refund($partialAmount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($partialRefund);
        $this->assertEquals('SUCCESS', $partialRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $partialRefund->responseMessage);
        $this->assertEquals($partialAmount, $partialRefund->balanceAmount);

        try {
            $transaction->refund()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40087', $e->responseCode);
            $this->assertStringContainsString("INVALID_REQUEST_DATA - You may only refund up to 100% of the original amount", $e->getMessage());
        }
    }

    public function testTransactionThenReversal()
    {
        try {
            $transaction = $this->card->charge(20)
                ->withCurrency("EUR")
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $reverse = $transaction->reverse(20)->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present managed reversal failed");
        }

        $this->assertNotNull($reverse);
        $this->assertEquals('SUCCESS', $reverse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverse->responseMessage);
    }

    public function testTransactionThenDefaultReversal()
    {
        try {
            $transaction = $this->card->charge(20)
                ->withCurrency("EUR")
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $defaultReversal = $transaction->reverse()->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present managed reversal failed");
        }

        $this->assertNotNull($defaultReversal);
        $this->assertEquals('SUCCESS', $defaultReversal->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $defaultReversal->responseMessage);
    }

    public function testReverseTransaction_WithIdempotencyKey()
    {
        $transaction = $this->card->charge(20)
            ->withCurrency("EUR")
            ->withAllowDuplicates(true)
            ->withIdempotencyKey($this->idempotencyKey)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $transaction->reverse(20)
                ->withIdempotencyKey($this->idempotencyKey)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testTransactionThenPartialReversal()
    {
        try {
            $transaction = $this->card->charge(16)
                ->withCurrency("EUR")
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $transaction->reverse(10)->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40214', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - partial reversal not supported', $e->getMessage());
        }
    }

    public function testCreditAuthorizationForMultiCapture()
    {
        try {
            $transaction = $this->card->authorize(42)
                ->withCurrency('EUR')
                ->withMultiCapture(true)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card Authorization Failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
        $this->assertTrue($transaction->multiCapture);

        $capture1 = $transaction->capture(10)->execute();

        $this->assertNotNull($capture1);
        $this->assertEquals('SUCCESS', $capture1->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture1->responseMessage);

        $capture2 = $transaction->capture(20)->execute();

        $this->assertNotNull($capture2);
        $this->assertEquals('SUCCESS', $capture2->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture2->responseMessage);

        $capture3 = $transaction->capture(10)->execute();

        $this->assertNotNull($capture3);
        $this->assertEquals('SUCCESS', $capture3->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture3->responseMessage);
    }

    public function testCreditChargeTransactions_WithSameIdempotencyKey()
    {
        $response = $this->card->charge(69)
            ->withCurrency("EUR")
            ->withIdempotencyKey($this->idempotencyKey)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);

        $exceptionCaught = false;
        try {
            $this->card->charge(69)
                ->withCurrency("EUR")
                ->withIdempotencyKey($this->idempotencyKey)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals(
                sprintf(
                    "Status Code: %s - Idempotency Key seen before: id=%s",
                    'DUPLICATE_ACTION',
                    $response->transactionId
                ), $e->getMessage()
            );
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardTokenization()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->tokenize()->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card Tokenization failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);
    }

    public function testCardTokenizationThenPayingWithToken_SingleToMultiUse()
    {
        // process an auto-capture authorization
        $response = $this->card->tokenize(true, PaymentMethodUsageMode::SINGLE)
            ->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $response = $tokenizedCard->charge(10)
            ->withCurrency("USD")
            ->withRequestMultiUseToken(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertStringStartsWith('PMT_', $response->token);
        $tokenizedCard->token = $response->token;
        $response = $tokenizedCard->charge(10)
            ->withCurrency("USD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCardTokenization_MissingCardNumber()
    {
        $card = new CreditCardData();

        try {
            $card->tokenize()->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields : number', $e->getMessage());
        }
    }

    public function testCardTokenizationWithIdempotencyKey()
    {
        $response = $this->card->tokenize()->withIdempotencyKey($this->idempotencyKey)->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        $exceptionCaught = false;
        try {
            $this->card->tokenize()->withIdempotencyKey($this->idempotencyKey)->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardTokenizationThenPayingWithToken()
    {
        // process an auto-capture authorization
        $response = $this->card->tokenize()
            ->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";

        $response = $tokenizedCard->charge(69)
            ->withCurrency("EUR")
            ->withOrderId("124214-214221")
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testVerifyTokenizedPaymentMethod()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->tokenize()
                ->execute();

        } catch (ApiException $e) {
            $this->fail('Credit Card Tokenization failed ' . $e->getMessage());
        }

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;
        try {
            $response = $tokenizedCard->verify()
                ->withCurrency($this->currency)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card token retrieval failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testVerifyTokenizedPaymentMethodWithIdempotencyKey()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->tokenize()
                ->execute();

        } catch (ApiException $e) {
            $this->fail('Credit Card Tokenization failed ' . $e->getMessage());
        }
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;

        try {
            $response = $tokenizedCard->verify()
                ->withCurrency($this->currency)
                ->withIdempotencyKey($this->idempotencyKey)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card token retrieval failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);

        try {
            $tokenizedCard->verify()->withIdempotencyKey($this->idempotencyKey)->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testVerifyTokenizedPaymentMethod_WrongID()
    {
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = "PMT_" . GenerationUtils::getGuid();

        try {
            $tokenizedCard->verify()->withCurrency($this->currency)->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40116', $e->responseCode);
            $this->assertEquals('Status Code: RESOURCE_NOT_FOUND - payment_method ' . $tokenizedCard->token . ' not found at this location.', $e->getMessage());
        }
    }

    public function testCreditVerify()
    {
        $response = $this->card->verify()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testCreditVerifyWithIdempotencyKey()
    {
        $response = $this->card->verify()
            ->withCurrency($this->currency)
            ->withIdempotencyKey($this->idempotencyKey)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testCardTokenizationThenDeletion()
    {
        $this->markTestSkipped('Permission not enabled to execute action for this appId/appKey');
        // process an auto-capture authorization
        $response = $this->card->tokenize()
            ->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;

        $response = $tokenizedCard->deleteToken();
        $this->assertEquals(true, $response);

        $response = $tokenizedCard->deleteToken();
        $this->assertEquals(false, $response);
    }

    public function testCardTokenizationThenDeletion_WithIdempotencyKey()
    {
        $this->markTestSkipped(' Permission not enabled to execute action for this appId/appKey');
        // process an auto-capture authorization
        $response = $this->card->tokenize()
            ->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;

        $response = (new ManagementBuilder(TransactionType::TOKEN_DELETE))
            ->withPaymentMethod($tokenizedCard)
            ->withIdempotencyKey($this->idempotencyKey)
            ->execute();
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('DELETED', $response->responseMessage);

        try {
            (new ManagementBuilder(TransactionType::TOKEN_DELETE))
                ->withPaymentMethod($tokenizedCard)
                ->withIdempotencyKey($this->idempotencyKey)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCardDelete_WrongId()
    {
        $this->markTestSkipped('Permission not enabled to execute action for this appId/appKey');
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = "PMT_" . GenerationUtils::getGuid();

        try {
            $tokenizedCard->deleteToken();
        } catch (ApiException $e) {
            $this->assertEquals('40116', $e->responseCode);
            $this->assertStringContainsString(sprintf('Status Code: RESOURCE_NOT_FOUND - payment_method %s not found at this location', $tokenizedCard->token), $e->getMessage());
        }
    }

    public function testCardTokenizationThenUpdate()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->tokenize()->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card Tokenization failed ' . $e->getMessage());
        }
        $this->assertNotNull($response);

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;
        $tokenizedCard->expYear = date('Y', strtotime('+1 year'));
        $tokenizedCard->expMonth = date('m');

        try {
            $response = $tokenizedCard->updateTokenExpiry();
        } catch (ApiException $e) {
            $this->fail('Credit Card token update failed ' . $e->getMessage());
        }

        $this->assertEquals(true, $response);
    }

    public function testCardUpdate_WrongId()
    {
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = "PMT_" . GenerationUtils::getGuid();
        $tokenizedCard->expYear = date('Y', strtotime('+1 year'));
        $tokenizedCard->expMonth = date('m');

        try {
            $tokenizedCard->updateTokenExpiry();
        } catch (ApiException $e) {
            $this->assertEquals('40116', $e->responseCode);
            $this->assertStringContainsString(sprintf('Status Code: RESOURCE_NOT_FOUND - payment_method %s not found at this location', $tokenizedCard->token), $e->getMessage());
        }
    }

    public function testCardTokenizationThenUpdateWithIdempotencyKey()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->tokenize()->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card Tokenization failed ' . $e->getMessage());
        }
        $this->assertNotNull($response);

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;
        $tokenizedCard->expYear = date('Y', strtotime('+1 year'));
        $tokenizedCard->expMonth = date('m');

        $response = (new ManagementBuilder(TransactionType::TOKEN_UPDATE))
            ->withPaymentMethod($tokenizedCard)
            ->withIdempotencyKey($this->idempotencyKey)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        try {
            (new ManagementBuilder(TransactionType::TOKEN_UPDATE))
                ->withPaymentMethod($tokenizedCard)
                ->withIdempotencyKey($this->idempotencyKey)
                ->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        }

        $response = $tokenizedCard->verify()->withCurrency($this->currency)->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);

        $tokenizedCard->expYear = date('Y', strtotime('+2 year'));
        $this->assertTrue($tokenizedCard->updateTokenExpiry());
    }

    public function testCreditRefundTransactionWrongId()
    {
        $transaction = new Transaction();
        $transaction->transactionId = GenerationUtils::getGuid();
        try {
            $transaction->refund(10)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40008', $e->responseCode);
            $this->assertStringContainsString('RESOURCE_NOT_FOUND', $e->getMessage());
        }
    }

    public function testCreditRefundTransactionWithIdempotencyKey()
    {
        $transaction = $this->card->charge(10.22)
            ->withCurrency($this->currency)
            ->withIdempotencyKey($this->idempotencyKey)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $transaction->refund(10.22)
                ->withCurrency($this->currency)
                ->withIdempotencyKey($this->idempotencyKey)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCreditSale_Tokenized_WithStoredCredentials()
    {
        $storeCredentials = new StoredCredential();
        $storeCredentials->initiator = StoredCredentialInitiator::MERCHANT;
        $storeCredentials->type = StoredCredentialType::INSTALLMENT;
        $storeCredentials->sequence = StoredCredentialSequence::SUBSEQUENT;
        $storeCredentials->reason = StoredCredentialReason::INCREMENTAL;

        $response = $this->card->tokenize()
            ->execute();
        $tokenId = $response->token;
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "James Mason";
        $response = $tokenizedCard->charge(50)
            ->withCurrency("EUR")
            ->withStoredCredential($storeCredentials)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditSale_WithStoredCredentials()
    {
        $storeCredentials = new StoredCredential();
        $storeCredentials->initiator = StoredCredentialInitiator::MERCHANT;
        $storeCredentials->type = StoredCredentialType::INSTALLMENT;
        $storeCredentials->sequence = StoredCredentialSequence::SUBSEQUENT;
        $storeCredentials->reason = StoredCredentialReason::INCREMENTAL;

        $response = $this->card->charge(50)
            ->withCurrency("EUR")
            ->withStoredCredential($storeCredentials)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditSale_WithDynamicDescriptor()
    {
        $dynamicDescriptor = 'My company';
        $response = $this->card->charge(50)
            ->withCurrency("EUR")
            ->withDynamicDescriptor($dynamicDescriptor)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditReverseTransactionWrongId()
    {
        $transaction = new Transaction();
        $transaction->transactionId = GenerationUtils::getGuid();
        try {
            $transaction->reverse()
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40008', $e->responseCode);
            $this->assertStringContainsString('RESOURCE_NOT_FOUND', $e->getMessage());
        }
    }

    public function testCreditVerification()
    {
        $response = $this->card->verify()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals("VERIFIED", $response->responseMessage);
    }

    public function testCreditVerification_withIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();

        $response = $this->card->verify()
            ->withIdempotencyKey($idempotencyKey)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals("VERIFIED", $response->responseMessage);

        $exceptionCaught = false;
        try {
            $this->card->verify()
                ->withIdempotencyKey($idempotencyKey)
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Status Code: DUPLICATE_ACTION - Idempotency Key seen before: ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditVerification_WithAddress()
    {
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->country = "US";
        $address->postalCode = "12345";

        $response = $this->card->verify()
            ->withCurrency($this->currency)
            ->withAddress($address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals("VERIFIED", $response->responseMessage);
    }

    public function testCreditVerification_WithoutCurrency()
    {
        try {
            $this->card->verify()
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields currency', $e->getMessage());
        }
    }

    public function testCreditVerification_InvalidCVV()
    {
        $this->card->cvn = "1234";

        try {
            $this->card->verify()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40085', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Security Code/CVV2/CVC must be 3 digits', $e->getMessage());
        }
    }

    public function testCreditVerification_NotNumericCVV()
    {
        $this->card->cvn = "SMA";

        try {
            $this->card->verify()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('50018', $e->responseCode);
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - The line number 12 which contains \'         [number] XXX [/number] \' does not conform to the schema', $e->getMessage());
        }
    }

    public function testCaptureHigherAmount()
    {
        try {
            $transaction = $this->card->authorize(55)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card Authorization Failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        $capture = $transaction->capture('60')
            ->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);

        $transaction = $this->card->authorize(30)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        try {
            $transaction->capture('40')
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('50020', $e->responseCode);
            $this->assertStringContainsString("INVALID_REQUEST_DATA - Can't settle for more than 115% of that which you authorised", $e->getMessage());
        }
    }

    public function testCaptureLowerAmount()
    {
        try {
            $transaction = $this->card->authorize('55')
                ->withCurrency($this->currency)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card Authorization Failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        $capture = $transaction->capture('20')
            ->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);
    }

    public function testChargeThenRefundHigherAmount()
    {
        try {
            $transaction = $this->card->charge(50)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $transaction->refund(60)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40087', $e->responseCode);
            $this->assertStringContainsString("INVALID_REQUEST_DATA - You may only refund up to 115% of the original amount", $e->getMessage());
        }
    }

    public function testCaptureThenRefundHigherAmount()
    {
        $transaction = $this->card->authorize('55')
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        $capture = $transaction->capture('55')
            ->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);

        try {
            $response = $transaction->refund(60)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40087', $e->responseCode);
            $this->assertStringContainsString("INVALID_REQUEST_DATA - You may only refund up to 100% of the original amount", $e->getMessage());
        }

        if (!empty($response)) {
            $this->assertNotNull($response);
            $this->assertEquals('SUCCESS', $capture->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);
        }
    }

    public function testManualTransactions()
    {
        $entryModes = [ManualEntryMethod::MOTO, ManualEntryMethod::MAIL, ManualEntryMethod::PHONE];
        foreach ($entryModes as $entryMode) {
            $this->card->entryMethod = $entryMode;

            $response = $this->card->charge(69)
                ->withCurrency($this->currency)
                ->execute();

            $this->assertNotNull($response);
            $this->assertEquals('SUCCESS', $response->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        }
    }

    public function testCreditSale_ExpiryCard()
    {
        $this->card->expYear = date('Y', strtotime('-1 year'));

        $exceptionCaught = false;
        try {
            $this->card->charge(1)
                ->withCurrency($this->currency)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40085', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Expiry date invalid', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    /**
     * Avs test cards scenario
     *
     * @dataProvider AvsCardTests
     * @param $cardNumber
     * @param $cvnResponseMessage
     * @param $avsResponseCode
     * @param $avsAddressResponse
     * @param $status
     * @param $transactionStatus
     * @param $cvvResult
     * @param $avsPostcode
     * @param $addressResult
     */
    public function testCreditSale_CVVResult($cardNumber, $cvnResponseMessage, $avsResponseCode, $avsAddressResponse, $status, $transactionStatus, $cvvResult, $avsPostcode, $addressResult)
    {
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->country = "US";
        $address->postalCode = "12345";

        $this->card->number = $cardNumber;

        $response = $this->card->charge(5)
            ->withCurrency($this->currency)
            ->withAddress($address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals($status, $response->responseCode);
        $this->assertEquals($transactionStatus, $response->responseMessage);
        $this->assertEquals($cvnResponseMessage, $response->cvnResponseMessage);
        $this->assertEquals($avsResponseCode, $response->avsResponseCode);
        $this->assertEquals($avsAddressResponse, $response->avsAddressResponse);
        $this->assertEquals($addressResult, $response->cardIssuerResponse->avsAddressResult);
        $this->assertEquals($avsPostcode, $response->cardIssuerResponse->avsPostalCodeResult);
        $this->assertEquals($cvvResult, $response->cardIssuerResponse->cvvResult);
    }

    public function testCreditAuthorizationWithPaymentLinkId()
    {
        $transaction = $this->card->authorize(42)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->withPaymentLinkId('LNK_W1xgWehivDP8P779cFDDTZwzL01Ew4')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
    }

    public function testVerifyTokenizedPaymentMethodWithFingerprint()
    {
        $customer = new Customer();
        $customer->deviceFingerPrint = "ALWAYS";
        $response = $this->card->tokenize()
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotNull($response->fingerprint);

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;

        $response = $tokenizedCard->verify()
            ->withCurrency($this->currency)
            ->withCustomerData($customer)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
        $this->assertNotNull($response->fingerprint);
    }

    public function testCreditSaleWith_InvalidFingerPrint()
    {
        $customer = new Customer();
        $customer->deviceFingerPrint = "NOT_ALWAYS";

        try {
            $this->card->charge(60)
                ->withCurrency($this->currency)
                ->withCustomerData($customer)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40213', $e->responseCode);
            $this->assertEquals("Status Code: INVALID_REQUEST_DATA - fingerprint_mode contains unexpected data", $e->getMessage());
        }
    }

    public function testUpdatePaymentToken()
    {
        $startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 1)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::DESC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertCount(1, $response->result);
        $pmtToken = reset($response->result)->paymentMethodId;
        $this->assertNotEmpty($pmtToken);
        $this->assertNotNull($pmtToken);
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $pmtToken;
        $tokenizedCard->cardHolderName = 'James BondUp';
        $tokenizedCard->expYear = date('Y', strtotime('+1 year'));
        $tokenizedCard->expMonth = date('m');
        $tokenizedCard->number = "4263970000005262";

        $response = $tokenizedCard->updateToken()
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::MULTIPLE)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);
        $this->assertEquals($pmtToken, $response->token);
        $this->assertEquals(PaymentMethodUsageMode::MULTIPLE, $response->tokenUsageMode);
    }

    public function testCardTokenizationThenUpdateAndThenCharge()
    {
        $response = $this->card->tokenize()
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;
        $tokenizedCard->cardHolderName = "GpApi";

        $responseUpdateToken = $tokenizedCard->updateToken()
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::MULTIPLE)
            ->execute();
        $this->assertNotNull($responseUpdateToken);
        $this->assertEquals('SUCCESS', $responseUpdateToken->responseCode);
        $this->assertEquals('ACTIVE', $responseUpdateToken->responseMessage);
        $this->assertEquals('MULTIPLE', $responseUpdateToken->tokenUsageMode);

        $chargeResponse = $tokenizedCard->charge(1)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertNotNull($chargeResponse);
        $this->assertEquals('SUCCESS', $chargeResponse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $chargeResponse->responseMessage);
    }

    public function testCardTokenizationThenUpdateToSingleUsage()
    {
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = 'PMT_' . GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            $tokenizedCard->updateToken()
                ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('50020', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Tokentype can only be MULTI', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCardTokenizationThenUpdateWithoutUsageMode()
    {
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = 'PMT_' . GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            $tokenizedCard->updateToken()
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('50021', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Mandatory Fields missing [card expdate] See Developers Guide', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    /**
     * AVS test cards
     *
     */
    public function AvsCardTests()
    {
        return [
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_1, "MATCHED", "NOT_CHECKED", "NOT_CHECKED", 'SUCCESS', TransactionStatus::CAPTURED, "M", "U", "U"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_2, "MATCHED", "NOT_CHECKED", "NOT_CHECKED", 'SUCCESS', TransactionStatus::CAPTURED, "M", "I", "I"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_3, "MATCHED", "NOT_CHECKED", "NOT_CHECKED", 'SUCCESS', TransactionStatus::CAPTURED, "M", "P", "P"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_4, "MATCHED", "MATCHED", "MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "M", "M", "M"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_5, "MATCHED", "MATCHED", "NOT_MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "M", "M", "N"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_6, "MATCHED", "NOT_MATCHED", "MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "M", "N", "M"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_7, "MATCHED", "NOT_MATCHED", "NOT_MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "M", "N", "N"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_8, "NOT_MATCHED", "NOT_MATCHED", "MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "N", "N", "M"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_9, "NOT_MATCHED", "NOT_CHECKED", "NOT_CHECKED", 'SUCCESS', TransactionStatus::CAPTURED, "N", "U", "U"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_10, "NOT_MATCHED", "NOT_CHECKED", "NOT_CHECKED", 'SUCCESS', TransactionStatus::CAPTURED, "N", "I", "I"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_11, "NOT_MATCHED", "NOT_CHECKED", "NOT_CHECKED", 'SUCCESS', TransactionStatus::CAPTURED, "N", "P", "P"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_12, "NOT_MATCHED", "MATCHED", "MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "N", "M", "M"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_13, "NOT_MATCHED", "MATCHED", "NOT_MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "N", "M", "N"],
            [GpApiAvsCheckTestCards::AVS_MASTERCARD_14, "NOT_MATCHED", "NOT_MATCHED", "NOT_MATCHED", 'SUCCESS', TransactionStatus::CAPTURED, "N", "N", "N"],
            [GpApiAvsCheckTestCards::AVS_VISA_1, "NOT_CHECKED", "NOT_CHECKED", "NOT_CHECKED", 'DECLINED', TransactionStatus::DECLINED, "I", "U", "U"],
            [GpApiAvsCheckTestCards::AVS_VISA_2, "NOT_CHECKED", "NOT_CHECKED", "NOT_CHECKED", 'DECLINED', TransactionStatus::DECLINED, "I", "I", "I"],
            [GpApiAvsCheckTestCards::AVS_VISA_3, "NOT_CHECKED", "NOT_CHECKED", "NOT_CHECKED", 'DECLINED', TransactionStatus::DECLINED, "I", "P", "P"],
            [GpApiAvsCheckTestCards::AVS_VISA_4, "NOT_CHECKED", "MATCHED", "MATCHED", 'DECLINED', TransactionStatus::DECLINED, "I", "M", "M"],
            [GpApiAvsCheckTestCards::AVS_VISA_5, "NOT_CHECKED", "MATCHED", "NOT_MATCHED", 'DECLINED', TransactionStatus::DECLINED, "I", "M", "N"],
            [GpApiAvsCheckTestCards::AVS_VISA_6, "NOT_CHECKED", "NOT_MATCHED", "MATCHED", 'DECLINED', TransactionStatus::DECLINED, "I", "N", "M"],
            [GpApiAvsCheckTestCards::AVS_VISA_7, "NOT_CHECKED", "NOT_MATCHED", "NOT_MATCHED", 'DECLINED', TransactionStatus::DECLINED, "I", "N", "N"],
            [GpApiAvsCheckTestCards::AVS_VISA_8, "NOT_CHECKED", "NOT_CHECKED", "NOT_CHECKED", 'DECLINED', TransactionStatus::DECLINED, "U", "U", "U"],
            [GpApiAvsCheckTestCards::AVS_VISA_9, "NOT_CHECKED", "NOT_CHECKED", "NOT_CHECKED", 'DECLINED', TransactionStatus::DECLINED, "U", "I", "I"],
            [GpApiAvsCheckTestCards::AVS_VISA_10, "NOT_CHECKED", "NOT_CHECKED", "NOT_CHECKED", 'DECLINED', TransactionStatus::DECLINED, "U", "P", "P"],
            [GpApiAvsCheckTestCards::AVS_VISA_11, "NOT_CHECKED", "MATCHED", "MATCHED", 'DECLINED', TransactionStatus::DECLINED, "U", "M", "M"],
            [GpApiAvsCheckTestCards::AVS_VISA_12, "NOT_CHECKED", "MATCHED", "NOT_MATCHED", 'DECLINED', TransactionStatus::DECLINED, "U", "M", "N"],
            [GpApiAvsCheckTestCards::AVS_VISA_13, "NOT_CHECKED", "NOT_MATCHED", "MATCHED", 'DECLINED', TransactionStatus::DECLINED, "U", "N", "M"],
            [GpApiAvsCheckTestCards::AVS_VISA_14, "NOT_CHECKED", "NOT_MATCHED", "NOT_MATCHED", 'DECLINED', TransactionStatus::DECLINED, "U", "N", "N"]
        ];
    }

    public function setUpConfig()
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        //DO NO DELETE - usage example for some settings
//        $config->dynamicHeaders = [
//            'x-gp-platform' => 'prestashop;version=1.7.2',
//            'x-gp-extension' => 'coccinet;version=2.4.1',
//        ];
//        $config->permissions = ['TRN_POST_Authorize'];
//        $config->webProxy = new CustomWebProxy('127.0.0.1:8866');
//        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

}