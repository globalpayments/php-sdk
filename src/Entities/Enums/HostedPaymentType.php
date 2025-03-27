<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class HostedPaymentType extends Enum
{
    const NONE = "NONE";
    const MAKE_PAYMENT = "MAKE_PAYMENT";
    const MAKE_PAYMENT_RETURN_TOKEN = "MAKE_PAYMENT_RETURN_TOKEN";
    const GET_TOKEN = "GET_TOKEN";
    const MY_ACCOUNT = "MY_ACCOUNT";
}