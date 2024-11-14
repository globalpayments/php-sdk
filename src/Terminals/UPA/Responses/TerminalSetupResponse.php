<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Enums\DeviceConfigType;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;

class TerminalSetupResponse extends UpaResponseHandler implements IBatchCloseResponse
{
    public string|DeviceConfigType $configType;

    public ?string $fileContent;

    public ?int $fileLength;

    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse($jsonResponse): void
    {
        parent::parseResponse($jsonResponse);
        $firstDataNode = $this->isGpApiResponse($jsonResponse) ? $jsonResponse['response'] : $jsonResponse['data'];
        if (empty($firstDataNode['data'])) {
            return;
        }
        $secondNode = $firstDataNode['data'];
        switch ($this->command) {
            case UpaMessageId::GET_CONFIG_CONTENTS:
                $this->configType = $secondNode['configType'] ?? '';
                $this->fileContent = $secondNode['fileContents'] ?? null;
                $this->fileLength = $secondNode['length'] ?? null;
                break;
            default:
                break;
        }
    }
}