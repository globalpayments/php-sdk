<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\EmvLastChipRead;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Enums\GpApi\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\TransactionSummary;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class CreditCardPresentTest extends TestCase
{
    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'i872l4VgZRtSrykvSn8Lkah8RE1jihvT';
        $config->appKey = '9pArW2uWoA8enxKc';
        $config->environment = Environment::TEST;
        $config->channel = Channels::CardPresent;

        return $config;
    }

    public function testCardPresentWithChipTransaction()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $response = $card->charge(19)
            ->withCurrency("EUR")
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testCardPresentWithSwipeTransaction()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $response = $card->authorize(16)
            ->withCurrency("EUR")
            ->withOrderId("124214-214221")
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testRefundOnCardPresentChipCard()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $tag = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';

        $response = $card->refund(19)
            ->withCurrency("EUR")
            ->withOrderId("124214-214221")
            ->withTagData($tag)
            ->execute();

        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testCreditVerification_CardPresent()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $response = $card->verify()
            ->withCurrency("USD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals("VERIFIED", $response->responseMessage);
    }

    public function testCreditVerification_CardPresent_CVNNotMatched()
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "852";
        $card->cardHolderName = "James Mason";

        $response = $card->verify()
            ->withCurrency("USD")
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('NOT_VERIFIED', $response->responseCode);
        $this->assertEquals("NOT_VERIFIED", $response->responseMessage);
    }

    public function testAuthorizationCaptureBatchClose()
    {
        $card = new CreditCardData();
        $card->number = "5425230000004415";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "852";
        $card->cardHolderName = "James Mason";

        $transaction = $card->authorize(2.11)
            ->withCurrency('USD')
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED);

        $capture = $transaction->capture(2.11)
            ->execute();

        $this->assertTransactionResponse($capture, TransactionStatus::CAPTURED);
        $this->assertNotEmpty($capture->batchSummary->batchReference);

        $batch = BatchService::closeBatch($capture->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 2.11);
    }

    public function testBatchClose()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $transaction = $card->charge(2.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(1);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 2.11);
    }

    public function testBatchClose_ChipTransaction()
    {
        $tag = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';

        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $transaction = $card->charge(2.11)
            ->withCurrency('USD')
            ->withTagData($tag)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(1);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 2.11);
    }

    public function testBatchClose_MultipleChargeCreditTrackData()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $transaction = $card->charge(2.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $transaction = $card->charge(2.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 4.22);
    }

    public function testBatchClose_Refund_CreditTrackData()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $transaction = $card->charge(2.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $transaction = $transaction->refund()
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 0);
    }

    public function testBatchClose_Reverse_CreditCardData()
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "123";
        $card->cardHolderName = "James Mason";

        $transaction = $card->authorize(2.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED);

        $transaction = $transaction->reverse()
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::REVERSED);

        sleep(1);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40223', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the batch_id', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_WithIdempotency()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $idempotencyKey = GenerationUtils::getGuid();
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $transaction = $card->charge(2.11)
            ->withCurrency('USD')
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        //TODO - set idempotency key

        $this->assertBatchCloseResponse($batch, 2.11);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
            //TODO - set idempotency key
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertContains('Status Code: DUPLICATE_ACTION - Idempotency Key seen before: ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_WithCardNumberDetails()
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "123";
        $card->cardHolderName = "James Mason";

        $transaction = $card->charge(3.2)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 3.2);
    }

    public function testBatchClose_WithCardNumberDetails_DeclinedTransaction()
    {
        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "800";
        $card->cardHolderName = "James Mason";

        $transaction = $card->charge(3.2)
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('DECLINED', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::DECLINED, $transaction->responseMessage);

        sleep(2);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40017', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_BATCH_ACTION - 9,No transaction associated with batch', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_WithClosedBatchReference()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $transaction = $card->charge(2.11)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 2.11);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40014', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_BATCH_ACTION - 5,No current batch', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_Verify_MissingBatchId()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = EntryMethod::SWIPE;

        $transaction = $card->verify()
            ->withCurrency('USD')
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals('VERIFIED', $transaction->responseMessage);

        sleep(1);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40223', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the batch_id', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_CardNotPresentChannel()
    {
        $config = new GpApiConfig();
        $config->appId = 'i872l4VgZRtSrykvSn8Lkah8RE1jihvT';
        $config->appKey = '9pArW2uWoA8enxKc';
        $config->environment = Environment::TEST;
        $config->channel = Channels::CardNotPresent;

        ServicesContainer::configureService($config);

        $card = new CreditCardData();
        $card->number = "4263970000005262";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "800";
        $card->cardHolderName = "James Mason";

        $transaction = $card->charge(1)
            ->withCurrency('USD')
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(1);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('50002', $e->responseCode);
            $this->assertEquals('Status Code: UNAUTHORIZED_DOWNSTREAM - -2,Authentication error—Verify and correct credentials', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_WithInvalidBatchReference()
    {
        $batchReference = GenerationUtils::getGuid();
        $exceptionCaught = false;
        try {
            BatchService::closeBatch($batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals(sprintf('Status Code: RESOURCE_NOT_FOUND - Batch %s not found at this location.', $batchReference), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    private function assertBatchCloseResponse($batch, $amount)
    {
        $this->assertNotNull($batch);
        $this->assertEquals('CLOSED', $batch->responseMessage);
        $this->assertGreaterThanOrEqual($amount, $batch->batchSummary->totalAmount);
        $this->assertGreaterThanOrEqual(1, $batch->batchSummary->transactionCount);
    }

    private function assertTransactionResponse($transaction, $transactionStatus)
    {
        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals($transactionStatus, $transaction->responseMessage);
    }

    public function testReauthAReversedSale()
    {
        $card = new CreditCardData();
        $card->number = "5425230000004415";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "852";
        $card->cardHolderName = "James Mason";

        $transaction = $card->charge(42)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $transaction->responseMessage);

        $reverse = $transaction->reverse()->execute();

        $this->assertNotNull($reverse);
        $this->assertEquals('SUCCESS', $reverse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverse->responseMessage);

        $response = $reverse->reauthorized()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testReauthorizedAnExistingTransaction()
    {
        $startDate = (new \DateTime())->modify('-29 days')->setTime(0,0,0);
        $endDate = (new \DateTime())->modify('-25 days')->setTime(23,59,59);

        $response = ReportingService::findTransactionsPaged(1, 1)
            ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
            ->where(SearchCriteria::TRANSACTION_STATUS, TransactionStatus::PREAUTHORIZED)
            ->andWith(SearchCriteria::CHANNEL, Channels::CardPresent)
            ->andWith(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(count($response->result) > 0);
        /**
         * @var TransactionSummary $result
         */
        $result = $response->result[0];
        $transaction = new Transaction();
        $transaction->transactionId = $result->transactionId;

        $reverse = $transaction->reverse()->execute();

        $this->assertNotNull($reverse);
        $this->assertEquals('SUCCESS', $reverse->responseCode);
        $this->assertEquals(TransactionStatus::REVERSED, $reverse->responseMessage);

        $response = $reverse->reauthorized()->execute();
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $response->responseMessage);
    }
}