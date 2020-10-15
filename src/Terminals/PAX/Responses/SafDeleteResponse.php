<?php
namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\PAX\SubGroups\HostResponse;

class SafDeleteResponse extends PaxDeviceResponse
{

    public $safDeletedCount;

    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, PaxMessageId::B11_RSP_DELETE_SAF_FILE);
    }

    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);

        if ($this->deviceResponseCode == '000000') {
            $this->safDeletedCount = (int) $messageReader->readToCode(ControlCodes::FS);
        }
    }
}
