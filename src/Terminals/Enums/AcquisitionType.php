<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class AcquisitionType extends Enum
{
    const CONTACT = 'CONTACT';
    const CONTACTLESS = 'CONTACTLESS';
    const SWIPE = 'SWIPE';
    const MANUAL = 'MANUAL';
    const SCAN = 'SCAN';
}