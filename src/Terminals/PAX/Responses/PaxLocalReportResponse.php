<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\Abstractions\ITerminalReport;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;

class PaxLocalReportResponse extends PaxTerminalResponse implements ITerminalReport
{

    public ?string $totalReportRecords = null;
    public ?string $reportRecordNumber = null;
    public ?string $edcType = null;
    public ?string $originalTransactionType = null;

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
