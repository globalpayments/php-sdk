<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\DepositSortProperty;
use GlobalPayments\Api\Entities\Enums\GpApi\DepositStatus;
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

    public function testReportFindDepositsByStartDateAndOrderByTimeCreated()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDeposits()
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits by start date failed with: " . $e->getMessage());
        }
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response[array_rand($response)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
        $this->assertGreaterThanOrEqual($startDate, $randomDeposit->depositDate);
    }

    public function testReportFindDepositsOrderByDepositId()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDeposits()
                ->orderBy(DepositSortProperty::DEPOSIT_ID, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits order by deposit id failed with: " . $e->getMessage());
        }
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response[array_rand($response)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsOrderByStatus()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDeposits()
                ->orderBy(DepositSortProperty::STATUS, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits order by status failed with: " . $e->getMessage());
        }
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response[array_rand($response)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsOrderByType()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        try {
            $response = ReportingService::findDeposits()
                ->orderBy(DepositSortProperty::TYPE, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits order by type failed with: " . $e->getMessage());
        }
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response[array_rand($response)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsOrderByEndDateOrderByTimeCreated()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        $endDate = new \DateTime('2021-01-15 midnight');
        $response = ReportingService::findDeposits()
            ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
            ->withPaging(1, 10)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();


        $randomDeposit = $response[array_rand($response)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
    }

    public function testReportFindDepositsByNotFoundAmount()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        $amount = 140;

        try {
            $response = ReportingService::findDeposits()
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::AMOUNT, $amount)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits not found amount failed with: " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertTrue(sizeof($response)==0);
    }

    public function testReportFindDepositsByAmount()
    {
        $startDate = new \DateTime('2020-11-01 midnight');
        $amount = 141;

        try {
            $response = ReportingService::findDeposits()
                ->orderBy(DepositSortProperty::TIME_CREATED, SortDirection::DESC)
                ->withPaging(1, 10)
                ->where(SearchCriteria::START_DATE, $startDate)
                ->andWith(DataServiceCriteria::AMOUNT, $amount)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find deposits by amount failed with: " . $e->getMessage());
        }
        /** @var DepositSummary $randomDeposit */
        $randomDeposit = $response[array_rand($response)];
        $this->assertNotNull($randomDeposit);
        $this->assertInstanceOf(DepositSummary::class, $randomDeposit);
        foreach ($response as $deposit) {
            $this->assertEquals($deposit->amount, $amount);
        }
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $accessTokenInfo = new \GlobalPayments\Api\Utils\AccessTokenInfo();
        //this is gpapistuff stuff
        $config->setAppId('VuKlC2n1cr5LZ8fzLUQhA7UObVks6tFF');
        $config->setAppKey('NmGM0kg92z2gA7Og');
        $config->environment = Environment::TEST;
        $config->setAccessTokenInfo($accessTokenInfo);
//        $klogger = new Logger("C:\\laragon\\www\\PHP-SDK-v3\\logs");
//        $config->requestLogger = new SampleRequestLogger($klogger);

        return $config;
    }
}