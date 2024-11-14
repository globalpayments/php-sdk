<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Exceptions\MessageException;
use GlobalPayments\Api\Terminals\Abstractions\ISignatureResponse;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;

class SignatureResponse extends UpaResponseHandler implements ISignatureResponse
{
    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse($jsonResponse): void
    {
        parent::parseResponse($jsonResponse);
        $firstDataNode = $this->isGpApiResponse($jsonResponse) ? $jsonResponse['response'] : $jsonResponse['data'];
        if (empty($firstDataNode['data'])) {
            throw new MessageException(self::INVALID_RESPONSE_FORMAT);
        }
        switch ($this->command) {
            case UpaMessageId::GET_SIGNATURE:
                $this->signatureData = $firstDataNode['data']['signatureData'] ?? null;
                break;
        }
    }

}