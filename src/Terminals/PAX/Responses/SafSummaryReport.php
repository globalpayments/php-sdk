<?php
namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\PAX\SubGroups\HostResponse;

class SafSummaryReport extends PaxDeviceResponse
{

    public $safTotalCount;

    public $safTotalAmount;

    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, PaxMessageId::R11_RSP_SAF_SUMMARY_REPORT);
    }

    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);

        if ($this->deviceResponseCode == '000000') {
            $this->safTotalCount = (int) $messageReader->readToCode(ControlCodes::FS);
            $this->safTotalAmount = (int) $messageReader->readToCode(ControlCodes::FS);
        }
    }
}
