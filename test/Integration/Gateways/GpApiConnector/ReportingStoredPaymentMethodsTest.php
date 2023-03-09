<?php

namespace Gateways\GpApiConnector;

use DateTime;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\SortDirection;
use GlobalPayments\Api\Entities\Enums\StoredPaymentMethodSortProperty;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\StoredPaymentMethodSummary;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use PHPUnit\Framework\TestCase;

class ReportingStoredPaymentMethodsTest extends TestCase
{
    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        return BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
    }

    public function testFindStoredPaymentMethod_By_StartDateAndEndDate()
    {
        $startDate = (new DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $endDate = (new DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $paymentMethodsList = $response->result;
        uasort($paymentMethodsList, function ($a, $b) {
            return strcmp(($a->timeCreated)->format('Y-m-d H:i:s'), ($b->timeCreated)->format('Y-m-d H:i:s'));
        });

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertSame($paymentMethodsList[$index], $rs);
            $this->assertGreaterThanOrEqual($startDate, $rs->timeCreated);
            $this->assertLessThanOrEqual($endDate, $rs->timeCreated);
        }
    }

    public function testFindStoredPaymentMethod_By_LastUpdated()
    {
        $startDate = (new DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $endDate = (new DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::FROM_TIME_LAST_UPDATED, $startDate)
            ->andWith(SearchCriteria::TO_TIME_LAST_UPDATED, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertTrue(count($response->result) > 0);
    }

    public function testFindStoredPaymentMethod_By_LastUpdated_CurrentDay()
    {
        $currentDay = (new DateTime());

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::FROM_TIME_LAST_UPDATED, $currentDay)
            ->andWith(SearchCriteria::TO_TIME_LAST_UPDATED, $currentDay)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertTrue(count($response->result) > 0);
    }

    public function testFindStoredPaymentMethod_By_Id()
    {
        $paymentMethodId = 'PMT_3ad13ea3-6b43-4d1c-8075-aca4f61182ed';
        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::STORED_PAYMENT_METHOD_ID, $paymentMethodId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($paymentMethodId, $rs->paymentMethodId);
        }
    }

    public function testFindStoredPaymentMethod_By_RandomId()
    {
        $paymentMethodId = 'PMT_' . GenerationUtils::getGuid();

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::STORED_PAYMENT_METHOD_ID, $paymentMethodId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        $this->assertCount(0, $response->result);
    }

    public function testFindStoredPaymentMethod_By_Status()
    {
        $statuses = ['ACTIVE', 'DELETED'];
        foreach ($statuses as $status) {
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
    }

    public function testFindStoredPaymentMethod_By_Reference()
    {
        $reference = '5e3d3885-ceb3-a5ea-015c-945eaa4df8c8';
        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->orderBy(StoredPaymentMethodSortProperty::TIME_CREATED, SortDirection::ASC)
            ->where(SearchCriteria::REFERENCE_NUMBER, $reference)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals($reference, $rs->reference);
        }
    }

    public function testReportStoredPaymentMethodDetail()
    {
        $paymentMethodId = 'PMT_37c89e83-0349-4e19-add1-4b60d3c3d3ac';
        $response = ReportingService::storedPaymentMethodDetail($paymentMethodId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertInstanceOf(StoredPaymentMethodSummary::class, $response);
        $this->assertEquals($paymentMethodId, $response->paymentMethodId);
    }

    public function testReportStoredPaymentMethodDetail_NonExistentId()
    {
        $paymentMethodId = 'PMT_' . GenerationUtils::getGuid();
        $exceptionCaught = false;

        try {
            ReportingService::storedPaymentMethodDetail($paymentMethodId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40118', $e->responseCode);
            $this->assertEquals(sprintf('Status Code: RESOURCE_NOT_FOUND - PAYMENT_METHODS %s not found at this /ucp/payment-methods/%s', $paymentMethodId, $paymentMethodId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testReportStoredPaymentMethodDetail_RandomId()
    {
        $paymentMethodId = GenerationUtils::getGuid();
        $exceptionCaught = false;

        try {
            ReportingService::storedPaymentMethodDetail($paymentMethodId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40213', $e->responseCode);
            $this->assertEquals(sprintf('Status Code: INVALID_REQUEST_DATA - payment_method.id: %s contains unexpected data', $paymentMethodId), $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFindStoredPaymentMethod_By_CardInfo()
    {
        $card = new CreditCardData();
        $card->number = '4242424242424242';
        $card->expMonth = '12';
        $card->expYear = date('y', strtotime('+1 year'));

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->where(SearchCriteria::PAYMENT_METHOD, $card)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($card->expMonth, $rs->cardExpMonth);
            $this->assertEquals($card->expYear, $rs->cardExpYear);
            $this->assertEquals(substr_replace($card->number, 'xxxxxxxxxxxx', 0, -4), $rs->cardNumberLastFour);
        }
    }

    public function testFindStoredPaymentMethod_By_OnlyCardNumberInfo()
    {
        $card = new CreditCardData();
        $card->number = '4263970000005262';
        $card->expMonth = '12';
        $card->expYear = date('y', strtotime('+1 year'));

        $response = ReportingService::findStoredPaymentMethodsPaged(1, 10)
            ->where(SearchCriteria::PAYMENT_METHOD, $card)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var StoredPaymentMethodSummary $rs */
        foreach ($response->result as $index => $rs) {
            $this->assertEquals($card->expMonth, $rs->cardExpMonth);
            $this->assertEquals($card->expYear, $rs->cardExpYear);
            $this->assertEquals(substr_replace($card->number, 'xxxxxxxxxxxx', 0, -4), $rs->cardNumberLastFour);
        }
    }

    public function testFindStoredPaymentMethod_By_WithoutMandatoryCardNumber()
    {
        $card = new CreditCardData();
        $exceptionCaught = false;
        try {
            ReportingService::findStoredPaymentMethodsPaged(1, 10)
                ->where(SearchCriteria::PAYMENT_METHOD, $card)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('40005', $e->responseCode);
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields : number', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }
}