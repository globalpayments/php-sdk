<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\PaymentEntryMode;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentType;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReportingTransactionsTest extends TestCase
{
    private $startDate;
    private $endDate;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function testTransactionDetailsReport()
    {
        $transactionId = 'TRN_RyWZELCUbOq12IPDowbOevTC9BZxZi_6827116a3d1b';
        try {
            /** @var TransactionSummary $response */
            $response = ReportingService::transactionDetail($transactionId)->execute();
        } catch (ApiException $e) {
            $this->fail("Transaction details report failed with " . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertInstanceOf(TransactionSummary::class, $response);
        $this->assertEquals($transactionId, $response->transactionId);
    }

    public function testTransactionDetailsReport_WrongId()
    {
        $transactionId = GenerationUtils::getGuid();
        try {
            ReportingService::transactionDetail($transactionId)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals("Status Code: RESOURCE_NOT_FOUND - Transactions " . $transactionId . " not found at this /ucp/transactions/" . $transactionId . "", $e->getMessage());
        }
    }

    public function testReportFindTransactionsByStartDateAndEndDate()
    {
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->andWith(SearchCriteria::END_DATE, $this->endDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find transactions failed with " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertLessThanOrEqual($this->endDate->format('Y-m-d'), $rs->transactionDate->format('Y-m-d'));
            $this->assertGreaterThanOrEqual($this->startDate->format('Y-m-d'), $rs->transactionDate->format('Y-m-d'));
        }
    }

    public function testReportFindTransactionsById()
    {
        $transactionId = 'TRN_RyWZELCUbOq12IPDowbOevTC9BZxZi_6827116a3d1b';
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->withTransactionId($transactionId)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find transactions by Id failed: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertCount(1, $response->result);
        $this->assertEquals($transactionId, $response->result[0]->transactionId);
    }

    public function testReportFindTransactionsById_WrongId()
    {
        $transactionId = GenerationUtils::getGuid();;
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->withTransactionId($transactionId)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find transactions by Id failed: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertCount(0, $response->result);
    }

    public function testReportFindTransactionsByBatchId()
    {
        $batchId = 'BAT_870078';

        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::BATCH_ID, $batchId)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by batch id failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($batchId, $rs->batchSequenceNumber);
        }
    }

    public function testReportFindTransactionsByType()
    {
        $paymentType = PaymentType::SALE;
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::PAYMENT_TYPE, $paymentType)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by type failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($paymentType, $rs->transactionType);
        }

        $paymentTypeRefund = PaymentType::REFUND;
        try {
            $responseRefund = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::PAYMENT_TYPE, $paymentTypeRefund)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by type failed: ' . $e->getMessage());
        }

        $this->assertNotNull($responseRefund);
        $this->assertTrue(is_array($responseRefund->result));
        /** @var TransactionSummary $rs */
        foreach ($responseRefund->result as $rs) {
            $this->assertEquals($paymentTypeRefund, $rs->transactionType);
        }

        $this->assertNotSame($response->result, $responseRefund->result);
    }

    public function testReportFindTransactionsByAmountAndCurrencyAndCountry()
    {
        $amount = 19.99;
        $currency = 'USD'; //case sensitive
        $country = 'US'; //case sensitive
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(DataServiceCriteria::AMOUNT, $amount)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->andWith(DataServiceCriteria::CURRENCY, $currency)
                ->andWith(DataServiceCriteria::COUNTRY, $country)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by amount, currency and country failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($amount, $rs->amount);
            $this->assertEquals($currency, $rs->currency);
            $this->assertEquals($country, $rs->country);
        }
    }

    public function testReportFindTransactionsByChannel()
    {
        $channel = Channel::CardNotPresent;
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::CHANNEL, $channel)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by channel failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($channel, $rs->channel);
        }

        $channelCP = Channel::CardPresent;
        try {
            $responseCP = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::CHANNEL, $channelCP)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by channel failed: ' . $e->getMessage());
        }

        $this->assertNotNull($responseCP);
        $this->assertTrue(is_array($responseCP->result));
        /** @var TransactionSummary $rs */
        foreach ($responseCP->result as $rs) {
            $this->assertEquals($channelCP, $rs->channel);
        }

        $this->assertNotSame($response->result, $responseCP->result);
    }

    public function testReportFindTransactionsByStatus()
    {
        $transactionStatus = TransactionStatus::CAPTURED;
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::TRANSACTION_STATUS, $transactionStatus)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by status failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($transactionStatus, $rs->transactionStatus);
        }
    }

    public function testReportFindTransactionsBy_AllStatuses()
    {
        $transactionStatus = new TransactionStatus();
        $reflectionClass = new ReflectionClass($transactionStatus);
        foreach ($reflectionClass->getConstants() as $value) {
            try {
                $response = ReportingService::findTransactionsPaged(1, 10)
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->where(SearchCriteria::TRANSACTION_STATUS, $value)
                    ->andWith(SearchCriteria::START_DATE, $this->startDate)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find transactions by status failed: ' . $e->getMessage());
            }

            $this->assertNotNull($response);
            $this->assertTrue(is_array($response->result));
            /** @var TransactionSummary $rs */
            foreach ($response->result as $rs) {
                $this->assertEquals(TransactionStatus::$mapTransactionStatusResponse[$value], $rs->transactionStatus);
            }
        }
    }

    public function testReportFindTransactionsByCardBrandAndAuthCode()
    {
        $cardBrand = 'VISA';
        $authCode = '12345';
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::CARD_BRAND, $cardBrand)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->andWith(SearchCriteria::AUTH_CODE, $authCode)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by card brand and auth code failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($cardBrand, $rs->cardType);
            $this->assertEquals($authCode, $rs->authCode);
        }
    }

    public function testReportFindTransactionsBy_AllCardBrands()
    {
        //"MC", "DINERS", "JCB" not supported in sandbox env
        $cardBrand = array("VISA", "AMEX", "DISCOVER", "CUP");
        foreach ($cardBrand as $value) {
            try {
                $response = ReportingService::findTransactionsPaged(1, 10)
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->where(SearchCriteria::CARD_BRAND, $value)
                    ->andWith(SearchCriteria::START_DATE, $this->startDate)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find transactions by card brand failed: ' . $e->getMessage());
            }

            $this->assertNotNull($response);
            $this->assertTrue(is_array($response->result));
            /** @var TransactionSummary $rs */
            foreach ($response->result as $rs) {
                $this->assertEquals($value, $rs->cardType);
            }
        }
    }

    public function testReportFindTransactionsByReference()
    {
        $referenceNumber = '1010000158841908572';
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::REFERENCE_NUMBER, $referenceNumber)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by reference number failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($referenceNumber, $rs->referenceNumber);
        }
    }

    public function testReportFindTransactionsBy_WrongReference()
    {
        $referenceNumber = GenerationUtils::getGuid();;
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::REFERENCE_NUMBER, $referenceNumber)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by reference number failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertCount(0, $response->result);
    }

    public function testReportFindTransactionsByBrandReference()
    {
        $brandReference = 's9RpaDwXq1sPRkbP';
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::BRAND_REFERENCE, $brandReference)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by brand reference failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($brandReference, $rs->brandReference);
        }
    }

    public function testReportFindTransactionsBy_WrongBrandReference()
    {
        $brandReference = GenerationUtils::getGuid();;
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::BRAND_REFERENCE, $brandReference)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by brand reference failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertCount(0, $response->result);
    }

    public function testReportFindTransactionsByEntryMode()
    {
        $entryMode = new PaymentEntryMode();
        $reflectionClass = new ReflectionClass($entryMode);
        foreach ($reflectionClass->getConstants() as $value) {
            try {
                $response = ReportingService::findTransactionsPaged(1, 10)
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->where(SearchCriteria::PAYMENT_ENTRY_MODE, $value)
                    ->andWith(SearchCriteria::START_DATE, $this->startDate)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
            }

            $this->assertNotNull($response);
            $this->assertTrue(is_array($response->result));
            /** @var TransactionSummary $rs */
            foreach ($response->result as $rs) {
                $this->assertStringContainsString($value, $rs->entryMode);
            }
        }
    }

    public function testReportFindTransactionsBy_NumberFirst6_and_NumberLast4()
    {
        $numberFirst6 = "411111";
        $numberLast4 = "1111";

        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED)
                ->where(SearchCriteria::CARD_NUMBER_FIRST_SIX, $numberFirst6)
                ->andWith(SearchCriteria::CARD_NUMBER_LAST_FOUR, $numberLast4)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by first6 and last4 of card number  failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertStringStartsWith($numberFirst6, $rs->maskedCardNumber);
            $this->assertStringEndsWith($numberLast4, $rs->maskedCardNumber);
        }
    }

    public function testReportFindTransactionsBy_TokenFirst6_and_TokenLast4()
    {
        $tokenFirst6 = "516730";
        $tokenLast4 = "5507";
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED)
                ->where(SearchCriteria::TOKEN_FIRST_SIX, $tokenFirst6)
                ->andWith(SearchCriteria::TOKEN_LAST_FOUR, $tokenLast4)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by first6 and last4 of token failed: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertStringStartsWith($tokenFirst6, (string)$rs->maskedCardNumber);
            $this->assertStringEndsWith($tokenLast4, $rs->maskedCardNumber);
        }
    }

    public function testReportFindTransactionsBy_TokenFirst6_and_TokenLast4_and_PaymentMethod()
    {
        $tokenFirst6 = "516730";
        $tokenLast4 = "5507";
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED)
                ->where(SearchCriteria::TOKEN_FIRST_SIX, $tokenFirst6)
                ->andWith(SearchCriteria::TOKEN_LAST_FOUR, $tokenLast4)
                ->andWith(SearchCriteria::PAYMENT_METHOD_NAME, PaymentMethodName::DIGITAL_WALLET)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by first6 and last4 of token failed: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertStringStartsWith($tokenFirst6, (string)$rs->maskedCardNumber);
            $this->assertStringEndsWith($tokenLast4, $rs->maskedCardNumber);
        }
    }

    public function testReportFindTransactionsBy_TokenFirst6_and_TokenLast4_and_WrongPaymentMethod()
    {
        $tokenFirst6 = "516730";
        $tokenLast4 = "5507";
        try {
            ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED)
                ->where(SearchCriteria::TOKEN_FIRST_SIX, $tokenFirst6)
                ->andWith(SearchCriteria::TOKEN_LAST_FOUR, $tokenLast4)
                ->andWith(SearchCriteria::PAYMENT_METHOD_NAME, PaymentMethodName::CARD)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40043', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Request contains unexpected fields: payment_method', $e->getMessage());
        }
    }

    public function testReportFindTransactionsBy_PaymentMethod()
    {
        $paymentMethodName = new PaymentMethodName();
        $reflectionClass = new ReflectionClass($paymentMethodName);
        foreach ($reflectionClass->getConstants() as $value) {
            try {
                $response = ReportingService::findTransactionsPaged(1, 10)
                    ->where(SearchCriteria::PAYMENT_METHOD, $value)
                    ->andWith(SearchCriteria::START_DATE, $this->startDate)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find transactions by payment method failed: ' . $e->getMessage());
            }
            $this->assertNotNull($response);
            $this->assertTrue(is_array($response->result));
        }
    }

    public function testReportFindTransactionsBy_Name()
    {
        $name = "NAME NOT PROVIDED";
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->where(SearchCriteria::CARDHOLDER_NAME, $name)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by name failed: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($name, $rs->cardHolderName);
        }
    }

    public function testReportFindTransactions_OrderBy_Type()
    {
        $response = ReportingService::findTransactionsPaged(1, 20)
            ->orderBy(TransactionSortProperty::TYPE, SortDirection::ASC)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $transactionList = $response->result;
        uasort($transactionList, function ($a, $b) {
            return strcmp($a->transactionType, $b->transactionType);
        });
        foreach ($response->result as $index => $tr) {
            $this->assertSame($transactionList[$index], $tr);
        }
    }

    public function testReportFindTransactions_OrderBy_ID()
    {
        $response = ReportingService::findTransactionsPaged(1, 10)
            ->orderBy(TransactionSortProperty::ID, SortDirection::ASC)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $transactionList = $response->result;
        uasort($transactionList, function ($a, $b) {
            return strcmp($a->transactionId, $b->transactionId);
        });
        foreach ($response->result as $index => $tr) {
            $this->assertSame($transactionList[$index], $tr);
        }
    }

    public function testReportFindTransactions_OrderBy_TimeCreated()
    {
        $response = ReportingService::findTransactionsPaged(1, 10)
            ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::ASC)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $transactionList = $response->result;
        uasort($transactionList, function ($a, $b) {
            return strcmp(($a->transactionDate)->format('Y-m-d H:i:s'), ($b->transactionDate)->format('Y-m-d H:i:s'));
        });
        foreach ($response->result as $index => $tr) {
            $this->assertSame($transactionList[$index], $tr);
        }
    }

    public function testReportFindTransactions_InvalidAccountName()
    {
        try {
            ReportingService::findTransactionsPaged(1, 10)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->andWith(SearchCriteria::ACCOUNT_NAME, "12345")
                ->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40003', $e->responseCode);
            $this->assertEquals("Status Code: ACTION_NOT_AUTHORIZED - Token does not match account_id or account_name in the request", $e->getMessage());
        }
    }

    public function testReportFindTransactions_WithoutStartDate()
    {
        $response = ReportingService::findTransactionsPaged(1, 10)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertLessThanOrEqual(new \DateTime('today midnight'), $rs->transactionDate);
            $this->assertGreaterThanOrEqual((new \DateTime())->modify('-30 days 00:00:00'), $rs->transactionDate);
        }
    }

    public function testReportFindTransactionsByPaymentMethod()
    {
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->andWith(SearchCriteria::PAYMENT_METHOD_NAME, PaymentMethodName::BANK_TRANSFER)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by payment method failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals(PaymentMethodName::BANK_TRANSFER, $rs->paymentType);
        }
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }
}
