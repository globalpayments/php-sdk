<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\CI\gpapi;

use GlobalPayments\Api\Entities\Enums\{Channel, TransactionStatus};
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Utils\CiTestingHarness;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
use PHPUnit\Framework\TestCase;

final class GpApiTransactionsTest extends TestCase
{
    const APP_ID = '4gPqnGBkppGYvoE5UX9EWQlotTxGUDbs';
    const APP_KEY = 'FQyJA5VuEQfcji2M';

    private static CiTestingHarness $harness;
    private CreditCardData $card;
    private string $amount = '2.02';
    private string $currency = 'USD';

    public static function setUpBeforeClass(): void
    {
        self::$harness = new CiTestingHarness(
            'https://apis.sandbox.globalpay.com/ucp',
            CiTestingHarness::LOCKED,
            'GpApiTransactionsTest'
        );
    }

    public function setUp(): void
    {
        $now = self::$harness->getCurrentTime();
        $this->card = new CreditCardData();
        $this->card->number = '4263970000005262';
        $this->card->expMonth = (int) $now->format('n');
        $this->card->expYear = (int) $now->format('Y') + 1;
        $this->card->cvn = '123';
        $this->card->cardPresent = true;
    }

    private function configureGpApiService(): void
    {
        $config = new GpApiConfig();
        $config->appId = self::APP_ID;
        $config->appKey = self::APP_KEY;
        $config->channel = Channel::CardNotPresent;
        $config->challengeNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $config->methodNotificationUrl = 'https://ensi808o85za.x.pipedream.net/';
        $config->merchantContactUrl = 'https://enp4qhvjseljg.x.pipedream.net/';
        $config->enableLogging = true;
        $config->requestLogger = new RequestConsoleLogger();
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $accessTokenInfo->riskAssessmentAccountName = 'EOS_RiskAssessment';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->serviceUrl = self::$harness->getTestingUrl();
        ServicesContainer::configureService($config);
        self::$harness->reset();
    }

    private function assertTransactionResponse(Transaction $transaction, TransactionStatus|string $status): void
    {
        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals($status, $transaction->responseMessage);
    }

    public function testPostCapture(): void
    {
        self::$harness->setFunction('GP-API|Transactions|POST Capture');
        $this->configureGpApiService();

        $transaction = $this->card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withClientTransactionId(self::$harness->generateRandomId('postCapture_auth'))
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED);

        // Correction A: capture() returns a ManagementBuilder, which has no
        // withClientTransactionId in the PHP SDK.
        $capture = $transaction->capture($this->amount)->execute();

        $this->assertTransactionResponse($capture, TransactionStatus::CAPTURED);
    }

    public function testPostCharge(): void
    {
        self::$harness->setFunction('GP-API|Transactions|POST Create');
        $this->configureGpApiService();

        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withClientTransactionId(self::$harness->generateRandomId('postCreate'))
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);
    }
}
