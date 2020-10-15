<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CardHolderAuthenticationEntity extends Enum
{
    const NOT_AUTHENTICATED                 = 'NOT_AUTHENTICATED';
    const ICC_OFFLINE_PIN                   = 'ICC_OFFLINE_PIN';
    const CARD_ACCEPTANCE_DEVICE            = 'CARD_ACCEPTANCE_DEVICE';
    const AUTHORIZING_AGENT_ONLINE_PIN      = 'AUTHORIZING_AGENT_ONLINE_PIN';
    const MERCHANT_CARD_ACCEPTOR_SIGNATURE  = 'MERCHANT_CARD_ACCEPTOR_SIGNATURE';
    const OTHER                             = 'OTHER';
}
