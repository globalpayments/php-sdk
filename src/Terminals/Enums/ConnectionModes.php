<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class ConnectionModes extends Enum
{
    const SERIAL = 'SERIAL';
    const TCP_IP = 'TCP_IP';
    const SSL_TCP = 'SSL_TCP';
    const HTTP = 'HTTP';
    const HTTPS = 'HTTPS';
}
