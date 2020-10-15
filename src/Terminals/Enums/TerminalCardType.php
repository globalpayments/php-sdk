<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class TerminalCardType extends Enum
{

    const VISA = "01";
    const MASTERCARD = "02";
    const AMEX = "03";
    const DISCOVER = "04";
    const DINER_CLUB = "05";
    const EN_ROUTE = "06";
    const JCB = "07";
    const REVOLUTION_CARD = "08";
    const VISA_FLEET = "09";
    const MASTERCARD_FLEET = "10";
    const FLEET_ONE = "11";
    const FLEET_WIDE = "12";
    const FUEL_MAN = "13";
    const GAS_CARD = "14";
    const VOYAGER = "15";
    const WRIGHT_EXPRESS = "16";
    const OTHER = "99";
}
