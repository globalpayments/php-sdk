<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\MessageException;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Utils\ArrayUtils;

class UpaResponseHandler extends TerminalResponse
{
    const INVALID_RESPONSE_FORMAT = "The response received is not in the proper format.";

    private function checkResponse(array $commandResult): void
    {
        if (!empty($commandResult['result']) && $commandResult['result'] === 'Failed') {
            throw new GatewayException(
                sprintf(
                    'Unexpected Gateway Response: %s - %s',
                    $commandResult['errorCode'],
                    $commandResult['errorMessage']
                ),
                $commandResult['errorCode'],
                $commandResult['errorMessage']
            );
        }
    }

    protected function parseResponse(array $response): void
    {
        $firstNodeData = $this->isGpApiResponse($response) ? $response['response'] : $response['data'];
        if (empty($firstNodeData['cmdResult'])) {
            throw new MessageException(self::INVALID_RESPONSE_FORMAT);
        }
        $this->checkResponse($firstNodeData['cmdResult']);
        if ($this->isGpApiResponse($response)) {
            $this->status = $response['status'] ?? null;
            $this->transactionId = $response['id'] ?? null;
            $this->deviceResponseText = $response['status'] ?? null;
        } else {
            $this->status = $firstNodeData['cmdResult']['result'] ?? null;
        }

        $this->deviceResponseCode = in_array($this->status, ['Success', 'COMPLETE']) ? '00' : null;
        $this->command = $firstNodeData['response'] ?? null;
        $this->requestId = $firstNodeData['requestId'] ?? '';
        $this->ecrId = $firstNodeData['EcrId'] ?? '';
    }

    /**
     * @throws MessageException
     */
    protected function parseJsonResponse($response): void
    {
        $response = ArrayUtils::jsonToArray($response);
        $this->parseResponse($response);
    }

    protected function isGpApiResponse($jsonResponse) : bool
    {
        if (is_object($jsonResponse)) {
            $jsonResponse = ArrayUtils::jsonToArray($jsonResponse);
        }
        return !empty($jsonResponse['provider']) && $jsonResponse['provider'] === GatewayProvider::GP_API;
    }
}
