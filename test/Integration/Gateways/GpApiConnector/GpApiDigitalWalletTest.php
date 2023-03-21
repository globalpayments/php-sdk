<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use PHPUnit\Framework\TestCase;

class GpApiDigitalWalletTest extends TestCase
{
    private $card;
    private $currency = 'EUR';
    private $amount = 10;
    private $googlePayToken;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";
        $this->googlePayToken = '{
          "signature": "MEYCIQChBRI9LwVnpkueFTULQBJbAHodFE6HnWX1iPFVdvfQ5wIhAMxZR232GGIa/yIROdEcIbh3x9qqILLUJ8K/KvqzncET",
          "protocolVersion": "ECv1",
          "signedMessage": "{\"encryptedMessage\":\"lUzJWAt/5VVerPaDLHctV0x7CHbAijOonczp2JtgVe9GS1fCiTwUuq6wCN6CWKe5mC+lRjpsgfTXuPFTgOGCCoTSYxAmb4hbtvjD7vlK+lHy/3UDtLGt0BxgPruT2IzyAzR5cNwf4fvmWwucTdf1nbywkAs5hIrVeQvxC1UkvBcsSz/pIMiBYkWRXk0NGH+g0AvyyYYUFZLZyZ/cfVXbUSRD012Ky8dvbt9Cu1VncBLhSuQ/0p8ifB/sWznp1ZZ3OTv5eMwHIW4oj18k22JJXhbbI5D8KEnkwg6zO5CBo+Ka29lTLbRd5Ok0MWSApeGfvQGqfSuPJJck/+HbJVshBYwuf6VEE0JLqXTIauEsYMY1rwb5St/57y8fDoHcNb5XYiE8/qN4G8YB0a8gGyEaMSLM5B9esgy8UQDWBc7nEOd7C13EDSe6O4joIdH0EFlICDYN\",\"ephemeralPublicKey\":\"BPp6cBv4ogIfYGfqtGPr2gwhNE3gLxco7Ka7laO1U7Jdijz2AHnodW1HgztnuBfuSXUKCVYOhblYYXt+pJE/zLI\\u003d\",\"tag\":\"dTBKFQag7foGF/MYBUJBsI8bFVQ766kEUb04jLk5hcA\\u003d\"}"
        }';
    }

    public static function tearDownAfterClass() : void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testClickToPayEncrypted()
    {
        $this->card->token = "9113329269393758302";
        $this->card->mobileType = EncyptedMobileType::CLICK_TO_PAY;

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->withMaskedDataResponse(true)
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
        $this->assertClickToPayPayerDetails($response);
    }

    public function testClickToPayEncryptedChargeThenRefund()
    {
        $this->card->token = "9113329269393758302";
        $this->card->mobileType = EncyptedMobileType::CLICK_TO_PAY;

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->withMaskedDataResponse(true)
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
        $this->assertClickToPayPayerDetails($response);

        $refund = $response->refund()
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($refund, TransactionStatus::CAPTURED);
        $this->assertClickToPayPayerDetails($response);
    }

    public function testClickToPayEncryptedChargeThenReverse()
    {
        $this->card->token = "9113329269393758302";
        $this->card->mobileType = EncyptedMobileType::CLICK_TO_PAY;

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->withMaskedDataResponse(true)
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
        $this->assertClickToPayPayerDetails($response);

        $reverse = $response->reverse()
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($reverse, TransactionStatus::REVERSED);
        $this->assertClickToPayPayerDetails($response);
    }

    public function testClickToPayEncryptedAuthorize()
    {
        $this->card->token = "9113329269393758302";
        $this->card->mobileType = EncyptedMobileType::CLICK_TO_PAY;

        $exceptionCaught = false;
        try {
            $this->card->authorize($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - capture_mode contains unexpected data.', $e->getMessage());
            $this->assertEquals('40213', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testClickToPayEncryptedRefund()
    {
        $this->card->token = "9113329269393758302";
        $this->card->mobileType = EncyptedMobileType::CLICK_TO_PAY;

        $exceptionCaught = false;
        try {
            $this->card->refund($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Mandatory Fields missing [ request card number] See Developers Guide', $e->getMessage());
            $this->assertEquals('50021', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }


    public function testPayWithApplePayEncrypted()
    {
        $this->markTestSkipped('You need a valid ApplePay token that it is valid only for 60 sec');
        $this->card->token = '{"version":"EC_v1","data":"Jguh2VrQWIpbjtmooCKw2B3yxhBQPwj0tU2FXhtJQatMmRiibhWyVcz1RwolGk2MH+zEL8o4Q3vvXQqb7XUFVaregAGm4mLn5unoTTw6/ltJjozThJ99BuNHo1QhHk6asnlNWy1JTliKq69uGvHcV9ZbBKA4pbUbcsLJu7rB5kakZXvNCLItGAFk2Iue2PMAJMGblTD76FhXbcDTpBFCJeSrupoBoEHk83HgbptaJUzUxsSCHnz0T0BPyLDcMk9cK0nzRowsUYEuH/X+lxjh6yJfkCnL6i6eFjZoonZsZXg37Mnt9kmcIammlHbGtxKXl76AeKieMuPwDMAcMDhnY9xPPM+QZo14dNksBxOV8GWuDLVYSBXmqzZ3GOruYQ29q6gpfZuqIZeiKTYArOhKH0S/ro+aX8fUbPDUP7xAkzc=","signature":"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","header":{"ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEHM7m7LSYllJofL8/T7Ajf6OC1J48iOvXKw4IRCJ5YK+7hkVV0iDwdLijJjtVrCp22EywLXk1VFFeJFU1X/mbMg==","publicKeyHash":"rEYX/7PdO7F7xL7rH0LZVak/iXTrkeU89Ck7E9dGFO4=","transactionId":"c943bc79e49bd3c023988a0681be4df68a30ee64c8360feba1920a320cc29bd0"}}';
        $this->card->mobileType = EncyptedMobileType::APPLE_PAY;

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testPayWithDecryptedFlow()
    {
        $encryptedProviders = [EncyptedMobileType::GOOGLE_PAY, EncyptedMobileType::APPLE_PAY];
        foreach ($encryptedProviders as $encryptedProvider) {
            $this->card->token = '5167300431085507';
            $this->card->mobileType = $encryptedProvider;
            $this->card->cryptogram = '234234234';
            $this->card->eci = '5';

            // process an auto-settle authorization
            $response = $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::DECRYPTED_MOBILE)
                ->execute();

            $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
            $this->assertNotEmpty($response->authorizationCode);
        }
    }

    public function testPayWithGooglePayEncrypted()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncyptedMobileType::GOOGLE_PAY;

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testGooglePayEncrypted_LinkedRefund()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncyptedMobileType::GOOGLE_PAY;

        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $refund = $transaction->refund()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($refund, TransactionStatus::CAPTURED);
    }

    public function testGooglePayEncrypted_Reverse()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncyptedMobileType::GOOGLE_PAY;

        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $reverse = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($reverse, TransactionStatus::REVERSED);
    }

    public function testGooglePayEncrypted_AuthAndReverse()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncyptedMobileType::GOOGLE_PAY;

        $transaction = $this->card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED);

        $reverse = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($reverse, TransactionStatus::REVERSED);
    }

    private function assertTransactionResponse($transaction, $transactionStatus)
    {
        $this->assertNotNull($transaction);
        $this->assertEquals("SUCCESS", $transaction->responseCode);
        $this->assertEquals($transactionStatus, $transaction->responseMessage);
        $this->assertNotEmpty($transaction->transactionId);
    }

    private function assertClickToPayPayerDetails($response){
        $this->assertNotNull($response->payerDetails);
        $this->assertNotNull($response->payerDetails->email);
        $this->assertNotNull($response->payerDetails->billingAddress);
        $this->assertNotNull($response->payerDetails->shippingAddress);
        $this->assertNotNull($response->payerDetails->firstName);
        $this->assertNotNull($response->payerDetails->lastName);
    }

}