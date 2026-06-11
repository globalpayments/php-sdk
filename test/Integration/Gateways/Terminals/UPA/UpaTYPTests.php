<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\UPA\Responses\TransactionResponse;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

/**
 * TYP integration tests for Mexico GP-API Sandbox using live UPA device responses.
 */
class UpaTYPTests extends TestCase
{
    private IDeviceInterface $device;

    public function setup(): void
    {
        $this->device = DeviceService::create($this->getConfig());
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    protected function getConfig(): ConnectionConfig
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.71.118';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_VERIFONE_T650P;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    /** Sale TYP fields: all present when TYP enabled, all null when disabled. */
    public function testSaleTypFieldConsistency(): void
    {
        /** @var TransactionResponse $response */
        $response = $this->device->sale(10)->execute();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);

        if ($response->redeemId !== null) {
            // TYP enabled: all sale TYP fields must be present
            $this->assertNotNull($response->redeemStatus);
            $this->assertNotNull($response->currencyAmountRedeemed);
            $this->assertNotNull($response->pointsRedeemed);
            $this->assertNotNull($response->discountAmountRedeemed);
        } else {
            // TYP disabled: all sale TYP fields must be null
            $this->assertNull($response->redeemStatus);
            $this->assertNull($response->currencyAmountRedeemed);
            $this->assertNull($response->pointsRedeemed);
            $this->assertNull($response->discountAmountRedeemed);
        }
    }

    /** Void TYP fields: all present when TYP enabled, all null when disabled. */
    public function testVoidTypFieldConsistency(): void
    {
        $saleResponse = $this->device->sale(10)->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
        $this->assertNotNull($saleResponse->transactionId);

        sleep(10);

        /** @var TransactionResponse $voidResponse */
        $voidResponse = $this->device->void()
            ->withTransactionId($saleResponse->transactionId)
            ->execute();

        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->deviceResponseCode);

        if ($voidResponse->voidRedeemId !== null) {
            // TYP enabled: all void TYP fields must be present
            $this->assertNotNull($voidResponse->voidRedeemStatus);
            $this->assertNotNull($voidResponse->voidCurrencyAmountRedeemed);
            $this->assertNotNull($voidResponse->voidPointsRedeemed);
            $this->assertNotNull($voidResponse->voidDiscountAmountRedeemed);
            $this->assertEquals($voidResponse->voidCurrencyAmountRedeemed, $voidResponse->voicCurrencyAmountRedeemed);
        } else {
            // TYP disabled: all void TYP fields must be null
            $this->assertNull($voidResponse->voidRedeemStatus);
            $this->assertNull($voidResponse->voidCurrencyAmountRedeemed);
            $this->assertNull($voidResponse->voidPointsRedeemed);
            $this->assertNull($voidResponse->voidDiscountAmountRedeemed);
        }
    }

    /** Reversal TYP fields: all present when TYP enabled, all null when disabled. */
    public function testReversalTypFieldConsistency(): void
    {
        $saleResponse = $this->device->sale(10)->execute();

        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
        $this->assertNotNull($saleResponse->terminalRefNumber);

        sleep(10);

        /** @var TransactionResponse $reverseResponse */
        $reverseResponse = $this->device->reverse()
            ->withTerminalRefNumber($saleResponse->terminalRefNumber)
            ->withAmount('10.00')
            ->withEcrId('1')
            ->execute();

        $this->assertNotNull($reverseResponse);
        $this->assertEquals('00', $reverseResponse->deviceResponseCode);

        if ($reverseResponse->voidRedeemId !== null) {
            // TYP enabled: all reverse TYP fields must be present
            $this->assertNotNull($reverseResponse->voidRedeemStatus);
            $this->assertNotNull($reverseResponse->voidCurrencyAmountRedeemed);
            $this->assertNotNull($reverseResponse->voidPointsRedeemed);
            $this->assertNotNull($reverseResponse->voidDiscountAmountRedeemed);
        } else {
            // TYP disabled: all reverse TYP fields must be null
            $this->assertNull($reverseResponse->voidRedeemStatus);
            $this->assertNull($reverseResponse->voidCurrencyAmountRedeemed);
            $this->assertNull($reverseResponse->voidPointsRedeemed);
            $this->assertNull($reverseResponse->voidDiscountAmountRedeemed);
        }
    }

    /** POSITIVE: Summary report should accept TYP report parameters. */
    public function testSummaryReportWithTypParameters(): void
    {
        $response = $this->device->getBatchDetails(
            batchId: '1009830',
            printReport: false,
            reportType: 'summary',
            reportSubType: '1',
            bothReports: '0',
            clerkId: '12',
            previousBatchReport: '0'
        );

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }

    /** NEGATIVE: Summary report with optional TYP params omitted should still execute successfully. */
    public function testSummaryReportWithoutTypParameters(): void
    {
        $response = $this->device->getBatchDetails(batchId: '1009830');

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }

    /** POSITIVE: Detail report should accept TYP report parameters. */
    public function testDetailReportWithTypParameters(): void
    {
        $response = $this->device->getBatchDetails(
            batchId: '1009830',
            printReport: false,
            reportType: null,
            reportSubType: '2',
            bothReports: '1',
            clerkId: '99',
            previousBatchReport: '1'
        );

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }

    /** NEGATIVE: Detail report with optional TYP params omitted should still execute successfully. */
    public function testDetailReportWithoutTypParameters(): void
    {
        $response = $this->device->getBatchDetails(
            batchId: '1009830',
            printReport: false,
            reportType: null,
            reportSubType: null,
            bothReports: null,
            clerkId: null,
            previousBatchReport: null
        );

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }

    /** Both reports requested via bothReports flag. */
    public function testBatchDetailsBothReports(): void
    {
        $response = $this->device->getBatchDetails(
            batchId: '1009830',
            printReport: false,
            bothReports: '1'
        );

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }

    /** Report filtered by clerkId. */
    public function testBatchDetailsWithClerkFilter(): void
    {
        $response = $this->device->getBatchDetails(
            batchId: '1009830',
            printReport: false,
            reportType: 'summary',
            reportSubType: '2',
            clerkId: '123'
        );

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }

    /** Report with previousBatchReport flag. */
    public function testBatchDetailsWithPreviousBatch(): void
    {
        $response = $this->device->getBatchDetails(
            batchId: '1009830',
            printReport: false,
            reportType: 'detail',
            reportSubType: '1',
            previousBatchReport: '1'
        );

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }

    /** Report with all-clerk filter (reportSubType 3). */
    public function testBatchDetailsWithAllClerkFilter(): void
    {
        $response = $this->device->getBatchDetails(
            batchId: '1009830',
            printReport: false,
            reportType: 'detail',
            reportSubType: '3'
        );

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
    }
}
