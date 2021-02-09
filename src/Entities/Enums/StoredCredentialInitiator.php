<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class StoredCredentialInitiator extends Enum
{
    const CARDHOLDER = 'cardholder';
    const MERCHANT = 'merchant';
    const SCHEDULED = 'scheduled';
    const PAYER = 'PAYER';

    public static $mapInitiator = [
        self::CARDHOLDER => [
            Target::Realex => self::CARDHOLDER,
            Target::Portico => 'C',
            Target::GP_API => self::PAYER
        ],
        self::MERCHANT => [
            Target::Realex => self::MERCHANT,
            Target::Portico => 'M',
            Target::GP_API => self::MERCHANT
        ],
        self::SCHEDULED => [
            Target::Realex => self::SCHEDULED
        ]
    ];
}
