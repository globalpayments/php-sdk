<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class ControlCodes extends Enum
{
    const STX = 0x02;
    const ETX = 0x03;
    const ACK = 0x06;
    const NAK = 0x15;
    const ENQ = 0x05;
    const FS = 0x1C;
    const GS = 0x1D;
    const EOT = 0x04;
    
    // PAX Specific ??
    const US = 0x1F;
    const RS = 0x1E;
    const COMMA = 0x2C;
    const COLON = 0x3A;
    const PTGS = 0x7C;
}
