<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\GpApiService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class DccCardPresentTest extends TestCase
{
    private string $currency = 'EUR';
    private float $amount = 0.10;
    /** @var CreditCardData */
    private CreditCardData $card;

    public function setup(): void
    {
        $config = $this->setUpConfig();
        $config->country = 'GB';
        $accessTokenInfo = GpApiService::generateTransactionKey($config);
        $config->accessTokenInfo->accessToken = $accessTokenInfo->accessToken;
        ServicesContainer::configureService($config);

        $this->card = new CreditCardData();
        $this->card->number = "4242424242424242";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";
        $this->card->cardPresent = true;
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig(): GpApiConfig
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'dcc_p';
        $config->accessTokenInfo = $accessTokenInfo;
        return $config;
    }

    private function getDccDetails(): Transaction
    {
        return $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();
    }

    public function testCreditGetDccInfo()
    {
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
        $creditTrackData->setTrackData(';4761739001010036=25122011184404889?');
        $creditTrackData->entryMethod = EntryMethod::PROXIMITY;
        $tagData = "9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001";
        $orderId = GenerationUtils::generateOrderId();

        $dccDetails = $creditTrackData->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);

        $transaction =   $creditTrackData->charge($this->amount)
                ->withCurrency($this->currency)
                ->withAllowDuplicates(true)
                ->withDccRateData($dccDetails->dccRateData)
                ->withClientTransactionId($orderId)
                ->withTagData($tagData)
                ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);
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