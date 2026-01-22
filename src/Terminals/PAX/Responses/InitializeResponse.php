<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Responses\PaxTerminalResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class InitializeResponse extends PaxTerminalResponse
{

    public ?string $serialNumber = null;
    public ?string $modelName = null;
    public ?string $osVersion = null;
    
    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, PaxMessageId::A01_RSP_INITIALIZE);
    }

    public function parseResponse($messageReader)
    {
        
        parent::parseResponse($messageReader);
        
        $this->serialNumber = $messageReader->readToCode(ControlCodes::FS);
        $this->modelName = $messageReader->readToCode(ControlCodes::FS);
        $this->osVersion = $messageReader->readToCode(ControlCodes::FS);
    }
}
