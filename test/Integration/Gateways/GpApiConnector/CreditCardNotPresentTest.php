<?php


namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\StoredCredentialReason;
use GlobalPayments\Api\Entities\Enums\StoredCredentialSequence;
use GlobalPayments\Api\Entities\Enums\StoredCredentialType;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\AccessTokenInfo;
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

    public function setup()
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

    public function testCreditSale()
    {
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->city = "Downtown";
        $address->state = "NJ";
        $address->country = "US";
        $address->postalCode = "12345";

        $response = $this->card->charge(69)
            ->withCurrency("USD")
            ->withAddress($address)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditAuthorization()
    {
        $transaction = $this->card->authorize(42)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
    }

    public function testAuthorizationThenCapture()
    {
        try {
            $transaction = $this->card->authorize(42)
                ->withCurrency('USD')
                ->withOrderId('123456-78910')
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card Authorization Failed");
        }

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

    public function testAuthorizationThenCaptureWithIdempotencyKey()
    {
        $transaction = $this->card->authorize(42)
            ->withCurrency('USD')
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
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCreditRefund()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->refund(16)
                ->withCurrency("USD")
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card Refund failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditDefaultRefund()
    {
        try {
            $transaction = $this->card->charge(50)
                ->withCurrency("USD")
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
                ->withCurrency("USD")
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
                ->withCurrency("USD")
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
                ->withCurrency("USD")
                ->withIdempotencyKey($idempotencyKey)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testTransactionThenRefund()
    {
        try {
            $transaction = $this->card->charge(50)
                ->withCurrency("USD")
                ->withAllowDuplicates(true)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present with ECOM transaction failed");
        }

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $partialRefund = $transaction->refund('22')
                ->withCurrency('USD')
                ->execute();
            $defaultRefund = $transaction->refund()
                ->withCurrency('USD')
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Card not present managed refund failed " . $e->getMessage());
        }

        $this->assertNotNull($partialRefund);
        $this->assertEquals('SUCCESS', $partialRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $partialRefund->responseMessage);

        $this->assertNotNull($defaultRefund);
        $this->assertEquals('SUCCESS', $defaultRefund->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $defaultRefund->responseMessage);
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
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
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
            $this->assertEquals('40006', $e->responseCode);
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
                    "Status Code: %s - Idempotency Key seen before: id=%s, status=%s",
                    'DUPLICATE_ACTION',
                    $response->transactionId,
                    TransactionStatus::CAPTURED
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
        $response = $this->card->tokenizeWithIdempotencyKey($this->idempotencyKey)->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        try {
            $this->card->tokenizeWithIdempotencyKey($this->idempotencyKey)->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
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
            $response = $tokenizedCard->verify()->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card token retrieval failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);
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
            $response = $tokenizedCard->verify()->withIdempotencyKey($this->idempotencyKey)->execute();
        } catch (ApiException $e) {
            $this->fail('Credit Card token retrieval failed ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        try {
            $tokenizedCard->verify()->withIdempotencyKey($this->idempotencyKey)->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testVerifyTokenizedPaymentMethod_WrongID()
    {
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = "PMT_" . GenerationUtils::getGuid();

        try {
            $tokenizedCard->verify()->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals('Status Code: RESOURCE_NOT_FOUND - PAYMENT_METHODS ' . $tokenizedCard->token . ' not found at this /ucp/payment-methods/' . $tokenizedCard->token, $e->getMessage());
        }
    }

    public function testDetokenizePaymentMethodWithIdempotencyKey()
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

        $detokenizedCard = $tokenizedCard->detokenizeWithIdempotencyKey($this->idempotencyKey);
        $this->assertNotNull($response);
        $this->assertEquals($this->card->number, $detokenizedCard->cardNumber);
        $this->assertEquals($this->card->expMonth, $detokenizedCard->cardExpMonth);
//        $this->assertEquals($this->card->expYear, $detokenizedCard->cardExpYear);

        try {
            $tokenizedCard->detokenizeWithIdempotencyKey($this->idempotencyKey);
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCreditVerify()
    {
        $response = $this->card->verify()
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testCreditVerifyWithIdempotencyKey()
    {
        $response = $this->card->verify()
            ->withCurrency('USD')
            ->withIdempotencyKey($this->idempotencyKey)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('VERIFIED', $response->responseMessage);
    }

    public function testCardTokenizationThenCardDetokenization()
    {
        try {
            // process an auto-capture authorization
            $response = $this->card->tokenize()
                ->execute();

        } catch (ApiException $e) {
            $this->fail('Credit Card Tokenization failed ' . $e->getMessage());
        }

        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;

        try {
            $response = $tokenizedCard->detokenize();
        } catch (ApiException $e) {
            $this->fail('Credit Card detokenization failed ' . $e->getMessage());
        }

        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testCardDetokenization_WrongId()
    {
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = "PMT_" . GenerationUtils::getGuid();

        try {
            $tokenizedCard->detokenize();
        } catch (ApiException $e) {
            $this->assertEquals('40116', $e->responseCode);
            $this->assertEquals('Status Code: RESOURCE_NOT_FOUND - payment_method ' . $tokenizedCard->token . ' not found at this location.', $e->getMessage());
        }
    }

    public function testCardTokenizationThenDeletion()
    {
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
        // process an auto-capture authorization
        $response = $this->card->tokenize()
            ->execute();
        $tokenId = $response->token;

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $tokenId;

        $response = $tokenizedCard->deleteTokenWithIdempotencyKey($this->idempotencyKey);
        $this->assertEquals(true, $response);

        try {
            $tokenizedCard->deleteTokenWithIdempotencyKey($this->idempotencyKey);
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCardDelete_WrongId()
    {
        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = "PMT_" . GenerationUtils::getGuid();

        try {
            $tokenizedCard->deleteToken();
        } catch (ApiException $e) {
            $this->assertEquals('40006', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - payment_method.id: ' . $tokenizedCard->token . ' contains unexpected data', $e->getMessage());
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
            $this->assertEquals('40006', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - payment_method.id: ' . $tokenizedCard->token . ' contains unexpected data', $e->getMessage());
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

        $this->assertTrue($tokenizedCard->updateTokenExpiryWithIdemPotencyKey($this->idempotencyKey));
        $this->assertFalse($tokenizedCard->updateTokenExpiryWithIdemPotencyKey($this->idempotencyKey));
        try {
            $tokenizedCard->updateTokenExpiryWithIdemPotencyKey($this->idempotencyKey);
        } catch (ApiException $e) {
            $this->assertEquals('40031', $e->responseCode);
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
        }

        $response = $tokenizedCard->verify()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        $tokenizedCard->expYear = date('Y', strtotime('+2 year'));
        $this->assertTrue($tokenizedCard->updateTokenExpiry());
    }

    public function testCreditRefundTransactionWrongId()
    {
        $transaction = new Transaction();
        $transaction->transactionId = GenerationUtils::getGuid();
        try {
            $transaction->refund(10)
                ->withCurrency('USD')
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40118', $e->responseCode);
            $this->assertContains('RESOURCE_NOT_FOUND', $e->getMessage());
        }
    }

    public function testCreditRefundTransactionWithIdempotencyKey()
    {
        $transaction = $this->card->charge(10.22)
            ->withCurrency('USD')
            ->withIdempotencyKey($this->idempotencyKey)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        try {
            $transaction->refund(10.22)
                ->withCurrency('USD')
                ->withIdempotencyKey($this->idempotencyKey)
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Idempotency Key seen before', $e->getMessage());
        }
    }

    public function testCreditSale_WithStoredCredentials()
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

    public function testCreditReverseTransactionWrongId()
    {
        $transaction = new Transaction();
        $transaction->transactionId = GenerationUtils::getGuid();
        try {
            $transaction->reverse()
                ->withCurrency('USD')
                ->withAllowDuplicates(true)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40118', $e->responseCode);
            $this->assertContains('RESOURCE_NOT_FOUND', $e->getMessage());
        }
    }

    public function testCreditVerification()
    {
        $response = $this->card->verify()
            ->withCurrency("USD")
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
            ->withCurrency("USD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals("VERIFIED", $response->responseMessage);

        $exceptionCaught = false;
        try {
            $this->card->verify()
                ->withIdempotencyKey($idempotencyKey)
                ->withCurrency("USD")
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Status Code: DUPLICATE_ACTION - Idempotency Key seen before: ', $e->getMessage());
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
            ->withCurrency("USD")
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
                ->withCurrency("USD")
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40085', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Security Code/CVV2/CVC must be 3 digits', $e->getMessage());
        }
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $accessTokenInfo = new AccessTokenInfo();
        //this is gpapistuff stuff
        $config->setAppId('VuKlC2n1cr5LZ8fzLUQhA7UObVks6tFF');
        $config->setAppKey('NmGM0kg92z2gA7Og');
        $config->environment = Environment::TEST;
        $config->setAccessTokenInfo($accessTokenInfo);
        $config->setChannel(Channels::CardNotPresent);

        return $config;
    }

}