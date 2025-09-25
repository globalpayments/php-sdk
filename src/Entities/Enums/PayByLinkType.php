<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PayByLinkType extends Enum
{
    const PAYMENT = 'PAYMENT';
    const HOSTED_PAYMENT_PAGE = 'HOSTED_PAYMENT_PAGE';
    const THIRD_PARTY_PAGE = 'THIRD_PARTY_PAGE';
    const EXCHANGE_APP_CREDENTIALS = 'EXCHANGE_APP_CREDENTIALS';
}