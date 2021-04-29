<?php

use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\Entities\Reporting\ActionSummary;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Enums\GpApi\StoredPaymentMethodSortProperty;

class ReportingActionsTest extends TestCase
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

        return $config;
    }

    public function testReportDisputeDetail()
    {
        $actionId = 'ACT_SDe8C3FL8w4d4yHf7btgL1xPWQac5j';
        $response = ReportingService::actionDetail($actionId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf( ActionSummary::class, $response);
        $this->assertEquals($actionId, $response->id);
    }

    public function testFindActions_By_StartDateAndEndDate()
    {
        $startDate = (new \DateTime())->modify('-30 days')->setTime(0,0,0);
        $endDate = (new \DateTime())->modify('-3 days')->setTime(0,0,0);

        $response = ReportingService::findActionsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $actionsList = $response->result;
        uasort($actionsList, function($a, $b) {return strcmp(($a->timeCreated)->format('Y-m-d H:i:s'), ($b->timeCreated)->format('Y-m-d H:i:s'));});

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertSame($actionsList[$index], $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->timeCreated);
            $this->assertLessThanOrEqual($endDate, $rs->timeCreated);
        }
    }

    public function testFindActions_FilterBy_Type()
    {
        $actionType = 'PREAUTHORIZE';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::ACTION_TYPE, $actionType)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($actionType, $rs->type);
        }
    }

    public function testFindActions_FilterBy_Resource()
    {
        $resource = 'TRANSACTIONS';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::RESOURCE, $resource)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($resource, $rs->resource);
        }
    }

    public function testFindActions_FilterBy_ResourceStatus()
    {
        $resourceStatus = 'REVERSED';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::RESOURCE_STATUS, $resourceStatus)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($resourceStatus, $rs->resourceStatus);
        }
    }
}