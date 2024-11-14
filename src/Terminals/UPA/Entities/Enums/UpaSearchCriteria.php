<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class UpaSearchCriteria extends Enum
{
    const ECR_ID = 'ecrId';
    const BATCH = 'batch';
    const REPORT_OUTPUT = 'reportOutput';
    const REPORT_TYPE = 'reportType';
}