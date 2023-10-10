<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class MerchantCategory extends Enum
{
    const HOTEL = 'HOTEL';
    const AIRLINE = 'AIRLINE';
    const RETAIL = 'RETAIL';
    const TOP_UP = 'TOP_UP';
    const PLAYER = 'PLAYER';
    const CD_KEY = 'CD_KEY';
    const OTHER = 'OTHER';
}