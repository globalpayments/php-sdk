<?php
namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class SafDelete extends Enum
{

    const NEWLY_STORED_TRANSACTION = "0";

    const FAILED_TRANSACTION = "1";

    const DELETE_ALL_SAF_RECORD = "2";
}
