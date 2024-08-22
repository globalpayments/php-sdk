<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class DebugLevel extends Enum
{
    const NOLOGS = 0;
    const ERROR = 1;
    const WARNING = 2;
    const FLOW = 4;
    const MESSAGE = 8;
    const DATA = 16;
    const PACKETS = 32;
    const PIA = 64;
    const PERF = 128;
}