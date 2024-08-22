<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\DeviceResponse;

class BatchList extends DeviceResponse implements IBatchCloseResponse
{
    const INVALID_RESPONSE_FORMAT = "The response received is not in the proper format.";

    public ?string $ecrId;

    public array $batchIds = [];

    /**
     * BatchReportResponse constructor.
     * @param $jsonResponse
     * @throws GatewayException
     */
    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    protected function parseResponse($jsonResponse)
    {
        if (empty($jsonResponse->data) || empty($jsonResponse->data->cmdResult)) {
            throw new GatewayException(self::INVALID_RESPONSE_FORMAT);
        }
        $firstDataNode = $jsonResponse->data;
        $cmdResult = $firstDataNode->cmdResult;

        $this->status = $cmdResult->result ?? null;
        $this->command = $firstDataNode->response;
        $this->ecrId = $firstDataNode->ecrId ?? null;
        if (empty($this->status) || $this->status !== 'Success') {
            $this->deviceResponseText = sprintf("Error: %s - %s", $cmdResult->errorCode, $cmdResult->errorMessage);
            return;
        }
        $batches = $firstDataNode->data->batchesAvail ?? null;
        foreach ($batches as $batch) {
            $this->batchIds[] = $batch->batchId;
        }
    }
}