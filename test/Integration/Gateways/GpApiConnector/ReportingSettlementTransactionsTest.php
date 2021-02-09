<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\DepositStatus;
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

class ReportingSettlementTransactionsTest extends TestCase
{
    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function testReportFindSettlementTransactionsByStartDateAndEndDate()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $endDate = (new \DateTime())->modify('-3 days');
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::END_DATE, $endDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertLessThanOrEqual($endDate, $rs->transactionDate);
        }
    }

    public function testReportFindSettlementTransactions_OrderBy_TimeCreated()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
        }

        try {
            $responseAsc = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::ASC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($responseAsc as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
        }

        $this->assertNotSame($response, $responseAsc);
    }

    public function testReportFindSettlementTransactions_OrderBy_Status()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::STATUS, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $randomTransaction */
        $randomTransaction = $response[array_rand($response)];
        $this->assertNotNull($randomTransaction);
        $this->assertInstanceOf(TransactionSummary::class, $randomTransaction);
        $this->assertGreaterThanOrEqual($startDate, $randomTransaction->transactionDate);

        try {
            $responseAsc = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::STATUS, SortDirection::ASC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $randomTransaction */
        $randomTransaction = $responseAsc[array_rand($responseAsc)];
        $this->assertNotNull($randomTransaction);
        $this->assertInstanceOf(TransactionSummary::class, $randomTransaction);
        $this->assertGreaterThanOrEqual($startDate, $randomTransaction->transactionDate);

        $this->assertNotSame($response, $responseAsc);
    }

    public function testReportFindSettlementTransactions_OrderBy_Type()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TYPE, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $randomTransaction */
        $randomTransaction = $response[array_rand($response)];
        $this->assertNotNull($randomTransaction);
        $this->assertInstanceOf(TransactionSummary::class, $randomTransaction);
        $this->assertGreaterThanOrEqual($startDate, $randomTransaction->transactionDate);

        try {
            $responseAsc = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TYPE, SortDirection::ASC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $randomTransaction */
        $randomTransaction = $responseAsc[array_rand($responseAsc)];
        $this->assertNotNull($randomTransaction);
        $this->assertInstanceOf(TransactionSummary::class, $randomTransaction);
        $this->assertGreaterThanOrEqual($startDate, $randomTransaction->transactionDate);

        $this->assertNotSame($response, $responseAsc);
    }

    public function testReportFindSettlementTransactions_OrderBy_TypeAndTimeCreated()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $randomTransaction */
        $randomTransaction = $response[array_rand($response)];
        $this->assertNotNull($randomTransaction);
        $this->assertInstanceOf(TransactionSummary::class, $randomTransaction);
        $this->assertGreaterThanOrEqual($startDate, $randomTransaction->transactionDate);

        try {
            $responseType = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TYPE, SortDirection::ASC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $randomTransaction */
        $randomTransaction = $responseType[array_rand($responseType)];
        $this->assertNotNull($randomTransaction);
        $this->assertInstanceOf(TransactionSummary::class, $randomTransaction);
        $this->assertGreaterThanOrEqual($startDate, $randomTransaction->transactionDate);

        $this->assertNotSame($response, $responseType);
    }

    public function testReportFindSettlementTransactions_FilterBy_NumberFirst6_And_NumberLast4()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $numberFirst6 = "376768";
        $numberLast4 = "5006";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::CARD_NUMBER_FIRST_SIX, $numberFirst6)
                ->andWith(SearchCriteria::CARD_NUMBER_LAST_FOUR, $numberLast4)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertStringStartsWith($numberFirst6, $rs->maskedCardNumber);
            $this->assertStringEndsWith($numberLast4, $rs->maskedCardNumber);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_DepositStatus()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $depositStatus = new DepositStatus();
        $reflectionClass = new ReflectionClass($depositStatus);
        foreach ($reflectionClass->getConstants() as $value) {
            try {
                $response = ReportingService::findSettlementTransactions()
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->withPaging(1, 10)
                    ->where(SearchCriteria::START_DATE, $startDate)
                    ->andWith(SearchCriteria::DEPOSIT_STATUS, $value)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
            }
            $this->assertNotNull($response);
            $this->assertTrue(is_array($response));
            /** @var TransactionSummary $rs */
            foreach ($response as $rs) {
                $this->assertInstanceOf(TransactionSummary::class, $rs);
                $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
                $this->assertEquals($value, $rs->depositStatus);
            }
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_CardBrand()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $cardBrand = array("VISA", "MASTERCARD", "AMEX", "DINERS", "DISCOVER", "JCB", "CUP");
        foreach ($cardBrand as $value) {
            try {
                $response = ReportingService::findSettlementTransactions()
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->withPaging(1, 10)
                    ->where(SearchCriteria::START_DATE, $startDate)
                    ->andWith(SearchCriteria::CARD_BRAND, $value)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
            }

            $this->assertNotNull($response);
            $this->assertTrue(is_array($response));
            /** @var TransactionSummary $rs */
            foreach ($response as $rs) {
                $this->assertInstanceOf(TransactionSummary::class, $rs);
                $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
                $this->assertStringStartsWith($value, $rs->cardType);
            }
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_Wrong_CardBrand()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $cardBrand = "Bank of America";
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::CARD_BRAND, $cardBrand)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
    }

    public function testReportFindSettlementTransactions_FilterBy_ARN()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $arn = "24137550037630153798573";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertEquals($arn, $rs->aquirerReferenceNumber);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_Wrong_ARN()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $arn = GenerationUtils::getGuid();

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
    }

    public function testReportFindSettlementTransactions_FilterBy_BrandReference()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $brandReference = "460008653352066";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::BRAND_REFERENCE, $brandReference)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertEquals($brandReference, $rs->brandReference);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_Wrong_BrandReference()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $brandReference = GenerationUtils::getGuid();
        $brandReference = trim(str_replace("-", "", $brandReference));

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::BRAND_REFERENCE, $brandReference)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
    }

    public function testReportFindSettlementTransactions_FilterBy_AuthCode()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $authCode = "931951";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::AUTH_CODE, $authCode)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertEquals($authCode, $rs->authCode);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_Reference()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $reference = "50080513769";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::REFERENCE_NUMBER, $reference)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertEquals($reference, $rs->referenceNumber);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_Random_Reference()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $reference = GenerationUtils::getGuid();
        $reference = trim(str_replace("-", "", $reference));

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::REFERENCE_NUMBER, $reference)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
    }

    public function testReportFindSettlementTransactions_FilterBy_Status()
    {
        //only for Status = FUNDED and REJECTED
        $startDate = (new \DateTime())->modify('-30 days');
        $transactionStatus = new TransactionStatus();
        $reflectionClass = new ReflectionClass($transactionStatus);
        foreach ($reflectionClass->getConstants() as $value) {
            try {
                $response = ReportingService::findSettlementTransactions()
                    ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                    ->withPaging(1, 10)
                    ->where(SearchCriteria::START_DATE, $startDate)
                    ->andWith(SearchCriteria::TRANSACTION_STATUS, $value)
                    ->execute();
            } catch (ApiException $e) {
                $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
            }
            $this->assertNotNull($response);
            $this->assertTrue(is_array($response));

            /** @var TransactionSummary $rs */
            foreach ($response as $rs) {
                $this->assertInstanceOf(TransactionSummary::class, $rs);
                $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
                $this->assertEquals(TransactionStatus::$mapTransactionStatusResponse[$value], $rs->transactionStatus);
            }
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_DepositID()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $depositId = "DEP_2342423423";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::DEPOSIT_ID, $depositId)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertEquals($depositId, $rs->depositId);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_Random_DepositID()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $depositID = GenerationUtils::getGuid();
        $depositID = trim(str_replace("-", "", $depositID));

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(SearchCriteria::DEPOSIT_ID, $depositID)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
    }

    public function testReportFindSettlementTransactions_FilterBy_FromDepositTimeCreated_And_ToDepositTimeCreated()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $endDate = (new \DateTime())->modify('-3 days');
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::START_DEPOSIT_DATE, $startDate)
                ->andWith(DataServiceCriteria::END_DEPOSIT_DATE, $endDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->depositTimeCreated);
            $this->assertLessThanOrEqual($endDate, $rs->depositTimeCreated);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_FromBatchTimeCreated_And_ToBatchTimeCreated()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $endDate = (new \DateTime())->modify('-3 days');
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::START_BATCH_DATE, $startDate)
                ->andWith(DataServiceCriteria::END_BATCH_DATE, $endDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertInstanceOf(TransactionSummary::class, $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->batchCloseDate);
            $this->assertLessThanOrEqual($endDate, $rs->batchCloseDate);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_SystemMid_And_SystemHierarchy()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $endDate = (new \DateTime())->modify('-10 days');
        $systemMid = "101023947262";
        $systemHierarchy = "055-70-024-011-019";
        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::MERCHANT_ID, $systemMid)
                ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        /** @var TransactionSummary $rs */
        foreach ($response as $rs) {
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
            $this->assertLessThanOrEqual($rs->transactionDate, $endDate);
            $this->assertEquals($systemMid, $rs->merchantId);
            $this->assertEquals($systemHierarchy, $rs->merchantHierarchy);
        }
    }

    public function testReportFindSettlementTransactions_FilterBy_Random_MerchantID()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $merchantID = "111";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::MERCHANT_ID, $merchantID)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
    }

    public function testReportFindSettlementTransactions_FilterBy_Random_SystemHierarchy()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $systemHierarchy = "100-00-000-000-001";

        try {
            $response = ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
                ->execute();
        } catch (ApiException $e) {
            $this->fail('Find settlement transactions failed with: ' . $e->getMessage());
        }
        $this->assertNotNull($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
    }

    public function testReportFindSettlementTransactions_FilterBy_Invalid_MerchantID()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $merchantID = GenerationUtils::getGuid();
        $merchantID = trim(str_replace("-", "", $merchantID));

        try {
            ReportingService::findSettlementTransactions()
                ->orderBy(TransactionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::MERCHANT_ID, $merchantID)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40100', $e->responseCode);
            $this->assertEquals("Status Code: INVALID_REQUEST_DATA - Invalid Value provided in the input field - system.mid", $e->getMessage());
        }
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $accessTokenInfo = new \GlobalPayments\Api\Utils\AccessTokenInfo();
        //this is gpapistuff stuff
        $config->setAppId('i872l4VgZRtSrykvSn8Lkah8RE1jihvT');
        $config->setAppKey('9pArW2uWoA8enxKc');
        $config->environment = Environment::TEST;
        $config->setAccessTokenInfo($accessTokenInfo);
//        $klogger = new Logger("C:\\laragon\\www\\PHP-SDK-v3\\logs");
//        $config->requestLogger = new SampleRequestLogger($klogger);
        return $config;
    }
}