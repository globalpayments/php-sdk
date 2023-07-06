<?php

namespace GlobalPayments\Api\Terminals\UPA;

class UpaMessageType
{
    const ACK = 'ACK';
    const NAK = 'NAK';
    const READY = 'READY';
    const BUSY = 'BUSY';
    const TO = 'TO';
    const MSG = 'MSG';
}