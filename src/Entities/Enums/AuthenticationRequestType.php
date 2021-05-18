<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class AuthenticationRequestType extends Enum
{
    const PAYMENT_TRANSACTION = "PAYMENT_TRANSACTION";
    const RECURRING_TRANSACTION = "RECURRING_TRANSACTION";
    const INSTALMENT_TRANSACTION = "INSTALMENT_TRANSACTION";
    const ADD_CARD = "ADD_CARD";
    const MAINTAIN_CARD = "MAINTAIN_CARD";
    const CARDHOLDER_VERIFICATION = "CARDHOLDER_VERIFICATION";
}
