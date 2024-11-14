<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\MessageException;
use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\DeviceResponse;
use GlobalPayments\Api\Utils\ArrayUtils;

class BatchList extends UpaResponseHandler implements IBatchCloseResponse
{
    const INVALID_RESPONSE_FORMAT = "The response received is not in the proper format.";
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

    /**
     * @throws GatewayException
     * @throws MessageException
     */
    protected function parseResponse($jsonResponse): void
    {
        parent::parseResponse(ArrayUtils::jsonToArray($jsonResponse));
        $firstDataNode = $this->isGpApiResponse($jsonResponse) ? $jsonResponse->response : ($jsonResponse->data ?? null);
        if (empty($firstDataNode) || empty($firstDataNode->cmdResult)) {
            throw new GatewayException(self::INVALID_RESPONSE_FORMAT);
        }
        $secondNode = $firstDataNode->data;
        $cmdResult = $firstDataNode->cmdResult;

        $this->status = $cmdResult->result ?? null;
        $this->command = $firstDataNode->response;
        $this->ecrId = $firstDataNode->EcrId ?? null;
        $this->referenceNumber = $jsonResponse->id ?? null;
        $this->requestId = $firstDataNode->requestId ?? null;
        $this->deviceResponseCode = in_array($this->status, ['Success', 'COMPLETE']) ? '00' : null;
        $batches = $secondNode->batchesAvail ?? null;
        foreach ($batches as $batch) {
            $this->batchIds[] = $batch->batchId;
        }
    }
}