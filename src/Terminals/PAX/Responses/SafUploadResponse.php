<?php
namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\PAX\SubGroups\HostResponse;

class SafUploadResponse extends PaxDeviceResponse
{

    public $totalCount;

    public $totalAmount;

    public $timeStamp;

    public $safUploadedCount;

    public $safUploadedAmount;

    public $safFailedCount;

    public $safFailedTotal;

    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, PaxMessageId::B09_RSP_SAF_UPLOAD);
    }

    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);

        if ($this->deviceResponseCode == '000000') {
            $this->totalCount = (int) $messageReader->readToCode(ControlCodes::FS);
            $totalAmount = $messageReader->readToCode(ControlCodes::FS);
            $this->totalAmount = isset($totalAmount) ? TerminalUtils::reformatAmount($totalAmount) : '';
            
            $this->timeStamp = (int) $messageReader->readToCode(ControlCodes::FS);
            $this->safUploadedCount = (int) $messageReader->readToCode(ControlCodes::FS);
            $safUploadedAmount = $messageReader->readToCode(ControlCodes::FS);
            $this->safUploadedAmount = isset($safUploadedAmount) ? TerminalUtils::reformatAmount($safUploadedAmount) : '';
            
            $this->safFailedCount = (int) $messageReader->readToCode(ControlCodes::FS);
            $this->safFailedTotal = (int) $messageReader->readToCode(ControlCodes::FS);
        }
    }
}
