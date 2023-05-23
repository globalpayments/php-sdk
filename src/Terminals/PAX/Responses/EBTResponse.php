<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Responses\PaxTerminalResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;

class EBTResponse extends PaxTerminalResponse
{

    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, [PaxMessageId::T05_RSP_DO_EBT]);
    }

    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);
        $this->mapResponse($messageReader);
    }
}
