<?php


use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Enums\GpApi\StoredPaymentMethodSortProperty;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\StoredPaymentMethodSummary;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class ReportingStoredPaymentMethodsTest extends TestCase
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

    public function testFindStoredPaymentMethod_By_StartDateAndEndDate()
    {
        $startDate = (new \DateTime())->modify('-30 days')->setTime(0,0,0);
        $endDate = (new \DateTime())->modify('-3 days')->setTime(0,0,0);

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $paymentMethodsList = $response->result;
        uasort($paymentMethodsList, function($a, $b) {return strcmp(($a->timeCreated)->format('Y-m-d H:i:s'), ($b->timeCreated)->format('Y-m-d H:i:s'));});

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertSame($paymentMethodsList[$index], $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->timeCreated);
            $this->assertLessThanOrEqual($endDate, $rs->timeCreated);
        }
    }

    public function testFindStoredPaymentMethod_By_LastUpdated()
    {
        $startDate = (new \DateTime())->modify('-30 days')->setTime(0,0,0);
        $endDate = (new \DateTime())->modify('-3 days')->setTime(0,0,0);

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::FROM_TIME_LAST_UPDATED, $startDate)
            ->andWith(SearchCriteria::TO_TIME_LAST_UPDATED, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertTrue(count($response->result) > 0);

    }

    public function testFindStoredPaymentMethod_By_Status()
    {
        $status = 'ACTIVE';
        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::STORED_PAYMENT_METHOD_STATUS, $status)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($status, $rs->status);
        }
    }

    public function testFindStoredPaymentMethod_By_CardNumberLastFour()
    {
        $cardNumberLastFour = '1112';
        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::CARD_NUMBER_LAST_FOUR, $cardNumberLastFour)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($cardNumberLastFour, $rs->cardNumberLastFour);
        }
    }

    public function testReportDisputeDetail()
    {
        $paymentMethodId = 'PMT_37c89e83-0349-4e19-add1-4b60d3c3d3ac';
        $response = ReportingService::storedPaymentMethodDetail($paymentMethodId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf(StoredPaymentMethodSummary::class, $response);
        $this->assertEquals($paymentMethodId, $response->paymentMethodId);
    }
}