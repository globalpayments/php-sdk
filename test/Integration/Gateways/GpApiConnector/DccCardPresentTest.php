<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\Services\GpApiService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;

class DccCardPresentTest extends TestCase
{
    private $currency = 'EUR';
    private $amount = 15.11;
    /** @var CreditCardData */
    private $card;
    const DCC_RATE_CONFIG = 'dcc_rate';

    public function setup(): void
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

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'dcc';
        $config->accessTokenInfo = $accessTokenInfo;
        return $config;
    }

    public function setUpConfigDcc()
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
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

    public function testCreditGetDccInfo_CreditTrackData()
    {
        $creditTrackData = new CreditTrackData();
        $creditTrackData->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $creditTrackData->entryMethod = EntryMethod::SWIPE;
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $this->getDccDetails();
        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('NOT_AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $exceptionCaught = false;
        try {
            $creditTrackData->charge($this->amount)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->withDccRateData($dccDetails->dccRateData)
                ->withClientTransactionId($orderId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - 37,Request expects the following field  payer_amount payer_currency exchange_rate commission_percentage  from the Merchant.', $e->getMessage());
            $this->assertEquals('40211', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditGetDccInfo_DebitTrackData()
    {
        $debitCard = new DebitTrackData();
        $debitCard->value = '%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?';
        $debitCard->pinBlock = '32539F50C245A6A93D123412324000AA';
        $debitCard->entryMethod = EntryMethod::SWIPE;
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $this->getDccDetails();
        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('NOT_AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $exceptionCaught = false;
        try {
            $debitCard->charge($this->amount)
                ->withCurrency($this->currency)
                ->withDccRateData($dccDetails->dccRateData)
                ->withClientTransactionId($orderId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: UNAUTHORIZED_DOWNSTREAM - -21,Unauthorized', $e->getMessage());
            $this->assertEquals('50001', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }
}