<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class TrackNumber extends Enum
{
    const UNKNOWN = "Unknown";
    const TRACK_ONE = "TrackOne";
    const TRACK_TWO = "TrackTwo";
    const BOTH_ONE_AND_TWO = "BothOneAndTwo";
}
