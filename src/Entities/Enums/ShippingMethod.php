<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ShippingMethod extends Enum
{
    const BILLING_ADDRESS = 'BILLING_ADDRESS';
    const VERIFIED_ADDRESS = 'ANOTHER_VERIFIED_ADDRESS';
    const UNVERIFIED_ADDRESS = 'UNVERIFIED_ADDRESS';
    const SHIP_TO_STORE = 'SHIP_TO_STORE';
    const DIGITAL_GOODS = 'DIGITAL_GOODS';
    const TRAVEL_AND_EVENT_TICKETS = 'TRAVEL_AND_EVENT_TICKETS';
    const OTHER = 'OTHER';
}
