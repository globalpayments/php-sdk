<?php
namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class SafReportSummary extends Enum
{

    const NEWLY_STORED_RECORD_REPORT = "0";

    const FAILED_RECORD_REPORT = "1";

    const ALL_REPORT = "2";
}
