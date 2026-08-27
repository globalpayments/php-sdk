<?php

namespace Gateways\GpApiConnector;

use DateTime;
use GlobalPayments\Api\Entities\Enums\ActionSortProperty;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Reporting\ActionSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class ReportingActionsTest extends TestCase
{
    private DateTime $startDate;
    private DateTime $endDate;

    /** @var ActionSummary */
    private mixed $actionSummary;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $response = ReportingService::findActionsPaged(1, 1)
            ->orderBy(ActionSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::RESOURCE, 'TRANSACTIONS')
            ->execute();

        if (count($response->result) == 1) {
            $this->actionSummary = $response->result[0];
        }
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig(): GpApiConfig
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testReportActionDetail()
    {
        [$actionId, $response] = $this->resolveActionDetail();

        $this->assertNotNull($response);
        $this->assertInstanceOf(ActionSummary::class, $response);
        $this->assertEquals($actionId, $response->id);
        $this->assertNotEmpty($response->type);
        $this->assertNotEmpty($response->appId);
        $this->assertNotEmpty($response->appName);
        $this->assertNotNull($response->timeCreated);
        $this->assertNotEmpty($response->messageReceived);
        $this->assertNotEmpty($response->messageSent);
        $this->assertNotEmpty($response->rawRequest);
        $this->assertNotEmpty($response->rawResponse);
    }

    /**
     * Resolve an action id that is retrievable via GET /actions/{id}.
     *
     * @return array{0:string,1:ActionSummary}
     * @throws ApiException
     */
    private function resolveActionDetail(): array
    {
        $maxRounds = 6;
        $lastResourceNotFound = null;

        for ($round = 1; $round <= $maxRounds; $round++) {
            $listResponse = ReportingService::findActionsPaged(1, 5)
                ->orderBy(ActionSortProperty::TIME_CREATED, SortDirection::DESC)
                ->where(SearchCriteria::RESOURCE, 'TRANSACTIONS')
                ->execute();

            $candidateIds = array_values(array_unique(array_filter(
                array_merge(
                    [$listResponse->action->id ?? null],
                    array_map(fn($a) => $a->id ?? null, $listResponse->result ?? [])
                )
            )));

            foreach ($candidateIds as $candidateId) {
                try {
                    $response = ReportingService::actionDetail($candidateId)->execute();
                    return [$candidateId, $response];
                } catch (ApiException $e) {
                    if (strpos($e->getMessage(), 'RESOURCE_NOT_FOUND') === false) {
                        throw $e;
                    }
                    $lastResourceNotFound = $e;
                }
            }

            usleep(500000);
        }

        if ($lastResourceNotFound !== null) {
            throw $lastResourceNotFound;
        }

        $this->fail('Unable to resolve a valid action id for GET /actions/{id}.');
    }

    public function testActionDetailRandomId_NotFound()
    {
        $actionId = GenerationUtils::getGuid();
        $exceptionCaught = false;

        try {
            ReportingService::actionDetail($actionId)
                ->execute();
        } catch (ApiException $e) {
            $exceptionCaught = true;
            $this->assertStringContainsString('RESOURCE_NOT_FOUND', $e->getMessage());
            $this->assertEquals(sprintf('Status Code: RESOURCE_NOT_FOUND - Actions %s not found at this /ucp/actions/%s', $actionId, $actionId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindActions_By_StartDateAndEndDate()
    {
        $response = ReportingService::findActionsPaged(1, 10)
            ->orderBy(ActionSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
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
            $this->assertGreaterThanOrEqual($this->startDate, $rs->timeCreated);
            $this->assertLessThanOrEqual($this->endDate, $rs->timeCreated);
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
        $this->assertEmpty($response->result);
        $this->assertCount(0, $response->result);
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
        $this->assertCount(0, $response->result);
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
        $resourceId = $this->actionSummary->resourceId ?? 'TRN_UG1RHqhOa2rayOD9t9diEHBRbFrz93_ded605ba6b28';
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
        $this->assertCount(0, $response->result);
    }

    public function testFindActions_FilterBy_MerchantName()
    {
        $merchantName = 'Sandbox_merchant_3';
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
            $this->assertStringContainsString('ACTION_NOT_AUTHORIZED', $e->getMessage());
            $this->assertEquals('Status Code: ACTION_NOT_AUTHORIZED - Token does not match merchant_name in the request', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindActions_FilterBy_AccountName()
    {
//        $accountName = 'transaction_processing';
        $accountName = 'tokenization';
//        $accountName = 'settlement_reporting';
//        $accountName = 'dispute_management';
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
        $appName = 'SDK_TESTING_APP';
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
        $version = '2021-03-22';
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
        $this->assertCount(0, $response->result);
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
            ->orderBy(ActionSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::ACTION_ID, $id)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var ActionSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($id, $rs->id);
        }

        $responseDesc = ReportingService::findActionsPaged(1, 10)
            ->orderBy(ActionSortProperty::TIME_CREATED, SortDirection::DESC)
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
        $startDate = (new DateTime())->modify('-30 days');
        $endDate = (new DateTime())->modify('-3 days');

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