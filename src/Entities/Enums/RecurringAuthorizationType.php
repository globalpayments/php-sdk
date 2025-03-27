<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class RecurringAuthorizationType extends Enum
{
    const UNASSIGNED = 'Unassigned';
    const SIGNED_CONTRACT_INPLACE = "SignedContractInPlace";
    const NEED_TO_PRINT_CONTRACT = "NeedToPrintContract";
    const RECORDED_CALL_INPLACE = "RecordedCallInPlace";
}