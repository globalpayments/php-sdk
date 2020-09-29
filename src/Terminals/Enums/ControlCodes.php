<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class ControlCodes extends Enum
{
    const STX = 0x02; // Denotes the beginning of a message frame
    const ETX = 0x03; // Denotes the ending of a message frame
    const EOT = 0x04; // Indicates communication session terminated
    const ENQ = 0x05; // Begin Session sent from the host to the POS
    const ACK = 0x06; // Acknowledge of message received
    const NAK = 0x15; // Indicates invalid message received
    const FS = 0x1C;  // Field separator
    const GS = 0x1D;  // Message ID follows (for non-PIN entry prompts)
    const RS = 0x1E;  // Message ID follows (for PIN entry prompts)

    // PAX Specific ??
    const US = 0x1F;
    const COMMA = 0x2C;
    const COLON = 0x3A;
    const PTGS = 0x7C;
}
