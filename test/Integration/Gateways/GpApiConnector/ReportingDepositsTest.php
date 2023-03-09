<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\DepositSortProperty;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\DepositSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use PHPUnit\Framework\TestCase;

class ReportingDepositsTest extends TestCase
{
    private $startDate;
    private $endDate;
    /** @var DepositSummary */
    private $depositSummary;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());

        $this->startDate = $startDate = (new \DateTime())->modify('-1 year')->setTime(0, 0, 0);
        $this->endDate = $endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);
        $response = ReportingService::findDepositsPaged(1, 1)
            ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();
        if (count($response->result) == 1) {
            $this->depositSummary = $response->result[0];
        }
    }

    static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function testReportDepositDetail()
    {
        $depositId = !empty($this->depositSummary) ? $this->depositSummary->depositId : 'DEP_2342423443';
        try {
            /** @var DepositSummary $response */
            $response = ReportingService::depositDetail($depositId)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Deposit detail failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertInstanceOf(DepositSummary::class, $response);
        $this->assertEquals($depositId, $response->depositId);
    }

    public function testReportDepositDetailWrongId()
    {
        $depositId = 'DEP_0000000001';
        $exceptionCaught = false;
        try {
            ReportingService::depositDetail($depositId)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals("Status Code: RESOURCE_NOT_FOUND - Deposits " . $depositId . " not found at this /ucp/settlement/deposits/" . $depositId, $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testReportFindDepositsByStartDateAndOrderByTimeCreated()
    {
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits by start date failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
        $this->assertGreaterThanOrEqual($this->startDate, $randomDeposit->depositDate);
    }

    public function testReportFindDepositsOrderByDepositId()
    {
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::DEPOSIT_ID, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits order by deposit id failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsOrderByStatus()
    {
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::STATUS, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits order by status failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsOrderByType()
    {
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TYPE, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits order by type failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsOrderByEndDateOrderByTimeCreated()
    {
        $response = ReportingService::findDepositsPaged(1, 10)
            ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        $randomDeposit = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsByNotFoundAmount()
    {
        $amount = 1;

        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->andWith(DataServiceCriteria::AMOUNT, $amount)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits not found amount failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(sizeof($response->result) == 0);
    }

    public function testReportFindDepositsByAmount()
    {
        $amount = $amount = !empty($this->depositSummary) ? $this->depositSummary->amount : 100;

        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->andWith(DataServiceCriteria::AMOUNT, $amount)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits by amount failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
        foreach ($response->result as $deposit) {
            $this->assertEquals($deposit->amount, $amount);
        }
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }
}