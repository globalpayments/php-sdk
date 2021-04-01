<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\DisputeDocument;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GpApi\AdjustmentFunding;
use GlobalPayments\Api\Entities\Enums\GpApi\DisputeSortProperty;
use GlobalPayments\Api\Entities\Enums\GpApi\DisputeStage;
use GlobalPayments\Api\Entities\Enums\GpApi\DisputeStatus;
use GlobalPayments\Api\Entities\Enums\GpApi\SortDirection;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\DisputeSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use PHPUnit\Framework\TestCase;

class ReportingDisputesTest extends TestCase
{
    public function setup()
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'GkwdYGzQrEy1SdTz7S10P8uRjFMlEsJg';
        $config->appKey = 'zvXE2DmmoxPbQ6d0';
        $config->environment = Environment::TEST;

        return $config;
    }

    public function testReportDisputeDetail()
    {
        $disputeId = 'DIS_SAND_abcd1234';
        $response = ReportingService::disputeDetail($disputeId)
            ->execute();
        $this->assertNotNull($response);
        $this->assertInstanceOf(DisputeSummary::class, $response);
        $this->assertEquals($disputeId, $response->caseId);
    }

    public function testReportDisputeDetailWrongId()
    {
        $disputeId = 'DIS_SAND_aaaa1111';
        try {
            ReportingService::disputeDetail($disputeId)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40073', $e->responseCode);
            $this->assertEquals(
                'Status Code: INVALID_REQUEST_DATA - 101,Unable to locate dispute record for that ID. Please recheck the ID provided.',
                $e->getMessage());
        }
    }

    public function testReportFindDisputes_By_ARN()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $arn = "135091790340196";
        $disputes = ReportingService::findDisputesPaged(1,10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
            ->execute();

        $this->assertNotNull($disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($dispute->transactionARN, $arn);
        }
    }

    public function testReportFindDisputes_By_ARN_NotFound()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $arn = "874091790340471";
        try {
            ReportingService::findDisputesPaged(1, 10)
                ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
                ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40048', $e->responseCode);
            $this->assertEquals(
                'Status Code: INVALID_REQUEST_DATA - 105,Unable to locate dispute record for arn. Please recheck thevalue provided for arn.',
                $e->getMessage());
        }
    }

    public function testReportFindDisputes_By_Brand()
    {
        $cardBrand = "VISA";
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::CARD_BRAND, $cardBrand)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($dispute->transactionCardType, $cardBrand);
        }
    }

    public function testReportFindDisputes_By_Status()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::DISPUTE_STATUS, DisputeStatus::UNDER_REVIEW)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($dispute->caseStatus, DisputeStatus::UNDER_REVIEW);
        }
    }

    public function testReportFindDisputes_By_Stage()
    {
        $disputeStage = DisputeStage::CHARGEBACK;
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::DISPUTE_STAGE, $disputeStage)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($dispute->caseStage, $disputeStage);
        }
    }

    public function testReportFindDisputes_By_MerchantId_And_SystemHierarchy()
    {
        $merchantId = "8593872";
        $systemHierarchy = "111-23-099-002-005";
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, $merchantId)
            ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($dispute->caseMerchantId, $merchantId);
            $this->assertEquals($dispute->merchantHierarchy, $systemHierarchy);
        }
    }

    public function testReportFindDisputes_By_From_And_To_Stage_Time_Created()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $endDate = new \DateTime('2021-01-21 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::END_STAGE_DATE, $endDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertTrue($dispute->caseIdTime >= $startDate && $dispute->caseIdTime <= $endDate);
        }
    }

    public function testReportFindDisputes_Filter_By_From_And_To_Adjustment_Time_Created()
    {

        $endDate = new \DateTime();
        $startDate = (new \DateTime())->modify('-2 year +1 day');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::START_ADJUSTMENT_DATE, $startDate)
            ->andWith(DataServiceCriteria::END_ADJUSTMENT_DATE, $endDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $this->assertTrue(sizeof($disputes->result) > 0);
    }

    public function testReportFindDisputes_By_Adjustment_Funding()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::ADJUSTMENT_FUNDING, AdjustmentFunding::DEBIT)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $this->assertTrue(sizeof($disputes->result) > 0);
    }

    public function testReportFindDisputes_Order_By_Id()
    {
        $startDate = new \DateTime('2020-02-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_ARN()
    {
        $startDate = new \DateTime('2020-06-09 midnight');
        $endDate = new \DateTime('2020-06-22 midnight');
        // EndStageDate is mandatory in order to be able to sort by ARN
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ARN, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::END_STAGE_DATE, $endDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_Brand()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::BRAND, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_Status()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::STATUS, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_Stage()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::STAGE, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_FromStageTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::FROM_STAGE_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_ToStageTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::TO_STAGE_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_AdjustmentFunding()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ADJUSTMENT_FUNDING, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_FromAdjustmentTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::FROM_ADJUSTMENT_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_ToAdjustmentTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::TO_ADJUSTMENT_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_Id_With_Brand_VISA()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::DISPUTE_STATUS, DisputeStatus::UNDER_REVIEW)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    public function testReportFindDisputes_Order_By_Id_With_Stage_Chargeback()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::DISPUTE_STAGE, DisputeStage::CHARGEBACK)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
    }

    /***************************************
     *          Settlement disputes        *
     ***************************************/

    public function testReportSettlementDisputeDetail()
    {
        $settlementDisputeId = "DIS_810";
        $response = ReportingService::settlementDisputeDetail($settlementDisputeId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf(DisputeSummary::class, $response);
        $this->assertEquals($settlementDisputeId, $response->caseId);
    }

    public function testReportSettlementDisputeDetailWrongId()
    {
        $settlementDisputeId = "DIS_010";
        try {
            ReportingService::settlementDisputeDetail($settlementDisputeId)
                ->execute();
        } catch (GatewayException $ex) {
            $this->assertEquals('40118', $ex->responseCode);
            $this->assertEquals(
                'Status Code: RESOURCE_NOT_FOUND - Disputes DIS_010 not found at this /ucp/settlement/disputes/DIS_010',
                $ex->getMessage());
        }
    }

    public function testReportSettlementDispute_Order_By_Id_With_Status_UnderReview()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::DISPUTE_STATUS, DisputeStatus::UNDER_REVIEW)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($dispute->caseStatus, DisputeStatus::UNDER_REVIEW);
        }
    }

    public function testReportFindSettlementDisputes_Order_By_Id()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_ARN()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ARN, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_Brand()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::BRAND, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_Stage()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::STAGE, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_FromStageTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::FROM_STAGE_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_ToStageTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::TO_STAGE_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_AdjustmentFunding()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ADJUSTMENT_FUNDING, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_FromAdjustmentTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::FROM_ADJUSTMENT_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_Order_By_ToAdjustmentTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::TO_ADJUSTMENT_TIME_CREATED, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_FilterBy_ARN()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $arn = '74500010037624410827759';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($dispute->transactionARN, $arn);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_ARN_NotFound()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $arn = '00000010037624410827111';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(sizeof($summary->result) == 0);
    }

    public function testReportFindSettlementDisputes_FilterBy_Brand()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $brand = 'VISA';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::CARD_BRAND, $brand)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($dispute->transactionCardType, $brand);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_Brand_NotFound()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $brand = 'MASTERCAR';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::CARD_BRAND, $brand)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(count($summary->result) == 0);
    }

    public function testReportFindSettlementDisputes_FilterBy_Stage()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $stage = 'CHARGEBACK';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(SearchCriteria::DISPUTE_STAGE, $stage)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($dispute->caseStage, $stage);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_FromAndToStageTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $endDate = new \DateTime('2021-01-22 midnight');

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::END_STAGE_DATE, $endDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertTrue($dispute->caseTime <= $endDate);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_AdjustmentFunding()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $adjustmentFunding = AdjustmentFunding::CREDIT;

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::ADJUSTMENT_FUNDING, $adjustmentFunding)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_FilterBy_FromAndToAdjustmentTimeCreated()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $endDate = new \DateTime('2021-01-21 midnight');

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::START_ADJUSTMENT_DATE, $endDate)
            ->andWith(DataServiceCriteria::END_ADJUSTMENT_DATE, $endDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
    }

    public function testReportFindSettlementDisputes_FilterBy_SystemMidAndHierarchy()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $systemMid = '101023947262';
        $systemHierarchy = '055-70-024-011-019';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, $systemMid)
            ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($dispute->merchantHierarchy, $systemHierarchy);
            $this->assertEquals($dispute->caseMerchantId, $systemMid);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_WrongSystemMid()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $systemHierarchy = '000-70-024-011-111';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(count($summary->result) == 0);
    }

    public function testReportFindSettlementDisputes_FilterBy_WrongSystemHierarchy()
    {
        $startDate = new \DateTime('2020-01-01 midnight');
        $systemMid = '000023947222';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $startDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, $systemMid)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(count($summary->result) == 0);
    }

    public function testReportDisputeAcceptance()
    {
        $disputeId = "DIS_SAND_abcd1234";
        $dispute = ReportingService::disputeDetail($disputeId)
            ->execute();
        $response = $dispute->accept()->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testDisputeAcceptWrongId()
    {
        $dispute = new DisputeSummary();
        $dispute->caseId = "DIS_SAND_abcd1234ZZ";

        $exceptionCaught = false;
        try {
            $dispute->accept()->execute();
        } catch (GatewayException $ex) {
            $exceptionCaught = true;
            $this->assertEquals("40067", $ex->responseCode);
            $this->assertContains("INVALID_DISPUTE_ACTION", $ex->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testReportDisputeChallenge()
    {
        $dispute = new DisputeSummary();
        $dispute->caseId = "DIS_SAND_abcd1234";
        $document = new DisputeDocument();
        $document->type = 'SALES_RECEIPT';
        $document->b64_content = 'R0lGODlhigPCAXAAACwAAAAAigPCAYf///8AQnv';
        $documents[] = $document;
        $response = $dispute->challenge($documents)->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testReportDisputeChallenge_MissingType()
    {
        $dispute = new DisputeSummary();
        $dispute->caseId = "DIS_SAND_abcd1234";
        $document = new DisputeDocument();
        $document->b64_content = 'R0lGODlhigPCAXAAACwAAAAAigPCAYf///8AQnv';
        $documents[] = $document;
        $response = $dispute->challenge($documents)->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testReportDisputeChallenge_MultipleDocuments()
    {
        $dispute = new DisputeSummary();
        $dispute->caseId = "DIS_SAND_abcd1241";
        $document = new DisputeDocument();
        $document->type = 'SALES_RECEIPT';
        $document->b64_content = 'R0lGODlhigPCAXAAACwAAAAAigPCAYf///8AQnv';

        $secondDocument = new DisputeDocument();
        $secondDocument->type = 'SALES_RECEIPT';
        $secondDocument->b64_content = 'R0lGODlhigPCAXAAACwAAAAAigPCAYf///8AQnv';

        $documents[] = $document;
        $documents[] = $secondDocument;
        $response = $dispute->challenge($documents)->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
    }

    public function testReportDisputeChallengeWrongId()
    {
        $dispute = new DisputeSummary();
        $dispute->caseId = "DIS_SAND_aaaa0000";
        $document = new DisputeDocument();
        $document->type = 'SALES_RECEIPT';
        $document->b64_content = 'R0lGODlhigPCAXAAACwAAAAAigPCAYf///8AQnv';
        $documents[] = $document;

        $exceptionCaught = false;
        try {
            $dispute->challenge($documents)->execute();
        } catch (GatewayException $ex) {
            $exceptionCaught = true;
            $this->assertEquals("40060", $ex->responseCode);
            $this->assertContains("INVALID_DISPUTE_ACTION", $ex->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }
}