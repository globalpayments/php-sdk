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
        $cmdResult = $commandResult['cmdResult'] ?? ($commandResult['data']['cmdResult'] ?? null);
        if (!empty($cmdResult['result']) && $cmdResult['result'] === 'Failed') {
            $errorCode = $cmdResult['errorCode'] ?? '';
            $errorMessage = $cmdResult['errorMessage'] ?? '';
            $host = $commandResult['data']['host'] ?? [];
            $gatewayResponseCode = $host['gatewayResponseCode'] ?? '';
            $gatewayResponseMessage = $host['gatewayResponseMessage'] ?? '';
            $issuerResponseCode = $host['responseCode'] ?? '';
            $issuerResponseMessage = $host['responseText'] ?? '';
            $fullMessage = sprintf(
                'Unexpected Gateway Response: %s - %s | GatewayResponseCode: %s | GatewayResponseMessage: %s | IssuerResponseCode: %s | IssuerResponseMessage: %s',
                $errorCode,
                $errorMessage,
                $gatewayResponseCode,
                $gatewayResponseMessage,
                $issuerResponseCode,
                $issuerResponseMessage
            );
            throw new GatewayException(
                $fullMessage,
                $errorCode,
                $errorMessage
            );
        }
    }

    protected function parseResponse(array $response): void
    {
        $firstNodeData = $this->isGpApiResponse($response) ? $response['response'] : $response['data'];
        if (empty($firstNodeData['cmdResult'])) {
            throw new MessageException(self::INVALID_RESPONSE_FORMAT);
        }
        $this->checkResponse($firstNodeData);
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
