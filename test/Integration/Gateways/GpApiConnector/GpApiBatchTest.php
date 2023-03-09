<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\Services\BatchService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class GpApiBatchTest extends TestCase
{
    /**
     * @var CreditTrackData
     */
    private $creditTrackData;

    /**
     * @var CreditCardData
     */
    private $creditCardData;
    private $currency = 'USD';
    private $amount = 2.11;
    private $tag = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';

    public function setup() : void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->creditTrackData = new CreditTrackData();
        $this->creditTrackData->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $this->creditTrackData->entryMethod = EntryMethod::SWIPE;

        $this->creditCardData = new CreditCardData();
        $this->creditCardData->number = "4263970000005262";
        $this->creditCardData->expMonth = date('m');
        $this->creditCardData->expYear = date('Y', strtotime('+1 year'));
        $this->creditCardData->cvn = "123";
        $this->creditCardData->cardHolderName = "James Mason";
        $this->creditCardData->cardPresent = true;
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
    }

    public function testBatchClose()
    {
        $transaction = $this->creditTrackData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, $this->amount);
    }

    public function testAuthorizationCaptureBatchClose()
    {
        $transaction = $this->creditCardData->authorize($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED);

        $capture = $transaction->capture($this->amount)
            ->execute();

        $this->assertTransactionResponse($capture, TransactionStatus::CAPTURED);
        $this->assertNotEmpty($capture->batchSummary->batchReference);

        sleep(2);

        $batch = BatchService::closeBatch($capture->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, $this->amount);
    }

    public function testBatchClose_ChipTransaction()
    {
        $transaction = $this->creditTrackData->charge($this->amount)
            ->withCurrency($this->currency)
            ->withTagData($this->tag)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, $this->amount);
    }

    public function testBatchClose_ContactlessTransaction()
    {
        $card = new DebitTrackData();
        $card->setValue(';4024720012345671=18125025432198712345?');
        $card->entryMethod = EntryMethod::PROXIMITY;
        $card->pinBlock = 'AFEC374574FC90623D010000116001EE';

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withTagData($this->tag)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, $this->amount);
    }

    public function testBatchClose_MultipleChargeCreditTrackData()
    {
        $transaction = $this->creditTrackData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $transaction = $this->creditTrackData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, $this->amount * 2);
    }

    public function testBatchClose_Refund_CreditTrackData()
    {
        $transaction = $this->creditTrackData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $transaction = $transaction->refund()
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, 0);
    }

    public function testBatchClose_Reverse_CreditCardData()
    {
        $transaction = $this->creditCardData->authorize($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::PREAUTHORIZED);

        $transaction = $transaction->reverse()
            ->withCurrency($this->currency)
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
        $idempotencyKey = GenerationUtils::getGuid();

        $transaction = $this->creditTrackData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = (new ManagementBuilder(TransactionType::BATCH_CLOSE))
            ->withBatchReference($transaction->batchSummary->batchReference)
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertBatchCloseResponse($batch, $this->amount);

        $exceptionCaught = false;
        try {
            (new ManagementBuilder(TransactionType::BATCH_CLOSE))
                ->withBatchReference($transaction->batchSummary->batchReference)
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString('Status Code: DUPLICATE_ACTION - Idempotency Key seen before: ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_WithCardNumberDetails()
    {
        $transaction = $this->creditCardData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, $this->amount);
    }

    public function testBatchClose_WithCardNumberDetails_DeclinedTransaction()
    {
        $this->creditCardData->number = "38865000000705";

        $transaction = $this->creditCardData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('DECLINED', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::DECLINED, $transaction->responseMessage);

        sleep(3);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: ACTION_FAILED - Action failed unexpectedly. Please try again ', $e->getMessage());
            $this->assertEquals('500010', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_WithClosedBatchReference()
    {
        $transaction = $this->creditTrackData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(2);

        $batch = BatchService::closeBatch($transaction->batchSummary->batchReference);
        $this->assertBatchCloseResponse($batch, $this->amount);

        sleep(2);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: ACTION_FAILED - Action failed unexpectedly. Please try again ', $e->getMessage());
            $this->assertEquals('500010', $e->responseCode);
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testBatchClose_Verify_MissingBatchId()
    {
        $transaction = $this->creditTrackData->verify()
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
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        ServicesContainer::configureService($config);

        $transaction = $this->creditCardData->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();
        $this->assertTransactionResponse($transaction, TransactionStatus::CAPTURED);

        sleep(1);

        $exceptionCaught = false;
        try {
            BatchService::closeBatch($transaction->batchSummary->batchReference);
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Merchant configuration does not exist for the following combination: country - US, channel - CNP, currency - USD', $e->getMessage());
            $this->assertEquals('40041', $e->responseCode);
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

}