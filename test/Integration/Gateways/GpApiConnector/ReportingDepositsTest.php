<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\DepositSortProperty;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\DepositSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class ReportingDepositsTest extends TestCase
{
    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function testReportDepositDetail()
    {
        $depositId = 'DEP_2342423443';
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
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
        $this->assertGreaterThanOrEqual($startDate, $randomDeposit->depositDate);
    }

    public function testReportFindDepositsOrderByDepositId()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::DEPOSIT_ID, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::STATUS, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TYPE, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        $endDate = new \DateTime('2021-01-15 midnight');
        $response = ReportingService::findDepositsPaged(1, 10)
            ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertNotEmpty($response->result);
        $randomDeposit = $response->result[array_rand($response->result)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsByNotFoundAmount()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        $amount = 140;

        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
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
        $startDate = new \DateTime('2020-11-01 midnight');
        $amount = 141;

        try {
            $response = ReportingService::findDepositsPaged(1, 10)
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::START_DATE, $startDate)
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
        $config = new GpApiConfig();
        $config->appId = 'VuKlC2n1cr5LZ8fzLUQhA7UObVks6tFF';
        $config->appKey = 'NmGM0kg92z2gA7Og';
        $config->environment = Environment::TEST;

        return $config;
    }
}