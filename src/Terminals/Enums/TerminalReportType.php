<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class TerminalReportType extends Enum
{
    const LOCAL_DETAIL_REPORT = 'LocalDetailReport';
    const GET_SAF_REPORT = 'GetSAFReport';
    const GET_BATCH_REPORT = 'GetBatchReport';
    const GET_BATCH_DETAILS = 'GetBatchDetails';
    const FIND_BATCHES = 'GetBatches';
    const GET_OPEN_TAB_DETAILS = 'GetOpenTabDetails';
}
