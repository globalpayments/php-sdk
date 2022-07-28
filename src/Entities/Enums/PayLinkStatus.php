<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PayLinkStatus  extends Enum
{
    const  ACTIVE = 'ACTIVE';
    const INACTIVE = 'INACTIVE';
    const CLOSED = 'CLOSED';
    const EXPIRED = 'EXPIRED';
    const PAID = 'PAID';
}