<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Responses\PaxDeviceResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class InitializeResponse extends PaxDeviceResponse
{

    public $serialNumber;
    public $modelName;
    public $osVersion;
    
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
