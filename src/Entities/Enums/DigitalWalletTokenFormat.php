<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class DigitalWalletTokenFormat extends Enum
{
    const CARD_NUMBER = 'CARD_NUMBER';
    const CARD_TOKEN = 'CARD_TOKEN';
}