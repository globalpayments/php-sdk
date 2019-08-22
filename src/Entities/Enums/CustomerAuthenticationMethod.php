<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CustomerAuthenticationMethod extends Enum
{
    const NOT_AUTHENTICATED = 'NOT_AUTHENTICATED';
    const MERCHANT_SYSTEM = 'MERCHANT_SYSTEM_AUTHENTICATION';
    const FEDERATED_ID = 'FEDERATED_ID_AUTHENTICATION';
    const ISSUER_CREDENTIAL = 'ISSUER_CREDENTIAL_AUTHENTICATION';
    const THIRD_PARTY = 'THIRD_PARTY_AUTHENTICATION';
    const FIDO = 'FIDO_AUTHENTICATION';
}
