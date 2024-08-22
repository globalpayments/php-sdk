<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Terminals\Abstractions\IDeviceScreen;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;

class UDScreenResponse extends UpaResponseHandler implements IDeviceScreen
{
    public string $userData;

    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse(array $jsonResponse): void
    {
        parent::parseResponse($jsonResponse);

        if (empty($jsonResponse['data']['data'])) {
            return;
        }
        $secondDataNode = $jsonResponse['data']['data'];
        switch ($this->command) {
            case UpaMessageId::EXECUTE_UD_SCREEN:
                $this->userData = $secondDataNode['dataString'] ?? '';
                break;
            default:
                break;
        }
    }
}