<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\Channels;
use GlobalPayments\Api\Entities\Enums\GpApi\EntryMode;
use GlobalPayments\Api\Entities\Enums\GpApi\PaymentType;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Enums\GpApi\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReportingTransactionsTest extends TestCase
{
    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function testTransactionDetailsReport()
    {
        $transactionId = 'TRN_piIDuelPio1Vk1JWE7bNatWngfxUQT';
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
        $startDate = new \DateTime('2020-11-01 midnight');
        $endDate = new \DateTime('2020-12-01 23:59');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::END_DATE, $endDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find transactions failed with " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertLessThanOrEqual($endDate, $rs->transactionDate);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
        }
    }

    public function testReportFindTransactionsById()
    {
        $transactionId = 'TRN_mCBetNCJSP0xdJK1QdlfBsMVzemHHt';
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->withTransactionId($transactionId)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find transactions by Id failed: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertEquals(1, count($response->result));
        $this->assertEquals($transactionId, $response->result[0]->transactionId);
    }

    public function testReportFindTransactionsById_WrongId()
    {
        $transactionId = GenerationUtils::getGuid();;
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->withTransactionId($transactionId)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find transactions by Id failed: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertEquals(0, count($response->result));
    }

    public function testReportFindTransactionsByBatchId()
    {
        $batchId = 'BAT_870078';
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::BATCH_ID, $batchId)
                ->andWith(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::PAYMENT_TYPE, $paymentType)
                ->andWith(SearchCriteria::START_DATE, $startDate)
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
                ->andWith(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(DataServiceCriteria::AMOUNT, $amount)
                ->andWith(SearchCriteria::START_DATE, $startDate)
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
        $channel = Channels::CardNotPresent;
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::CHANNEL, $channel)
                ->andWith(SearchCriteria::START_DATE, $startDate)
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

        $channelCP = Channels::CardPresent;
        try {
            $responseCP = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::CHANNEL, $channelCP)
                ->andWith(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::TRANSACTION_STATUS, $transactionStatus)
                ->andWith(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');

        $transactionStatus = new TransactionStatus();
        $reflectionClass = new ReflectionClass($transactionStatus);
        foreach ($reflectionClass->getConstants() as $value) {
            try {
                $response = ReportingService::findTransactionsPaged(1, 10)
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->where(SearchCriteria::TRANSACTION_STATUS, $value)
                    ->andWith(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::CARD_BRAND, $cardBrand)
                ->andWith(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::AUTH_CODE, $authCode)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by type failed: ' . $e->getMessage());
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
        $startDate = new \DateTime('2020-11-01 midnight');

        $cardBrand = array("VISA", "MC", "AMEX", "DINERS", "DISCOVER", "JCB", "CUP");
        foreach ($cardBrand as $value) {
            try {
                $response = ReportingService::findTransactionsPaged(1, 10)
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->where(SearchCriteria::CARD_BRAND, $value)
                    ->andWith(SearchCriteria::START_DATE, $startDate)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find transactions by type failed: ' . $e->getMessage());
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::REFERENCE_NUMBER, $referenceNumber)
                ->andWith(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by type failed: ' . $e->getMessage());
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::REFERENCE_NUMBER, $referenceNumber)
                ->andWith(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by type failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertEquals(0, count($response->result));
    }

    public function testReportFindTransactionsByBrandReference()
    {
        $brandReference = 's9RpaDwXq1sPRkbP';
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::BRAND_REFERENCE, $brandReference)
                ->andWith(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::BRAND_REFERENCE, $brandReference)
                ->andWith(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by brand reference failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertEquals(0, count($response->result));
    }

    public function testReportFindTransactionsByEntryMode()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        $entryMode = new EntryMode();
        $reflectionClass = new ReflectionClass($entryMode);
        foreach ($reflectionClass->getConstants() as $value) {
            try {
                $response = ReportingService::findTransactionsPaged(1, 10)
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->where(SearchCriteria::PAYMENT_ENTRY_MODE, $value)
                    ->andWith(SearchCriteria::START_DATE, $startDate)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
            }

            $this->assertNotNull($response);
            $this->assertTrue(is_array($response->result));
            /** @var TransactionSummary $rs */
            foreach ($response->result as $rs) {
                $this->assertEquals($value, $rs->entryMode);
            }
        }
    }

    public function testReportFindTransactionsBy_NumberFirst6_and_NumberLast4()
    {
        $numberFirst6 = "411111";
        $numberLast4 = "1111";

        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::CARD_NUMBER_FIRST_SIX, $numberFirst6)
                ->andWith(SearchCriteria::CARD_NUMBER_LAST_FOUR, $numberLast4)
                ->andWith(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::TOKEN_FIRST_SIX, $tokenFirst6)
                ->andWith(SearchCriteria::TOKEN_LAST_FOUR, $tokenLast4)
                ->andWith(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertStringStartsWith($tokenFirst6, (string)$rs->maskedCardNumber);
            $this->assertStringEndsWith($tokenLast4, $rs->maskedCardNumber);
        }
    }

    public function testReportFindTransactionsBy_Name()
    {
        $name = "NAME NOT PROVIDED";
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->where(SearchCriteria::CARDHOLDER_NAME, $name)
                ->andWith(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($name, $rs->cardHolderName);
        }
    }

    public function testReportFindTransactions_OrderBy_Status()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::STATUS, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
    }

    public function testReportFindTransactions_OrderBy_Type()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TYPE, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        try {
            $responseAsc = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TYPE, SortDirection::ASC)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
        }

        $this->assertNotNull($responseAsc);
        $this->assertTrue(is_array($responseAsc->result));

        $this->assertNotSame($response->result, $responseAsc->result);
    }

    public function testReportFindTransactions_OrderBy_TypeAndTimeCreated()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TYPE, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        try {
            $responseTime = ReportingService::findTransactionsPaged(1, 10)
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find transactions by entry mode failed: ' . $e->getMessage());
        }

        $this->assertNotNull($responseTime);
        $this->assertTrue(is_array($responseTime->result));

        $this->assertNotSame($response->result, $responseTime->result);
    }

    public function testReportFindTransactions_InvalidAccountName()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            ReportingService::findTransactionsPaged(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::ACCOUNT_NAME, "12345")
                ->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40003', $e->responseCode);
            $this->assertEquals("Status Code: ACTION_NOT_AUTHORIZED - Token does not match account_id or account_name in the request", $e->getMessage());
        }
    }

    public function testReportFindTransactions_WithoutStartDate()
    {
        try {
            ReportingService::findTransactionsPaged(1, 10)
                ->execute();
        } catch (ApiException $e) {
            $this->assertEquals('40075', $e->responseCode);
            $this->assertEquals("Status Code: MANDATORY_DATA_MISSING - Retrieving a list expects a date range to be populated", $e->getMessage());
        }
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'VuKlC2n1cr5LZ8fzLUQhA7UObVks6tFF';
        $config->appKey = 'NmGM0kg92z2gA7Og';
        $config->environment = Environment::TEST;

        return $config;
    }
}
