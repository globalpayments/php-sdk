<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class DccCardNotPresentTest extends TestCase
{
    private $currency = 'EUR';
    private $amount = 15.11;
    /** @var CreditCardData */
    private $card;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->number = "4006097467207025";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cardHolderName = "James Mason";
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'dcc';
        $config->accessTokenInfo = $accessTokenInfo;
        return $config;
    }

    public function testCreditGetDccInfo()
    {
        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED, $expectedDccValue);
    }

    public function testCreditDccRateAuthorize()
    {
        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $response = $this->card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($response, TransactionStatus::PREAUTHORIZED, $expectedDccValue);
    }

    public function testCreditDccRateRefundStandalone()
    {
        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $response = $this->card->refund($this->amount)
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED, $expectedDccValue);
    }

    public function testCreditDccRateReversal()
    {
        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED, $expectedDccValue);

        $reverse = $transaction->reverse()
            ->withDccRateData($transaction->dccRateData)
            ->execute();
        $this->assertTransactionResponse($reverse, TransactionStatus::REVERSED, $expectedDccValue);
    }

    public function testCreditDccRateRefund()
    {
        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $transaction = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED, $expectedDccValue);

        $refund = $transaction->refund()
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($refund, TransactionStatus::CAPTURED, $expectedDccValue);
    }

    public function testAuthorizationThenCapture()
    {
        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $transaction = $this->card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED, $expectedDccValue);

        $capture = $transaction->capture()
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($capture, TransactionStatus::CAPTURED, $expectedDccValue);
    }

    public function testCardTokenizationThenPayingWithToken()
    {
        $response = $this->card->tokenize()->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals('ACTIVE', $response->responseMessage);

        $tokenizedCard = new CreditCardData();
        $tokenizedCard->token = $response->token;
        $tokenizedCard->cardHolderName = "James Mason";

        $dccDetails = $tokenizedCard->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $response = $tokenizedCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED, $expectedDccValue);
    }

    public function testCreditGetDccInfo_WithIdempotencyKey()
    {
        $idempotency = GenerationUtils::getGuid();

        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->withIdempotencyKey($idempotency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertDccInfoResponse($dccDetails, $expectedDccValue);

        sleep(2);

        $exceptionCaught = false;
        try {
            $this->card->getDccRate()
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->withIdempotencyKey($idempotency)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertEquals(
                sprintf("Status Code: %s - Idempotency Key seen before: id=%s, status=%s", 'DUPLICATE_ACTION', $dccDetails->transactionId, 'AVAILABLE'
                ), $e->getMessage()
            );
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditGetDccInfo_RateNotAvailable()
    {
        $this->card->number = "4263970000005262";

        $dccDetails = $this->card->getDccRate()
            ->withAmount($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $expectedDccValue = $this->getAmount($dccDetails);
        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('NOT_AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);
        $this->assertEquals($expectedDccValue, $dccDetails->dccRateData->cardHolderAmount);

        sleep(2);

        $response = $this->card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withDccRateData($dccDetails->dccRateData)
            ->execute();
        $this->assertTransactionResponse($response, TransactionStatus::CAPTURED, $this->amount);
        $this->assertEquals($this->amount, $response->dccRateData->cardHolderAmount);
        $this->assertEquals($this->currency, $response->dccRateData->cardHolderCurrency);
    }

    public function testCreditGetDccInfo_InvalidCardNUmber()
    {
        $this->card->number = "4000000000005262";

        $exceptionCaught = false;
        try {
            $this->card->getDccRate()
                ->withAmount($this->amount)
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40090', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - card.number value is invalid. Please check the format and data provided is correct.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditGetDccInfo_WithoutAmount()
    {
        $exceptionCaught = false;
        try {
            $this->card->getDccRate()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields : amount', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditGetDccInfo_WithoutCurrency()
    {
        $exceptionCaught = false;
        try {
            $this->card->getDccRate()
                ->withAmount($this->amount)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields : currency', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    private function assertDccInfoResponse($dccDetails, $expectedDccValue)
    {
        $this->assertNotNull($dccDetails);
        $this->assertEquals('SUCCESS', $dccDetails->responseCode);
        $this->assertEquals('AVAILABLE', $dccDetails->responseMessage);
        $this->assertNotNull($dccDetails->dccRateData);
        $this->assertEquals($expectedDccValue, $dccDetails->dccRateData->cardHolderAmount);
    }

    private function assertTransactionResponse($transaction, $transactionStatus, $expectedDccValue)
    {
        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals($transactionStatus, $transaction->responseMessage);
        if ($transactionStatus !== TransactionStatus::REVERSED) {
            $this->assertEquals($expectedDccValue, $transaction->dccRateData->cardHolderAmount);
        }
    }

    private function getAmount($dccDetails)
    {
        return round($this->amount * $dccDetails->dccRateData->cardHolderRate, 2);
    }

}
