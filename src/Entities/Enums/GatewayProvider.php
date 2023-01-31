<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class GatewayProvider extends Enum
{
    const PORTICO         = 'PORTICO';
    const GP_ECOM         = 'GP_ECOM';
    const GENIUS          = 'GENIUS';
    const TRANSIT         = 'TRANSIT';
    const GP_API          = 'GP-API';
    const TRANSACTION_API = 'TRANSACTION-API';
}
