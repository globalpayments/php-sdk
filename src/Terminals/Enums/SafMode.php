<?php
namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class SafMode extends Enum
{

    const STAY_ONLINE = "0";

    const STAY_OFFLINE = "1";

    const OFFLINE_TILL_BATCH = "2";

    const OFFLINE_ONDEMAND_OR_AUTO = "3";
}
