<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Responses\PaxDeviceResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;

class PaxLocalReportResponse extends PaxDeviceResponse
{

    public $totalReportRecords;
    public $reportRecordNumber;
    public $edcType;
    public $originalTransactionType;

    public function __construct($rawResponse)
    {
        parent::__construct($rawResponse, PaxMessageId::R03_RSP_LOCAL_DETAIL_REPORT);
    }
    
    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);
        $this->mapLocalReportResponse($messageReader);
    }
}
