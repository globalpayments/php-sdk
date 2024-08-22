<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class DeviceConfigType extends Enum
{
    const CONTACT_TERMINAL_CONFIG = '1';
    const CONTACT_CARD_DATA_CONFIG = '2';
    const CONTACT_CAPK = '3';
    const CONTACTLESS_TERMINAL_CONFIG = '4';
    const CONTACTLESS_CARD_DATA_CONFIG = '5';
    const CONTACTLESS_CAPK = '6';
    const AID_LIST = '7';
    const MODIFY_AIDS = '8';
}