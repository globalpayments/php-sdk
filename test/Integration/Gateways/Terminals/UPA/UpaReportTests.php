<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\ReportOutput;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaSearchCriteria;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchList;
use GlobalPayments\Api\Terminals\UPA\Responses\BatchReportResponse;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

class UpaReportTests extends TestCase
{
    private IDeviceInterface $device;

    /**
     * @throws ApiException
     */
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
        $config->deviceType = DeviceType::UPA_DEVICE;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->ipAddress = '192.168.8.181';
        $config->port = '8081';
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testGetSAFReport()
    {
        $report = $this->device->getSAFReport()
            ->where(UpaSearchCriteria::ECR_ID, 13)
            ->andCondition(UpaSearchCriteria::REPORT_OUTPUT, ReportOutput::PRINT)
            ->execute();

        $this->assertNotNull($report);
        $this->assertEquals('Success', $report->status);
    }

    public function testGetBatchReport()
    {
        $batchId = '1009830';
        $report = $this->device->getBatchReport()
            ->where(UpaSearchCriteria::BATCH, $batchId)
            ->andCondition(UpaSearchCriteria::ECR_ID, 13)
            ->execute();

        $this->assertNotNull($report);
        $this->assertEquals('Success', $report->status);
    }

    public function testGetBatchDetails()
    {
        /** @var BatchReportResponse $response */
        $response = $this->device->getBatchDetails('1009830');

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
        $this->assertNotNull($response->batchRecord);
        $this->assertNotNull($response->batchRecord->transactionDetails);
        $this->assertEquals('1009830', $response->batchRecord->batchId);
    }

    public function testFindAvailableBatches()
    {
        $this->device->ecrId = '12';
        /** @var BatchList $response */
        $response = $this->device->findBatches()->execute();

        $this->assertNotNull($response);
        $this->assertEquals('Success', $response->status);
        $this->assertIsArray($response->batchIds);
        $this->assertGreaterThan(0, $this->count($response->batchIds));
    }
}