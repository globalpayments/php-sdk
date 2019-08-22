<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ChallengeWindowSize extends Enum
{
    const WINDOWED_250X400 = "WINDOWED_250X400";
    const WINDOWED_390X400 = "WINDOWED_390X400";
    const WINDOWED_500X600 = "WINDOWED_500X600";
    const WINDOWED_600X400 = "WINDOWED_600X400";
    const FULL_SCREEN = "FULL_SCREEN";
}
