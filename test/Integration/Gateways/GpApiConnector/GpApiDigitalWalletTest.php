<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Builders\HPPBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiAuthorizationRequestBuilder;
use GlobalPayments\Api\Entities\Enums\{
    CaptureMode,
    CardType,
    ChallengeRequestIndicator,
    Channel,
    DataResidency,
    EncyptedMobileType,
    Environment,
    ExemptStatus,
    HPPAllowedPaymentMethods,
    HPPDigitalWalletProvider,
    HPPStorageModes,
    HPPTypes,
    PhoneNumberType,
    TransactionModifier,
    TransactionStatus,
};
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\{Address, PayerDetails, PhoneNumber};
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\ArrayUtils;
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

    public function testClickToPayEncrypted()
    {
        $this->card->token = $this->clickToPayToken;
        $this->card->mobileType = EncyptedMobileType::CLICK_TO_PAY;

        try {
            $response = $this->card->charge($this->amount)
                ->withCurrency($this->currency)
                ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
                ->withMaskedDataResponse(true)
                ->execute();

            $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
            $this->assertClickToPayPayerDetails($response);
        } catch (GatewayException $e) {
            $this->assertStringContainsString('Status Code: MANDATORY_DATA_MISSING', $e->getMessage());
            $this->assertStringContainsString('digital_wallet.decrypt.id', $e->getMessage());
            $this->assertContains($e->responseCode, ['50027', '40007']);
        }
    }

    public function testClickToPayEncryptedChargeThenRefund()
    {
        $this->card->token = $this->clickToPayToken;
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
        $this->card->token = $this->clickToPayToken;
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
        $this->card->token = $this->clickToPayToken;
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
            $errorMessage = $e->getMessage();
            $this->assertTrue(
                $errorMessage === 'Status Code: INVALID_REQUEST_DATA - capture_mode contains unexpected data.' ||
                (str_contains($errorMessage, 'Status Code: MANDATORY_DATA_MISSING') &&
                    str_contains($errorMessage, 'digital_wallet.decrypt.id'))
            );
            $this->assertContains($e->responseCode, ['40213', '50027', '40007']);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testClickToPayEncryptedRefund()
    {
        $this->card->token = $this->clickToPayToken;
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
        $this->card->mobileType = EncyptedMobileType::GOOGLE_PAY;

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->execute();

        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED);
        $this->assertNotEmpty($response->cardBrandTransactionId);
        $this->assertEquals(CardType::VISA, $response->cardDetails->brand);
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

    public function testClickToPayRequestUsesConfiguredAccountNameFallback()
    {
        $config = $this->createClickToPayRequestConfig();
        $config->transactionAccountName = 'Transaction_Processing';
        $config->accessTokenInfo->transactionProcessingAccountName = null;
        $config->accessTokenInfo->transactionProcessingAccountID = null;

        $requestBody = $this->buildClickToPayRequestBody($config);

        $this->assertArrayHasKey('account_name', $requestBody);
        $this->assertSame('Transaction_Processing', $requestBody['account_name']);
    }

    public function testClickToPayRequestOmitsAccountNameWhenMissingInTokenAndConfig()
    {
        $config = $this->createClickToPayRequestConfig();
        $config->transactionAccountName = '';
        $config->accessTokenInfo->transactionProcessingAccountName = null;
        $config->accessTokenInfo->transactionProcessingAccountID = null;

        $requestBody = $this->buildClickToPayRequestBody($config);

        $this->assertArrayNotHasKey('account_name', $requestBody);
    }

    public function testCreateHPPUrlWithClickToPayEU(): void
    {
        ServicesContainer::configureService($this->createEuClickToPayConfig());

        try {
            $reference = 'INT_TEST_CTP_EU_' . uniqid();
            $response = $this->createEuClickToPayBuilder($reference)->execute();

            $this->assertNotNull($response);
            $this->assertNotNull($response->payByLinkResponse);
            $this->assertNotNull($response->payByLinkResponse->url);
            $this->assertNotNull($response->payByLinkResponse->id);
            $this->assertStringContainsString('https://', $response->payByLinkResponse->url);
            $this->assertMatchesRegularExpression('/^https:\/\/.*\/hpp\/.*/', $response->payByLinkResponse->url);
        } finally {
            ServicesContainer::configureService($this->setUpConfig());
        }
    }

    private function createClickToPayRequestConfig(): GpApiConfig
    {
        $config = new GpApiConfig();
        $config->channel = Channel::CardNotPresent;
        $config->country = 'US';
        $config->accessTokenInfo = new AccessTokenInfo();

        return $config;
    }

    private function buildClickToPayRequestBody(GpApiConfig $config): array
    {
        $card = new CreditCardData();
        $card->token = $this->clickToPayToken;
        $card->mobileType = EncyptedMobileType::CLICK_TO_PAY;

        $authBuilder = $card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE);

        $request = (new GpApiAuthorizationRequestBuilder())->buildRequest($authBuilder, $config);

        return ArrayUtils::array_remove_empty((array) $request->requestBody);
    }

    private function createEuClickToPayConfig(): GpApiConfig
    {
        $euConfig = new GpApiConfig();
        $euConfig->appId = BaseGpApiTestConfig::EU_CTP_APP_ID;
        $euConfig->appKey = BaseGpApiTestConfig::EU_CTP_APP_KEY;
        $euConfig->environment = Environment::TEST;
        $euConfig->dataResidency = DataResidency::EU;
        $euConfig->country = 'US';
        $euConfig->channel = Channel::CardNotPresent;
        $euConfig->accessTokenInfo = new AccessTokenInfo();
        $euConfig->accessTokenInfo->transactionProcessingAccountName = 'GPECOM_Transaction_Processing_CNP';
        $euConfig->requestLogger = new RequestConsoleLogger();

        return $euConfig;
    }

    private function createEuClickToPayBuilder(string $reference): HPPBuilder
    {
        $payer = new PayerDetails();
        $payer->firstName = 'John';
        $payer->lastName = 'Doe';
        $payer->name = 'John Doe';
        $payer->email = 'john.doe+test@example.com';
        $payer->status = 'NEW';

        $phone = new PhoneNumber('44', '07987654321', PhoneNumberType::MOBILE);

        $billingAddress = new Address();
        $billingAddress->streetAddress1 = '123 Test Street';
        $billingAddress->city = 'London';
        $billingAddress->state = 'LND';
        $billingAddress->postalCode = 'SW1A 1AA';
        $billingAddress->country = 'GB';
        $billingAddress->countryCode = 'GB';

        $shippingAddress = new Address();
        $shippingAddress->streetAddress1 = '456 Shipping Street';
        $shippingAddress->city = 'Manchester';
        $shippingAddress->state = 'MAN';
        $shippingAddress->postalCode = 'M1 1AA';
        $shippingAddress->country = 'GB';
        $shippingAddress->countryCode = 'GB';

        $payer->billingAddress = $billingAddress;
        $payer->shippingAddress = $shippingAddress;
        $payer->mobilePhone = $phone;
        $payer->shippingPhone = $phone;

        return HPPBuilder::create()
            ->withType(HPPTypes::HOSTED_PAYMENT_PAGE)
            ->withName('Mobile Bill Payment')
            ->withDescription('map_COMMENT1')
            ->withReference($reference)
            ->withExpirationDate((new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->modify('+30 days')->format('Y-m-d\TH:i:s\Z'))
            ->withSubmitButtonLabel('SUBMIT NOW')
            ->withAmount('100000')
            ->withCurrency('USD')
            ->withPayer($payer)
            ->withNotifications(
                'https://webhook.site/return',
                'https://webhook.site/status',
                'https://webhook.site/cancel'
            )
            ->withBillingAddress($billingAddress)
            ->withShippingPhone($phone)
            ->withShippingAddress($shippingAddress)
            ->withTransactionConfig(Channel::CardNotPresent, 'US', CaptureMode::AUTO, [HPPAllowedPaymentMethods::CARD])
            ->withOrderReference('123456789')
            ->withCurrencyConversionMode(true)
            ->withAddressMatchIndicator(true)
            ->withSurcharge('DEBIT', '100001')
            ->withSurcharge('CREDIT', '100002')
            ->withSurcharge('COMMERCIAL', '100003')
            ->withAuthentication(
                ChallengeRequestIndicator::CHALLENGE_MANDATED,
                ExemptStatus::LOW_VALUE,
                true
            )
            ->withApm(true, true)
            ->withEntryMode('ECOM')
            ->withPaymentMethodConfig(HPPStorageModes::ALWAYS)
            ->withShipping(true, '100')
            ->withHPPDisplayConfiguration(
                'https://www.example.com',
                'https://www.example.com'
            )
            ->withInstallments(
                'MERCHANT_FUNDED',
                24,
                '100000'
            )
            ->withDigitalWallets([
                HPPDigitalWalletProvider::CLICK_TO_PAY,
            ]);
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