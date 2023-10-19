<?php

namespace GlobalPayments\Api\Terminals\Diamond\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class AuthorizationType extends Enum
{
    const ONLINE = '1';
    const OFFLINE = '3';
    const REFERRAL = '4';
}