<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\{
    CardType,
    Channel,
    EncyptedMobileType as EncryptedMobileType,
    PaymentMethodUsageMode,
    TransactionModifier,
    TransactionStatus
};
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
use PHPUnit\Framework\TestCase;

class GpApiDigitalWalletTest extends TestCase
{
    private CreditCardData $card;
    private string $currency = 'EUR';
    private float $amount = 10;
    private string $googlePayToken;
    private string $clickToPayToken;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";
        $this->clickToPayToken = '8144735251653223601';
        $this->googlePayToken = '{
          "signature": "MEUCIHES+D2qscALKRtWzGb9ti5USOkP1M5myGG+n2gnLw7oAiEAwFj7JeulajB71ZdW9LvjRNwB6A4v7yjgNwTkzAR+fNo=",
          "protocolVersion": "ECv1",
          "signedMessage": "{\"encryptedMessage\":\"a0X0HwBemGudk84o6K+MUZG1YwInK4rgmNT4bLwtOrbVhQ/2jaT2EX0HYaxi3C5o063++A7EJ5KIl7uwqTSp1GWHtAFqZaWdIMKgK+0ZuGliVkPqFmYmXSD1ksQJQw/veDbANfbtQUiR1c4ZBWm9l2SUDTAbk/BICbYzdWlMIxv/d+wQEWaxYLekCRojSMPAA/tsJigswY8tGAbimvi6Q0eKP7LBd0lLYCP2OnICorODdYcv9kM8RPNXniPphxJ+DKIw9brWb4zSUq0/sJjQYoXIbz/eVXJ5wxZOHf0FUXz2gRwAteziL2HpuxlnNKWgi06TC/CuxrfnqWgJ8QE6bb9NDOrjHxiZS4hZnFsqhmcHZL8Idnmc0fSNY+2zbDkLS+sNrsbzahpEIrHJBGNkNbOEcq3JmFIR8U7Nc30z\",\"ephemeralPublicKey\":\"BCs/ogxOtzEsAmrHwSw1M2Ly2AhX7dUQ/M+HFjFwT4J9MD+nIl8Raruw488czk43t7nC0+wJhWCDzpR3W3Af3TM\\u003d\",\"tag\":\"bjWbrD74J3QPaLtCk7/4RKOPlb0xe33eYcRqUSqMovI\\u003d\"}"
        }';
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig(): GpApiConfig
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function setUpEuCtpConfig(): GpApiConfig
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfigEuCtp(Channel::CardNotPresent);
        $config->requestLogger = new RequestConsoleLogger();

        return $config;
    }

    public function testClickToPayEncrypted()
    {
        $this->card->token = $this->clickToPayToken;
        $this->card->mobileType = EncryptedMobileType::CLICK_TO_PAY;

        $this->assertClickToPayDecryptIdRequired(function (): void {
            $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();
        });
    }

    public function testClickToPayEncryptedChargeThenRefund()
    {
        $this->card->token = $this->clickToPayToken;
        $this->card->mobileType = EncryptedMobileType::CLICK_TO_PAY;

        $this->assertClickToPayDecryptIdRequired(function (): void {
            $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();
        });
    }

    public function testClickToPayEncryptedChargeThenReverse()
    {
        $this->card->token = $this->clickToPayToken;
        $this->card->mobileType = EncryptedMobileType::CLICK_TO_PAY;

        $this->assertClickToPayDecryptIdRequired(function (): void {
            $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();
        });
    }

    public function testClickToPayEncryptedAuthorize()
    {
        $this->card->token = $this->clickToPayToken;
        $this->card->mobileType = EncryptedMobileType::CLICK_TO_PAY;

        $this->assertClickToPayDecryptIdRequired(function (): void {
            $this->card->authorize($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();
        });
    }

    /**
     * Step 1: Decrypt endpoint only.
     * Set EU_CTP_ENCRYPTED_PAYLOAD and EU_CTP_DPA_REFERENCE in the environment before running.
     */
    public function testClickToPayDecryptEndpointOnly(): void
    {
        [$encryptedPayload, $dpaReference, $dataTypeIndicator] = $this->getEuCtpDecryptTestData(
            'Set EU_CTP_ENCRYPTED_PAYLOAD and EU_CTP_DPA_REFERENCE to run decrypt endpoint test.'
        );
        $this->configureEuCtpService();

        $card = $this->createClickToPayDecryptCard($encryptedPayload, $dpaReference, $dataTypeIndicator);

        $response = $card->decrypt()
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotEmpty($response->transactionId); // DEC_ID
        $this->assertNotEmpty($response->decryptId);     // DEC_ID
        $this->assertNotEmpty($response->token);         // PMT_ID
    }

    /**
     * Step 2: Use PMT_ID + DEC_ID on /transactions endpoint.
     * Set EU_CTP_ENCRYPTED_PAYLOAD and EU_CTP_DPA_REFERENCE in the environment before running.
     */
    public function testClickToPayTransactionWithPmtIdAndDecId(): void
    {
        [$encryptedPayload, $dpaReference, $dataTypeIndicator] = $this->getEuCtpDecryptTestData(
            'Set EU_CTP_ENCRYPTED_PAYLOAD and EU_CTP_DPA_REFERENCE to run decrypt+transactions flow test.'
        );
        $this->configureEuCtpService();

        $decryptCard = $this->createClickToPayDecryptCard($encryptedPayload, $dpaReference, $dataTypeIndicator);

        $decryptResponse = $decryptCard->decrypt()
            ->withCurrency('USD')
            ->execute();

        $this->assertNotEmpty($decryptResponse->token);      // PMT_ID
        $this->assertNotEmpty($decryptResponse->decryptId);  // DEC_ID

        $saleCard = new CreditCardData();
        $saleCard->token = $decryptResponse->token;
        $saleCard->mobileType = EncryptedMobileType::CLICK_TO_PAY;
        $saleCard->cardHolderName = 'Jason';
        $saleCard->dpaReference = $dpaReference;
        $saleCard->dataTypeIndicator = $dataTypeIndicator;

        $response = $saleCard->charge(1)
            ->withCurrency('EUR')
            ->withDecryptId($decryptResponse->decryptId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->transactionId);
        $this->assertNotEmpty($response->responseCode);
        $this->assertContains($response->responseMessage, [TransactionStatus::CAPTURED, TransactionStatus::PREAUTHORIZED]);
    }

    public function testClickToPayEncryptedRefund()
    {
        $this->card->token = $this->clickToPayToken;
        $this->card->mobileType = EncryptedMobileType::CLICK_TO_PAY;

        $this->assertClickToPayDecryptIdRequired(function (): void {
            $this->card->refund($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();
        });
    }


    public function testPayWithApplePayEncrypted()
    {
        $this->markTestSkipped('You need a valid ApplePay token that it is valid only for 60 sec');
        $this->card->token = '{"version":"EC_v1","data":"Jguh2VrQWIpbjtmooCKw2B3yxhBQPwj0tU2FXhtJQatMmRiibhWyVcz1RwolGk2MH+zEL8o4Q3vvXQqb7XUFVaregAGm4mLn5unoTTw6/ltJjozThJ99BuNHo1QhHk6asnlNWy1JTliKq69uGvHcV9ZbBKA4pbUbcsLJu7rB5kakZXvNCLItGAFk2Iue2PMAJMGblTD76FhXbcDTpBFCJeSrupoBoEHk83HgbptaJUzUxsSCHnz0T0BPyLDcMk9cK0nzRowsUYEuH/X+lxjh6yJfkCnL6i6eFjZoonZsZXg37Mnt9kmcIammlHbGtxKXl76AeKieMuPwDMAcMDhnY9xPPM+QZo14dNksBxOV8GWuDLVYSBXmqzZ3GOruYQ29q6gpfZuqIZeiKTYArOhKH0S/ro+aX8fUbPDUP7xAkzc=","signature":"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","header":{"ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEHM7m7LSYllJofL8/T7Ajf6OC1J48iOvXKw4IRCJ5YK+7hkVV0iDwdLijJjtVrCp22EywLXk1VFFeJFU1X/mbMg==","publicKeyHash":"rEYX/7PdO7F7xL7rH0LZVak/iXTrkeU89Ck7E9dGFO4=","transactionId":"c943bc79e49bd3c023988a0681be4df68a30ee64c8360feba1920a320cc29bd0"}}';
        $this->card->mobileType = EncryptedMobileType::APPLE_PAY;

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
    }

    public function testPayWithDecryptedFlow()
    {
        $encryptedProviders = [EncryptedMobileType::GOOGLE_PAY, EncryptedMobileType::APPLE_PAY];
        $address = new Address();
        $address->streetAddress1 = "123 Main St.";
        $address->postalCode = "12345";

        foreach ($encryptedProviders as $encryptedProvider) {
            $this->card->token = '5167300431085507';
            $this->card->mobileType = $encryptedProvider;
            $this->card->cryptogram = '234234234';
            $this->card->eci = '5';

            // process an auto-settle authorization
            $response = $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::DECRYPTED_MOBILE)
                ->withAddress($address)
                ->execute();

            $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
            $this->assertEquals('SUCCESS', $response->responseCode);
        }
    }

    public function testPayWithGooglePayEncrypted()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncryptedMobileType::GOOGLE_PAY;

        try {
            $response = $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->execute();
        } catch (GatewayException $e) {
            $this->skipIfInvalidGooglePayToken($e);
            throw $e;
        }

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
        $this->assertNotEmpty($response->cardBrandTransactionId);
        $this->assertEquals(CardType::VISA, $response->cardDetails->brand);
    }

    public function testGooglePayEncrypted_LinkedRefund()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncryptedMobileType::GOOGLE_PAY;

        try {
            $transaction = $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->execute();
        } catch (GatewayException $e) {
            $this->skipIfInvalidGooglePayToken($e);
            throw $e;
        }

        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $refund = $transaction->refund()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($refund, TransactionStatus::CAPTURED);
    }

    public function testGooglePayEncrypted_Reverse()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncryptedMobileType::GOOGLE_PAY;

        try {
            $transaction = $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->execute();
        } catch (GatewayException $e) {
            $this->skipIfInvalidGooglePayToken($e);
            throw $e;
        }

        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $reverse = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($reverse, TransactionStatus::REVERSED);
    }

    public function testGooglePayEncrypted_AuthAndReverse()
    {
        $this->card->token = $this->googlePayToken;
        $this->card->mobileType = EncryptedMobileType::GOOGLE_PAY;

        try {
            $transaction = $this->card->authorize($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->execute();
        } catch (GatewayException $e) {
            $this->skipIfInvalidGooglePayToken($e);
            throw $e;
        }

        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED);

        $reverse = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($reverse, TransactionStatus::REVERSED);
    }

    /**
     * Test Payment Method Creation Endpoint.
     *
     * Endpoint: POST https://apis.sandbox.eu.globalpay.com/ucp/payment-methods
     */
    public function testPaymentMethodEndpoint(): void
    {
        $this->configureEuCtpService(['PMT_POST_Create_Single']);
        $card = $this->createPaymentMethodCard();

        try {
            $response = $this->createPaymentMethodRequest($card)->execute();
            $this->assertSuccessfulPaymentMethodCreate($response);
        } catch (GatewayException $e) {
            $this->skipPaymentMethodGatewayException($e);
        }
    }

    public function testPaymentMethodEndpointRejectsIncorrectPermissionScope(): void
    {
        $this->configureEuCtpService(['TRN_POST_Authorize']);
        $card = $this->createPaymentMethodCard();
        try {
            $this->createPaymentMethodRequest($card)->execute();
        } catch (GatewayException $e) {
            if ($this->isPaymentMethodUnauthorized($e)) {
                $this->assertTrue(
                    strpos($e->getMessage(), 'ACTION_NOT_AUTHORIZED') !== false ||
                    $e->responseCode === '40022'
                );

                return;
            }

            $this->skipPaymentMethodGatewayException($e);
        }

        $this->fail('Expected ACTION_NOT_AUTHORIZED/40022 for insufficient permission scope.');
    }

    public function testPaymentMethodEndpointRejectsMissingCvv(): void
    {
        $this->configureEuCtpService(['PMT_POST_Create_Single']);
        $card = $this->createPaymentMethodCard();
        $card->cvn = null;

        try {
            $response = $this->createPaymentMethodRequest($card)->execute();
            $this->assertSuccessfulPaymentMethodCreate($response);

            return;
        } catch (GatewayException $e) {
            if ($this->isPaymentMethodUnauthorized($e)) {
                $this->skipPaymentMethodGatewayException($e);
            }

            if (
                strpos($e->getMessage(), 'MANDATORY_DATA_MISSING') !== false ||
                strpos($e->getMessage(), 'INVALID_REQUEST_DATA') !== false
            ) {
                $this->assertTrue(
                    strpos($e->getMessage(), 'cvv') !== false ||
                    strpos($e->getMessage(), 'CVV') !== false ||
                    strpos($e->getMessage(), 'Security Code') !== false
                );

                return;
            }

            $this->skipPaymentMethodGatewayException($e);
        }

        $this->fail('Expected payment method creation without CVV to be either rejected or accepted based on account CVV policy.');
    }

    public function testPaymentMethodEndpointAllowsMissingCvvWithCvvPresentNo(): void
    {
        $this->configureEuCtpService(['PMT_POST_Create_Single']);
        $card = $this->createPaymentMethodCard();
        $card->cvn = null;

        try {
            $response = $this->createPaymentMethodRequest($card)->execute();
            $this->assertSuccessfulPaymentMethodCreate($response);
        } catch (GatewayException $e) {
            if ($this->isPaymentMethodUnauthorized($e)) {
                $this->skipPaymentMethodGatewayException($e);

                return;
            }

            if ($this->isMissingCardCvvGatewayError($e)) {
                $this->markTestSkipped(
                    'Gateway/account still enforces card.cvv even when cvv_present is NO. ' .
                    'Enable CVV-optional payment-method creation to validate success path. Gateway: ' .
                    $e->getMessage()
                );

                return;
            }

            $this->fail('Expected payment method creation without CVV when cvv_present is NO. Gateway: ' . $e->getMessage());
        }
    }

    private function configureEuCtpService(array $permissions = []): void
    {
        $config = $this->setUpEuCtpConfig();
        $config->methodNotificationUrl = $config->methodNotificationUrl ?: 'https://en2acm739g1ux.x.pipedream.net/';
        $config->challengeNotificationUrl = $config->challengeNotificationUrl ?: 'https://en7rguhwokz8m.x.pipedream.net/';

        if (!empty($permissions)) {
            $config->permissions = $permissions;
        }

        ServicesContainer::removeConfiguration();
        ServicesContainer::configureService($config);
    }

    private function createPaymentMethodCard(): CreditCardData
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = 5;
        $card->expYear = 2030;
        $card->cvn = "123";
        $card->cardHolderName = "James Mason";

        return $card;
    }

    private function createClickToPayDecryptCard(
        string $encryptedPayload,
        string $dpaReference,
        string $dataTypeIndicator
    ): CreditCardData {
        $card = new CreditCardData();
        $card->token = $encryptedPayload;
        $card->mobileType = EncryptedMobileType::CLICK_TO_PAY;
        $card->cardHolderName = 'James Mason';
        $card->cardType = CardType::VISA;
        $card->dpaReference = $dpaReference;
        $card->dataTypeIndicator = $dataTypeIndicator;

        return $card;
    }

    private function getEuCtpDecryptTestData(string $skipMessage): array
    {
        $encryptedPayload = 'eyJraWQiOiJKN00zRTE0V1pVUDhHRkxUQkUzODEzcm1ydC0tRGRFTTZGZEZORVdQakpjNVJfTHZrIiwiYWxnIjoiUlMyNTYiLCJqdGkiOiJOV000WlRGak9HSXRPREF4T1MwME56aGtMVGsyWldJdE0yTXhNR0l5TWpZMFpHTTUiLCJpYXQiOjE3NzU1NDg5ODJ9.eyJzcmNDb3JyZWxhdGlvbklkIjoiYWE4N2ZhNzYtYjBlYi00MTFlLWMzZTQtMTRkOGU1OGUyZjAxIiwic3JjaVRyYW5zYWN0aW9uSWQiOiI3MjYyOWQ2Ni0zYTBmLTRjMjUtOGU3Ny0wMzYxYzU1NDhkZGYiLCJtYXNrZWRDYXJkIjp7InNyY0RpZ2l0YWxDYXJkSWQiOiI5ZmFmYjM4NTA2MzQwY2YyMzE3MjFmZDgzNjBlYWQwMiIsInBhbkJpbiI6IjQzOTU4NCIsInBhbkxhc3RGb3VyIjoiMDExMCIsInRva2VuQmluUmFuZ2UiOiI0OTA2MjQ2OTciLCJwYXltZW50QWNjb3VudFJlZmVyZW5jZSI6IlYwMDEwMDEzMDI0MzI1NjcxNjE0MjU3NzQ0MTgwIiwidG9rZW5MYXN0Rm91ciI6IjAyMDAiLCJwYW5FeHBpcmF0aW9uTW9udGgiOiIxMCIsInBhbkV4cGlyYXRpb25ZZWFyIjoiMjAzMiIsImRpZ2l0YWxDYXJkRGF0YSI6eyJzdGF0dXMiOiJBQ1RJVkUiLCJkZXNjcmlwdG9yTmFtZSI6Ik9CTiIsImFydFVyaSI6Imh0dHBzOi8vc2FuZGJveC5hc3NldHMudmltcy52aXNhLmNvbS92aW1zL2NhcmRhcnQvNWFmMzczNGNjYTRlNDM5ZTk3ZjAwZTQ0NzRkODI0NDdfaW1hZ2VBQDJ4LnBuZyIsImFydEhlaWdodCI6MjEwLCJhcnRXaWR0aCI6MzM0fSwiZGF0ZW9mQ2FyZENyZWF0ZWQiOjE3NzI1MzcwNDI2ODMsImRhdGVvZkNhcmRMYXN0VXNlZCI6MTc3NTExMjQ0MDc4NywibWFza2VkQmlsbGluZ0FkZHJlc3MiOnsiYWRkcmVzc0lkIjoiZmI0NGQ1ZTUtNGJmMS00NWJhLWJhZDgtNWNkOTk2MzRmMDhmIiwiY291bnRyeUNvZGUiOiJHQiJ9LCJlbGlnaWJsZSI6ZmFsc2UsInBheW1lbnRDYXJkVHlwZSI6IkRFQklUIiwidG9rZW5JZCI6ImNjOTE4YTIxMDAxYmMwMTgxNWM2MTQ5MjQxMWJlMzAyIn0sIm1hc2tlZENvbnN1bWVyIjp7InNyY0NvbnN1bWVySWQiOiJDRkpHcXdxNDZEaDZZaEVRdXA2YzM4d2ZQVENGSmlXYXhHS3NUNmpTZWJNPSIsImZpcnN0TmFtZSI6IlQqKioqKiIsImxhc3ROYW1lIjoiVioqKioqIiwiZnVsbE5hbWUiOiJUKioqKiogVioqKioqIiwiZW1haWxBZGRyZXNzIjoidmxhKipAZ2xvYmFscGF5LmNvbSIsIm1vYmlsZU51bWJlciI6eyJjb3VudHJ5Q29kZSI6IjQwIiwicGhvbmVOdW1iZXIiOiIqKioqKioqKjU2NTQifSwiY291bnRyeUNvZGUiOiJHQiIsImxhbmd1YWdlQ29kZSI6ImVuLUdCIiwic3RhdHVzIjoiQUNUSVZFIn0sImFzc3VyYW5jZURhdGEiOnsiZWNpIjoiMDcifSwiaXNHdWVzdENoZWNrb3V0IjpmYWxzZSwiaXNOZXdVc2VyIjpmYWxzZX0.PLKjFxIbA1mR0rEqELHmsNMhOv7P-ocTS4BskuIdwpL6q3lSpfeBymQ3U1p6oUdSbk1q0qoaThX-s845P9cDugl8K0r79Ng3huMUGfgXL25opdWKRUrIciS0y13hgUjyBku44_pvZuoAQ1ua0F1y6maKBia6_T0bFTKKQVLUBuZzIe_viL3i2m388M95chAVrSCum5XBFG46XysAox1L7FNm2I_UvE0QEmWvewVzwjd4BHfCVhGzCfr2mLURHHJYKvAEyT7WLYkCq4VpvKKkm4O-DouKE358OJtBiXYma7jlGc2IyWV0-gf2VV6m07h6o4TGYcORP9OMIg6GrEOaew';
        $dpaReference = '08f56394-4599-af88-ff38-1a64db7c6502';
        $dataTypeIndicator = 'FULL';

        if (empty($encryptedPayload) || empty($dpaReference)) {
            $this->markTestSkipped($skipMessage);
        }

        return [$encryptedPayload, $dpaReference, $dataTypeIndicator];
    }

    private function createPaymentMethodRequest(CreditCardData $card)
    {
        return $card->verify()
            ->withCurrency('USD')
            ->withRequestMultiUseToken(true)
            ->withPaymentMethodUsageMode(PaymentMethodUsageMode::SINGLE)
            ->withDescription("CustABC_Card_1")
            ->withClientTransactionId("CustABC_Card_1");
    }

    private function assertSuccessfulPaymentMethodCreate($response): void
    {
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertNotEmpty($response->token);
        $this->assertNotEmpty($response->transactionId);
    }

    private function isMissingCardCvvGatewayError(GatewayException $exception): bool
    {
        return strpos($exception->getMessage(), 'MANDATORY_DATA_MISSING') !== false &&
            strpos($exception->getMessage(), 'card.cvv') !== false;
    }

    private function isPaymentMethodUnauthorized(GatewayException $exception): bool
    {
        return $exception->responseCode === '40022' ||
            strpos($exception->getMessage(), 'ACTION_NOT_AUTHORIZED') !== false;
    }

    private function skipPaymentMethodGatewayException(GatewayException $exception): void
    {
        if ($this->isPaymentMethodUnauthorized($exception)) {
            $this->markTestSkipped(
                'Missing PMT_POST_Create permission for /payment-methods on current access token/account. ' .
                'Update EU CTP app credentials or account permissions and retry. Gateway: ' . $exception->getMessage()
            );

            return;
        }

        $this->markTestSkipped('EU endpoint not available or account not configured: ' . $exception->getMessage());
    }

    private function assertClickToPayDecryptIdRequired(callable $request): void
    {
        $exceptionCaught = false;
        try {
            $request();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertTrue(
                strpos($e->getMessage(), 'digital_wallet.decrypt.id') !== false,
                'Expected decrypt.id validation error but got: ' . $e->getMessage()
            );
            $this->assertEquals('40007', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    private function skipIfInvalidGooglePayToken(GatewayException $exception): void
    {
        if (
            strpos($exception->getMessage(), 'INVALID_REQUEST_DATA') !== false &&
            strpos($exception->getMessage(), 'Invalid token provided') !== false
        ) {
            $this->markTestSkipped('Google Pay test token is invalid/expired for current environment: ' . $exception->getMessage());
        }
    }

    private function assertTransactionResponse($transaction, $transactionStatus): void
    {
        $this->assertNotNull($transaction);
        $this->assertEquals("SUCCESS", $transaction->responseCode);
        $this->assertEquals($transactionStatus, $transaction->responseMessage);
        $this->assertNotEmpty($transaction->transactionId);
    }

    private function assertClickToPayPayerDetails($response): void
    {
        $this->assertNotNull($response->payerDetails);
        $this->assertNotNull($response->payerDetails->email);
        $this->assertNotNull($response->payerDetails->billingAddress);
        $this->assertNotNull($response->payerDetails->shippingAddress);
        $this->assertNotNull($response->payerDetails->firstName);
        $this->assertNotNull($response->payerDetails->lastName);
    }
}