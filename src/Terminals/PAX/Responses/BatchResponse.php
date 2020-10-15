<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\PAX\SubGroups\HostResponse;

class BatchResponse extends PaxDeviceResponse
{

    public $totalCount;
    public $totalAmount;
    public $timeStamp;
    public $tid;
    public $mid;
    public $batchNumber;
    public $sequenceNumber;
    
    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, PaxMessageId::B01_RSP_BATCH_CLOSE);
    }

    public function parseResponse($messageReader)
    {
        
        parent::parseResponse($messageReader);
        
        $hostResponse = new HostResponse($messageReader);
        $this->totalCount = $messageReader->readToCode(ControlCodes::FS);
        $this->totalAmount = $messageReader->readToCode(ControlCodes::FS);
        $this->timeStamp = $messageReader->readToCode(ControlCodes::FS);
        $this->tid = $messageReader->readToCode(ControlCodes::FS);
        $this->mid = $messageReader->readToCode(ControlCodes::FS);
        
        if (!empty($hostResponse->batchNumber)) {
            $this->batchNumber = $hostResponse->batchNumber;
        }
    }
}
