<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Responses\PaxDeviceResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;

class PaxGiftResponse extends PaxDeviceResponse
{

    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, [PaxMessageId::T07_RSP_DO_GIFT,
            PaxMessageId::T09_RSP_DO_LOYALTY]);
    }

    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);
        $this->mapResponse($messageReader);
    }
}
