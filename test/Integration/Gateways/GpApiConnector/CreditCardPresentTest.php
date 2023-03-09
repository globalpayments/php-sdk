<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\EmvLastChipRead;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\LodgingItemType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodProgram;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\StoredCredentialSequence;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\LodgingData;
use GlobalPayments\Api\Entities\LodgingItems;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class CreditCardPresentTest extends TestCase
{
    private $currency = 'USD';
    private $amount = 15.11;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardPresent);
    }

    public function testCardPresentWithChipTransaction()
    {
        $card = $this->initCreditTrackData();

        $response = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', TransactionStatus::CAPTURED);
    }

    public function testCardPresentWithSwipeTransaction()
    {
        $card = $this->initCreditTrackData();

        $response = $card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withOrderId("124214-214221")
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', TransactionStatus::PREAUTHORIZED);
    }

    public function testRefundOnCardPresentChipCard()
    {
        $card = $this->initCreditTrackData();
        $tag = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';

        $response = $card->refund($this->amount)
            ->withCurrency($this->currency)
            ->withOrderId("124214-214221")
            ->withTagData($tag)
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', TransactionStatus::CAPTURED);
    }

    public function testCardPresentWithManualEntryModeTransaction()
    {
        $card = $this->initCreditCardData();

        $response = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', TransactionStatus::CAPTURED);
    }

    public function testCreditVerification_CardPresent()
    {
        $card = $this->initCreditTrackData();

        $response = $card->verify()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', 'VERIFIED');
    }

    public function testCreditVerification_CardPresent_CVNNotMatched()
    {
        $card = $this->initCreditCardData();
        $card->number = "30450000000985";

        $response = $card->verify()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($response, 'NOT_VERIFIED', 'NOT_VERIFIED');
    }

    public function testReauthAReversedSale()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($transaction, 'SUCCESS', TransactionStatus::CAPTURED);

        $reverse = $transaction->reverse()
            ->execute();

        $this->assertTransactionResponse($reverse, 'SUCCESS', TransactionStatus::REVERSED);

        $response = $reverse->reauthorized()
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', TransactionStatus::CAPTURED);
    }

    public function testReauthAReversedAuthorizedTransaction()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($transaction, 'SUCCESS', TransactionStatus::PREAUTHORIZED);

        $reverse = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($reverse, 'SUCCESS', TransactionStatus::REVERSED);

        $reauthorized = $reverse->reauthorized()
            ->execute();

        $this->assertTransactionResponse($reauthorized, 'SUCCESS', TransactionStatus::PREAUTHORIZED);
    }

    public function testReauthorizedAnExistingTransaction()
    {
        $startDate = (new \DateTime())->modify('-29 days')->setTime(0, 0, 0);

        $response = ReportingService::findTransactionsPaged(1, 1)
            ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::TRANSACTION_STATUS, TransactionStatus::PREAUTHORIZED)
            ->andWith(SearchCriteria::CHANNEL, Channel::CardPresent)
            ->andWith(SearchCriteria::START_DATE, $startDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertCount(1, $response->result);
        /**
         * @var TransactionSummary $result
         */
        $result = $response->result[0];
        $transaction = new Transaction();
        $transaction->transactionId = $result->transactionId;

        $reverse = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($reverse, 'SUCCESS', TransactionStatus::REVERSED);

        $response = $reverse->reauthorized($result->amount)
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', TransactionStatus::PREAUTHORIZED);
    }

    public function testReauthAReversedAuthorizedTransaction_WithIdempotencyKey()
    {
        $idempotencyKey = GenerationUtils::getGuid();
        $card = $this->initCreditCardData();

        $transaction = $card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($transaction, 'SUCCESS', TransactionStatus::PREAUTHORIZED);

        $reverse = $transaction->reverse()
            ->withCurrency($this->currency)
            ->execute();

        $this->assertTransactionResponse($reverse, 'SUCCESS', TransactionStatus::REVERSED);

        $reauthorized = $reverse->reauthorized()
            ->withIdempotencyKey($idempotencyKey)
            ->execute();

        $this->assertTransactionResponse($reauthorized, 'SUCCESS', TransactionStatus::PREAUTHORIZED);

        $exceptionCaught = false;
        try {
            $reverse->reauthorized()
                ->withIdempotencyKey($idempotencyKey)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40039', $e->responseCode);
            $this->assertStringContainsString(sprintf('Status Code: DUPLICATE_ACTION - Idempotency Key seen before: id=%s', $reauthorized->transactionId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testReauthAReversedSale_WithAmount()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($transaction, 'SUCCESS', TransactionStatus::CAPTURED);

        $reverse = $transaction->reverse()
            ->execute();

        $this->assertTransactionResponse($reverse, 'SUCCESS', TransactionStatus::REVERSED);
        $this->assertEquals($reverse->transactionId, $transaction->transactionId);

        $response = $reverse->reauthorized(15)
            ->execute();

        $this->assertTransactionResponse($response, 'SUCCESS', TransactionStatus::CAPTURED);
        $this->assertEquals(15, $response->balanceAmount);
        $this->assertNotEquals($reverse->transactionId, $response->transactionId);
    }

    public function testReauthAReversedRefund()
    {
        $card = $this->initCreditCardData();

        $refund = $card->refund($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($refund, 'SUCCESS', TransactionStatus::CAPTURED);

        $exceptionCaught = false;
        try {
            $refund->reauthorized()->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40213', $e->responseCode);
            $this->assertStringContainsString('Status Code: INVALID_REQUEST_DATA', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testReauthASale_WithCapturedStatus()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse($transaction, 'SUCCESS', TransactionStatus::CAPTURED);

        $exceptionCaught = false;
        try {
            $transaction->reauthorized()->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40044', $e->responseCode);
            $this->assertStringContainsString('Status Code: INVALID_REQUEST_DATA - 36, Invalid original transaction for reauthorization-This error is returned from a CreditAuth or CreditSale if the original transaction referenced by GatewayTxnId cannot be found. This is typically because the original does not meet the criteria for the sale or authorization by GatewayTxnID. This error can also be returned if the original transaction is found, but the card number has been written over with nulls after 30 days.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testReauthASale_NonExistentId()
    {
        $exceptionCaught = false;

        try {
            $transaction = new Transaction();
            $transaction->transactionId = GenerationUtils::getGuid();
            $transaction->reauthorized()->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40008', $e->responseCode);
            $this->assertStringContainsString(sprintf('Status Code: RESOURCE_NOT_FOUND - Transaction %s not found at this location.', $transaction->transactionId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCreditSaleContactlessChip()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $tagData = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';
        $card->entryMethod = EntryMethod::PROXIMITY;

        $response = $card->charge(10)
            ->withCurrency("USD")
            ->withAllowDuplicates(true)
            ->withTagData($tagData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testCreditSaleContactlessSwipe()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $tagData = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390191FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';
        $response = $card->charge(10)
            ->withCurrency("USD")
            ->withAllowDuplicates(true)
            ->withTagData($tagData)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
    }

    public function testAdjustSaleTransaction()
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $tagData = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';
        $card->entryMethod = EntryMethod::PROXIMITY;

        $transaction = $card->charge(10)
            ->withCurrency("USD")
            ->withAllowDuplicates(true)
            ->withTagData($tagData)
            ->execute();

        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );

        $response = $transaction->edit()
            ->withAmount(10.01)
            ->withTagData($tagData)
            ->withGratuity(5.01)
            ->execute();

        $this->assertTransactionResponse(
            $response,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );
    }

    public function testAdjustAuthTransaction()
    {
        $card = $this->initCreditTrackData(EntryMethod::PROXIMITY);
        $tagData = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';

        $transaction = $card->authorize($this->amount)
            ->withCurrency($this->currency)
            ->withTagData($tagData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::PREAUTHORIZED
        );

        $response = $transaction->edit()
            ->withAmount(10.01)
            ->withTagData($tagData)
            ->withGratuity(5.01)
            ->withMultiCapture(StoredCredentialSequence::FIRST, 1)
            ->execute();
        $this->assertTransactionResponse(
            $response,
            'SUCCESS',
            TransactionStatus::PREAUTHORIZED
        );
    }

    public function testAdjustSaleTransaction_AdjustAmountHigherThanSale()
    {
        $card = $this->initCreditTrackData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );

        $response = $transaction->edit()
            ->withAmount($this->amount + 2)
            ->execute();
        $this->assertTransactionResponse(
            $response,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );
    }

    public function testAdjustSaleTransaction_AdjustOnlyTag()
    {
        $card = $this->initCreditTrackData(EntryMethod::PROXIMITY);
        $tagData = '9F4005F000F0A0019F02060000000025009F03060000000000009F2608D90A06501B48564E82027C005F3401019F360200029F0702FF009F0802008C9F0902008C9F34030403029F2701809F0D05F0400088009F0E0508000000009F0F05F0400098005F280208409F390105FFC605DC4000A800FFC7050010000000FFC805DC4004F8009F3303E0B8C89F1A0208409F350122950500000080005F2A0208409A031409109B02E8009F21030811539C01009F37045EED3A8E4F07A00000000310109F0607A00000000310108407A00000000310109F100706010A03A400029F410400000001';

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withTagData($tagData)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );

        $response = $transaction->edit()
            ->withTagData($tagData)
            ->execute();

        $this->assertTransactionResponse(
            $response,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );
    }

    public function testAdjustSaleTransaction_AdjustOnlyGratuity()
    {
        $card = $this->initCreditTrackData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );

        $response = $transaction->edit()
            ->withGratuity(1)
            ->execute();
        $this->assertTransactionResponse(
            $response,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );
    }

    public function testAdjustSaleTransaction_AdjustAmountToZero()
    {
        $card = $this->initCreditTrackData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );

        $response = $transaction->edit()
            ->withAmount(0)
            ->execute();
        $this->assertTransactionResponse(
            $response,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );
    }

    public function testAdjustSaleTransaction_AdjustGratuityToZero()
    {
        $card = $this->initCreditTrackData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );

        $response = $transaction->edit()
            ->withGratuity(0)
            ->execute();
        $this->assertTransactionResponse(
            $response,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );
    }

    public function testAdjustSaleTransaction_WithoutMandatory()
    {
        $card = $this->initCreditTrackData();

        $transaction = $card->charge($this->amount)
            ->withCurrency($this->currency)
            ->withChipCondition(EmvLastChipRead::SUCCESSFUL)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertTransactionResponse(
            $transaction,
            'SUCCESS',
            TransactionStatus::CAPTURED
        );

        $exceptionCaught = false;
        try {
            $transaction->edit()
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields [amount or tag or gratuityAmount]', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testAdjustSaleTransaction_TransactionNotFound()
    {
        $transaction = new Transaction();
        $transaction->transactionId = GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            $transaction->edit()
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40008', $e->responseCode);
            $this->assertStringContainsString(sprintf('Status Code: RESOURCE_NOT_FOUND - Transaction %s not found at this location.', $transaction->transactionId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testIncrementalAuth()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->authorize(50)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        $lodgingInfo = new LodgingData();
        $lodgingInfo->bookingReference = 's9RpaDwXq1sPRkbP';
        $lodgingInfo->durationDays = 10;
        $lodgingInfo->checkedInDate = date('Y-m-d H:i:s');
        $lodgingInfo->checkedOutDate = date('Y-m-d H:i:s', strtotime("+7 days"));
        $lodgingInfo->dailyRateAmount = '13.49';
        $item1 = new LodgingItems();
        $item1->types = [LodgingItemType::NO_SHOW];
        $item1->reference = 'item_1';
        $item1->totalAmount = '13.49';
        $item1->paymentMethodProgramCodes = [PaymentMethodProgram::ASSURED_RESERVATION];
        $lodgingInfo->items = [$item1];

        $transaction = $transaction->additionalAuth(10)
            ->withCurrency($this->currency)
            ->withLodgingData($lodgingInfo)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
        $this->assertEquals(60, $transaction->authorizedAmount);

        $capture = $transaction->capture()->execute();

        $this->assertNotNull($capture);
        $this->assertEquals('SUCCESS', $capture->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $capture->responseMessage);
    }

    public function testIncrementalAuth_WithoutCurrencyAndLodgingData()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->authorize(50)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        $transaction = $transaction->additionalAuth(10)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
        $this->assertEquals(60, $transaction->authorizedAmount);
    }

    public function testIncrementalAuth_ZeroAmount()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->authorize(50)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        $transaction = $transaction->additionalAuth(0)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);
        $this->assertEquals(50, $transaction->authorizedAmount);
    }

    public function testIncrementalAuth_ChargeTransaction()
    {
        $card = $this->initCreditCardData();

        $charge = $card->charge(50)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($charge);
        $this->assertEquals('SUCCESS', $charge->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $charge->responseMessage);

        $exceptionCaught = false;
        try {
            $charge->additionalAuth(10)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40290', $e->responseCode);
            $this->assertStringContainsString('Status Code: INVALID_ACTION - Cannot PROCESS Incremental Authorization over a transaction that does not have a status of PREAUTHORIZED.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testIncrementalAuth_WithoutAmount()
    {
        $card = $this->initCreditCardData();

        $transaction = $card->authorize(50)
            ->withCurrency($this->currency)
            ->execute();

        $this->assertNotNull($transaction);
        $this->assertEquals('SUCCESS', $transaction->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $transaction->responseMessage);

        $exceptionCaught = false;
        try {
            $transaction->additionalAuth()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertStringContainsString('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields [amount]', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testIncrementalAuth_TransactionNotFound()
    {
        $transaction = new Transaction();
        $transaction->transactionId = GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            $transaction->additionalAuth()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40008', $e->responseCode);
            $this->assertStringContainsString(sprintf('Status Code: RESOURCE_NOT_FOUND - Transaction %s not found at this location.', $transaction->transactionId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    private function initCreditCardData()
    {
        $card = new CreditCardData();
        $card->number = "5425230000004415";
        $card->expMonth = date('m');
        $card->expYear = date('Y', strtotime('+1 year'));
        $card->cvn = "852";
        $card->cardHolderName = "James Mason";
        $card->cardPresent = true;

        return $card;
    }

    private function initCreditTrackData($entryMethod = EntryMethod::SWIPE)
    {
        $card = new CreditTrackData();
        $card->setTrackData('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $card->entryMethod = $entryMethod;

        return $card;
    }

    private function assertTransactionResponse($transaction, $transactionResponseCode, $transactionStatus)
    {
        $this->assertNotNull($transaction);
        $this->assertEquals($transactionResponseCode, $transaction->responseCode);
        $this->assertEquals($transactionStatus, $transaction->responseMessage);
    }
}