<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\DisputeDocument;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\DisputeSortProperty;
use GlobalPayments\Api\Entities\Enums\DisputeStage;
use GlobalPayments\Api\Entities\Enums\DisputeStatus;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\Entities\Reporting\DataServiceCriteria;
use GlobalPayments\Api\Entities\Reporting\DisputeSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class ReportingDisputesTest extends TestCase
{
    private $arn;
    private $startDate;
    private $endDate;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    #region Report Disputes

    public function testReportDisputeDetail()
    {
        $disputeId = 'DIS_SAND_abcd1234';
        $response = ReportingService::disputeDetail($disputeId)
            ->execute();
        $this->assertNotNull($response);
        $this->assertInstanceOf(DisputeSummary::class, $response);
        $this->assertEquals($disputeId, $response->caseId);
        $this->arn = $response->transactionARN;
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
        $this->testReportDisputeDetail();
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $this->arn)
            ->execute();

        $this->assertNotNull($disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($this->arn, $dispute->transactionARN);
        }
    }

    public function testReportFindDisputes_By_ARN_NotFound()
    {
        $arn = "874091790340471";
        try {
            ReportingService::findDisputesPaged(1, 10)
                ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
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
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::CARD_BRAND, $cardBrand)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($cardBrand, $dispute->transactionCardType);
        }
    }

    public function testReportFindDisputes_By_Status()
    {
        $disputeStatus = array("UNDER_REVIEW", "WITH_MERCHANT", "CLOSED");
        foreach ($disputeStatus as $value) {
            $disputes = ReportingService::findDisputesPaged(1, 10)
                ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
                ->andWith(SearchCriteria::DISPUTE_STATUS, $value)
                ->execute();

            $this->assertNotNull($disputes);
            $this->assertInstanceOf(PagedResult::class, $disputes);
            foreach ($disputes->result as $dispute) {
                $this->assertEquals($value, $dispute->caseStatus);
            }
        }
    }

    public function testReportFindDisputes_By_Stage()
    {
        $disputeStage = DisputeStage::CHARGEBACK;
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::DISPUTE_STAGE, $disputeStage)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($disputeStage, $dispute->caseStage);
        }
    }

    public function testReportFindDisputes_By_MerchantId_And_SystemHierarchy()
    {
        $merchantId = "8593872";
        $systemHierarchy = "111-23-099-002-005";
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, $merchantId)
            ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($merchantId, $dispute->caseMerchantId);
            $this->assertEquals($systemHierarchy, $dispute->merchantHierarchy);
        }
    }

    public function testReportFindDisputes_By_From_And_To_Stage_Time_Created()
    {
        $endDate = (new \DateTime())->modify('-10 days');
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::END_STAGE_DATE, $endDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        foreach ($disputes->result as $dispute) {
            $this->assertTrue($dispute->caseIdTime >= $this->startDate && $dispute->caseIdTime <= $this->endDate);
        }
    }

    public function testReportFindDisputes_Order_By_Id()
    {
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $disputesList = $disputes->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->caseId, $b->caseId);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($disputes->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindDisputes_Order_By_ARN()
    {
        // EndStageDate is mandatory in order to be able to sort by ARN
        $disputes = ReportingService::findDisputesPaged(1, 25)
            ->orderBy(DisputeSortProperty::ARN, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::END_STAGE_DATE, $this->endDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $disputesList = $disputes->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->transactionARN, $b->transactionARN);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($disputes->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindDisputes_Order_By_Brand()
    {
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::BRAND, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $disputesList = $disputes->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->transactionCardType, $b->transactionCardType);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($disputes->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindDisputes_Order_By_Status()
    {
        $disputes = ReportingService::findDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::STATUS, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $disputesList = $disputes->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->caseStatus, $b->caseStatus);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($disputes->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindDisputes_Order_By_Stage()
    {
        $disputes = ReportingService::findDisputesPaged(1, 20)
            ->orderBy(DisputeSortProperty::STAGE, SortDirection::DESC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $disputesList = $disputes->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->caseStage, $b->caseStage);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($disputes->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindDisputes_Order_By_Id_With_Brand_VISA()
    {
        $disputes = ReportingService::findDisputesPaged(1, 30)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::CARD_BRAND, CardType::VISA)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $disputesList = $disputes->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->caseId, $b->caseId);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($disputes->result as $index => $dispute) {
            $this->assertEquals(CardType::VISA, $dispute->transactionCardType);
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindDisputes_Order_By_Id_With_Stage_Chargeback()
    {
        $disputes = ReportingService::findDisputesPaged(1, 30)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::DISPUTE_STAGE, DisputeStage::CHARGEBACK)
            ->execute();

        $this->assertNotNull($disputes);
        $this->assertInstanceOf(PagedResult::class, $disputes);
        $disputesList = $disputes->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->caseId, $b->caseId);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($disputes->result as $index => $dispute) {
            $this->assertEquals(DisputeStage::CHARGEBACK, $dispute->caseStage);
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    #endregion

    #region Get a Document associated with a Dispute

    public function testFindDocumentAssociatedWithDispute()
    {
        $disputeId = 'DIS_SAND_abcd1235';
        $documentId = 'DOC_MyEvidence_234234AVCDE-1';
        $response = ReportingService::documentDisputeDetail($disputeId)
            ->where(SearchCriteria::DISPUTE_DOCUMENT_ID, $documentId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf(DisputeDocument::class, $response);
        $this->assertEquals($documentId, $response->id);
        $this->assertNotEmpty($response->b64_content);
    }

    public function testFindDocumentAssociatedWithDispute_RandomDisputeId()
    {
        $disputeId = GenerationUtils::getGuid();
        $documentId = 'DOC_MyEvidence_234234AVCDE-1';

        $exceptionCaught = false;
        try {
            ReportingService::documentDisputeDetail($disputeId)
                ->where(SearchCriteria::DISPUTE_DOCUMENT_ID, $documentId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40073', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - 101,Unable to locate dispute record for that ID. Please recheck the ID provided.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindDocumentAssociatedWithDispute_RandomDocumentId()
    {
        $disputeId = "DIS_SAND_abcd1235";
        $documentId = GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            ReportingService::documentDisputeDetail($disputeId)
                ->where(SearchCriteria::DISPUTE_DOCUMENT_ID, $documentId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40071', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - 128,No document found, please recheck the values provided', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindDocumentAssociatedWithDispute_MissingDocId()
    {
        $disputeId = 'DIS_SAND_abcd1235';
//        $documentId = 'DOC_MyEvidence_234234AVCDE-1';

        $exceptionCaught = false;
        try {
            ReportingService::documentDisputeDetail($disputeId)
                ->execute();
        } catch (BuilderException $e) {
            $exceptionCaught = true;
            $this->assertEquals('disputeDocumentId cannot be null for this transaction type.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindDocumentAssociatedWithDispute_EmptyDisputeId()
    {
        $disputeId = null;
        $documentId = 'DOC_MyEvidence_234234AVCDE-1';

        $exceptionCaught = false;
        try {
            ReportingService::documentDisputeDetail($disputeId)
                ->where(SearchCriteria::DISPUTE_DOCUMENT_ID, $documentId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('404', $e->responseCode);
            $this->assertEquals('Status Code: Invalid Resource. - Unproccesable resource found.', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    #endregion

    #region Settlement disputes
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

    public function testReportSettlementDispute_Order_By_Id_With_Status_Funded()
    {
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_DEPOSIT_DATE, $this->startDate)
            ->andWith(SearchCriteria::DISPUTE_STATUS, DisputeStatus::SETTLE_DISPUTE_FUNDED)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals(DisputeStatus::SETTLE_DISPUTE_FUNDED, $dispute->caseStatus);
        }
    }

    public function testReportFindSettlementDisputes_Order_By_Id()
    {
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $disputesList = $summary->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->caseId, $b->caseId);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($summary->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindSettlementDisputes_Order_By_ARN()
    {
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ARN, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $disputesList = $summary->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->transactionARN, $b->transactionARN);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($summary->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindSettlementDisputes_Order_By_Brand()
    {
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::BRAND, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $disputesList = $summary->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->transactionCardType, $b->transactionCardType);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($summary->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }
    }

    public function testReportFindSettlementDisputes_Order_By_Stage()
    {
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::STAGE, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $disputesList = $summary->result;
        uasort($disputesList, function ($a, $b) {
            return strcmp($a->caseStage, $b->caseStage);
        });
        /**
         * @var DisputeSummary $dispute
         */
        foreach ($summary->result as $index => $dispute) {
            $this->assertSame($disputesList[$index], $dispute);
        }

    }

    public function testReportFindSettlementDisputes_FilterBy_ARN()
    {
        $arn = '74500010037624410827759';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($arn, $dispute->transactionARN);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_ARN_NotFound()
    {
        $arn = '00000010037624410827111';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::AQUIRER_REFERENCE_NUMBER, $arn)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(sizeof($summary->result) == 0);
    }

    public function testReportFindSettlementDisputes_FilterBy_Brand()
    {
        $brand = 'VISA';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::CARD_BRAND, $brand)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($brand, $dispute->transactionCardType);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_Brand_NotFound()
    {
        $brand = 'MASTERCAR';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::CARD_BRAND, $brand)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(count($summary->result) == 0);
    }

    public function testReportFindSettlementDisputes_FilterBy_Stage()
    {
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(SearchCriteria::DISPUTE_STAGE, DisputeStage::CHARGEBACK)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals(DisputeStage::CHARGEBACK, $dispute->caseStage);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_FromAndToStageTimeCreated()
    {
        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::END_STAGE_DATE, $this->endDate)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertTrue($dispute->caseTime <= $this->endDate);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_SystemMidAndHierarchy()
    {
        $systemMid = '101023947262';
        $systemHierarchy = '055-70-024-011-019';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, $systemMid)
            ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        foreach ($summary->result as $dispute) {
            $this->assertEquals($systemHierarchy, $dispute->merchantHierarchy);
            $this->assertEquals($systemMid, $dispute->caseMerchantId);
        }
    }

    public function testReportFindSettlementDisputes_FilterBy_WrongSystemMid()
    {
        $systemHierarchy = '000-70-024-011-111';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::SYSTEM_HIERARCHY, $systemHierarchy)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(count($summary->result) == 0);
    }

    public function testReportFindSettlementDisputes_FilterBy_WrongSystemHierarchy()
    {
        $systemMid = '000023947222';

        $summary = ReportingService::findSettlementDisputesPaged(1, 10)
            ->orderBy(DisputeSortProperty::ID, SortDirection::ASC)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::MERCHANT_ID, $systemMid)
            ->execute();

        $this->assertNotNull($summary);
        $this->assertInstanceOf(PagedResult::class, $summary);
        $this->assertTrue(count($summary->result) == 0);
    }

    #endregion

    #region Accept and Challenge Dispute

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
            $this->assertStringContainsString("INVALID_DISPUTE_ACTION", $ex->getMessage());
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

    public function testReportDisputeChallenge_MultipleDocuments_ClosedStatus()
    {
        $dispute = new DisputeSummary();
        $dispute->caseId = "DIS_SAND_abcd1234";
        $document = new DisputeDocument();
        $document->type = 'SALES_RECEIPT';
        $document->b64_content = 'R0lGODlhigPCAXAAACwAAAAAigPCAYf///8AQnv';

        $secondDocument = new DisputeDocument();
        $secondDocument->type = 'SALES_RECEIPT';
        $secondDocument->b64_content = 'R0lGODlhigPCAXAAACwAAAAAigPCAYf///8AQnv';

        $documents[] = $document;
        $documents[] = $secondDocument;

        $exceptionCaught = false;
        try {
            $dispute->challenge($documents)->execute();
        } catch (GatewayException $ex) {
            $exceptionCaught = true;
            $this->assertEquals("40072", $ex->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - 131,The dispute stage, Retrieval, can be challenged with a single document only. Please correct the request and resubmit', $ex->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testReportDisputeChallenge_MissingDocument()
    {
        $dispute = new DisputeSummary();
        $dispute->caseId = "DIS_SAND_abcd1234";
        $document = new DisputeDocument();
        $documents[] = $document;

        $exceptionCaught = false;
        try {
            $dispute->challenge($documents)->execute();
        } catch (GatewayException $ex) {
            $exceptionCaught = true;
            $this->assertEquals("40065", $ex->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Unable to challenge as No document provided with the request', $ex->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
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
            $this->assertStringContainsString("INVALID_DISPUTE_ACTION", $ex->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    #endregion

    public function testFindSettlementDisputesPaged_FilterBy_DepositId()
    {
        $depositReference = 'DEP_2342423443';
        $disputes = ReportingService::findSettlementDisputesPaged(1, 10)
            ->where(DataServiceCriteria::START_STAGE_DATE, $this->startDate)
            ->andWith(DataServiceCriteria::DEPOSIT_REFERENCE, $depositReference)
            ->execute();

        $this->assertNotNull($disputes);
        /** @var DisputeSummary $dispute */
        $this->assertNotCount(0, $disputes->result);
        foreach ($disputes->result as $dispute) {
            $this->assertEquals($depositReference, $dispute->depositReference);
        }
    }
}