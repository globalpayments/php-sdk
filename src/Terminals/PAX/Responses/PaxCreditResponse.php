<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Responses\PaxDeviceResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;

class PaxCreditResponse extends PaxDeviceResponse
{

    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, PaxMessageId::T01_RSP_DO_CREDIT);
    }

    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);
        $this->mapResponse($messageReader);
    }
}
