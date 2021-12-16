<?php

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use \GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;


class DccCardPresentTest extends TestCase
{
    private $currency = 'EUR';
    private $amount = 15.11;
    /** @var CreditCardData  */
    private $card;
    const DCC_RATE_CONFIG = 'dcc_rate';

    public function setup()
    {
        $this->markTestIncomplete();
        $config = $this->setUpConfig();
        $accessTokenInfo = GpApiService::generateTransactionKey($config);
        $config->accessTokenInfo->accessToken = $accessTokenInfo->accessToken;
        ServicesContainer::configureService($config);
        $dccRateConfig = $this->setUpConfigDcc();
        $dccRateConfig->accessTokenInfo->accessToken = $accessTokenInfo->accessToken;
        ServicesContainer::configureService($dccRateConfig, self::DCC_RATE_CONFIG);

        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";
        $this->card->cardPresent = true;
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'mivbnCh6tcXhrc6hrUxb3SU8bYQPl9pd';
        $config->appKey = 'Yf6MJDNJKiqObYAb';
        $config->environment = Environment::TEST;
        $config->channel = Channel::CardPresent;
        $config->country = 'GB';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'dcc';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
        return $config;
    }

    public function setUpConfigDcc()
    {
        $config = new GpApiConfig();
        $config->appId = 'mivbnCh6tcXhrc6hrUxb3SU8bYQPl9pd';
        $config->appKey = 'Yf6MJDNJKiqObYAb';
        $config->environment = Environment::TEST;
        $config->channel = Channel::CardPresent;
        $config->country = 'GB';
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'dcc_rate';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
        return $config;
    }

    private function getDccDetails()
    {
        return $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute(self::DCC_RATE_CONFIG);
    }

    public function testCreditGetDccInfo()
    {
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $this->getDccDetails();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->withDccRateData($dccDetails->dccRateData)
            ->withClientTransactionId($orderId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('CAPTURED', $response->responseMessage);
    }

    public function testCreditDccRateAuthorize()
    {
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $this->getDccDetails();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $response = $this->card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->withDccRateData($dccDetails->dccRateData)
            ->withClientTransactionId($orderId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $response->responseMessage);
    }

    public function testCreditDccRateRefundStandalone()
    {
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $this->getDccDetails();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $response = $this->card->refund($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->withDccRateData($dccDetails->dccRateData)
            ->withClientTransactionId($orderId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('CAPTURED', $response->responseMessage);
    }

    public function testCreditDccRateReversal()
    {
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $this->getDccDetails();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->withDccRateData($dccDetails->dccRateData)
            ->withClientTransactionId($orderId)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        $reverse = $transaction->reverse()
            ->withDccRateData($transaction->dccRateData)
            ->execute();

        $this->assertNotNull($reverse);
        $this->assertEquals('SUCCESS', $reverse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverse->responseMessage);
    }

    public function testCreditDccRateRefund()
    {
        $this->card->number = '4006097467207025';
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $this->getDccDetails();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->withDccRateData($dccDetails->dccRateData)
            ->withClientTransactionId($orderId)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        $reverse = $transaction->refund()
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();

        $this->assertNotNull($reverse);
        $this->assertEquals('SUCCESS', $reverse->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $reverse->responseMessage);
    }
}