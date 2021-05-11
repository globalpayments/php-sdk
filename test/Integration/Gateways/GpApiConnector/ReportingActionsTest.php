<?php

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Enums\GpApi\StoredPaymentMethodSortProperty;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Reporting\ActionSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

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

    public function testReportActionDetail()
    {
        $actionId = 'ACT_SDe8C3FL8w4d4yHf7btgL1xPWQac5j';
        $response = ReportingService::actionDetail($actionId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf(ActionSummary::class, $response);
        $this->assertEquals($actionId, $response->id);
    }

    public function testReportActionDetail_RandomId()
    {
        $actionId = GenerationUtils::getGuid();
        $exceptionCaught = false;

        try {
            ReportingService::actionDetail($actionId)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals(sprintf('Status Code: RESOURCE_NOT_FOUND - Actions %s not found at this /ucp/actions/%s', $actionId, $actionId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindActions_By_StartDateAndEndDate()
    {
        $startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $response = ReportingService::findActionsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $actionsList = $response->result;
        uasort($actionsList, function ($a, $b) {
            return strcmp(($a->timeCreated)->format('Y-m-d H:i:s'), ($b->timeCreated)->format('Y-m-d H:i:s'));
        });

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertSame($actionsList[$index], $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->timeCreated);
            $this->assertLessThanOrEqual($endDate, $rs->timeCreated);
        }
    }

    public function testFindActions_FilterBy_Id()
    {
        $id = 'ACT_p11JBFXHU9w2linA6IhMf5ccOoR50a';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::ACTION_ID, $id)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($id, $rs->id);
        }
    }

    public function testFindActions_FilterBy_RandomId()
    {
        $id = GenerationUtils::getGuid();
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::ACTION_ID, $id)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(empty($response->result));
        $this->assertEquals(0, count($response->result));
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

    public function testFindActions_FilterBy_RandomType()
    {
        $actionType = GenerationUtils::getGuid();
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::ACTION_TYPE, $actionType)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEmpty($response->result);
        $this->assertEquals(0, count($response->result));
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

    public function testFindActions_FilterBy_ResourceId()
    {
        $resourceId = 'ACT_tc888Ub0L0dthnPjOqrjeSnB4JflNF';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::RESOURCE_ID, $resourceId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($resourceId, $rs->resourceId);
        }
    }

    public function testFindActions_FilterBy_RandomResourceId()
    {
        $resourceId = GenerationUtils::getGuid();
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::RESOURCE_ID, $resourceId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertEquals(0, count($response->result));
    }

    public function testFindActions_FilterBy_MerchantName()
    {
        $merchantName = 'Sandbox_merchant_2';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::MERCHANT_NAME, $merchantName)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($merchantName, $rs->merchantName);
        }
    }

    public function testFindActions_FilterBy_RandomMerchantName()
    {
        $merchantName = GenerationUtils::getGuid();
        $exceptionCaught = false;

        try {
            ReportingService::findActionsPaged(1, 10)
                ->where(SearchCriteria::MERCHANT_NAME, $merchantName)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40003', $e->responseCode);
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - Token does not match merchant_name in the request', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindActions_FilterBy_AccountName()
    {
//        $accountName = 'Transaction_Processing';
        $accountName = 'Tokenization';
//        $accountName = 'Settlement Reporting';
//        $accountName = 'Dispute Management';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::ACCOUNT_NAME, $accountName)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($accountName, $rs->accountName);
        }
    }

    public function testFindActions_FilterBy_AppName()
    {
        $appName = 'demo_app';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::APP_NAME, $appName)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($appName, $rs->appName);
        }
    }

    public function testFindActions_FilterBy_Version()
    {
        $version = '2020-04-10';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::VERSION, $version)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($version, $rs->version);
        }
    }

    public function testFindActions_FilterBy_WrongVersion()
    {
        $version = '2020-05-10';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::VERSION, $version)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertEquals(0, count($response->result));
    }

    public function testFindActions_FilterBy_ResponseCode()
    {
        $responseCode = 'DECLINED';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::RESPONSE_CODE, $responseCode)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($responseCode, $rs->responseCode);
        }
    }

    public function testFindActions_FilterBy_HttpResponseCode()
    {
        $httpResponseCode = '200';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::HTTP_RESPONSE_CODE, $httpResponseCode)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($httpResponseCode, $rs->httpResponseCode);
        }
    }

    public function testFindActions_FilterBy_502_HttpResponseCode()
    {
        $httpResponseCode = '502';
        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::HTTP_RESPONSE_CODE, $httpResponseCode)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertGreaterThanOrEqual(1, count($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($httpResponseCode, $rs->httpResponseCode);
        }
    }

    public function testFindActions_OrderBy_TimeCreated()
    {
        $id = 'ACT_p11JBFXHU9w2linA6IhMf5ccOoR50a';
        $response = ReportingService::findActionsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::ACTION_ID, $id)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($id, $rs->id);
        }

        $responseDesc = ReportingService::findActionsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::DESC)
            ->where(SearchCriteria::ACTION_ID, $id)
            ->execute();

        $this->assertNotNull($responseDesc);
        $this->assertTrue(is_array($responseDesc->result));

        /** @var ActionSummary $rs */
        foreach ($responseDesc->result as $index => $rs) {
            $this->assertEquals($id, $rs->id);
        }

        $this->assertNotSame($response, $responseDesc);
    }

    public function testFindActions_FilterBy_MultipleFilters()
    {
        $resource = 'TRANSACTIONS';
        $actionType = 'AUTHORIZE';
        $resource_status = 'DECLINED';
        $startDate = (new \DateTime())->modify('-30 days');
        $endDate = (new \DateTime())->modify('-3 days');

        $response = ReportingService::findActionsPaged(1, 10)
            ->where(SearchCriteria::RESOURCE, $resource)
            ->andWith(SearchCriteria::ACTION_TYPE, $actionType)
            ->andWith(SearchCriteria::RESOURCE_STATUS, $resource_status)
            ->andWith(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($resource, $rs->resource);
            $this->assertEquals($actionType, $rs->type);
            $this->assertEquals($resource_status, $rs->resourceStatus);
        }
    }
}