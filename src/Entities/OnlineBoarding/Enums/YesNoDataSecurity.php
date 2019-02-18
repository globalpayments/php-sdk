<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class YesNoDataSecurity extends Enum
{
    const YES = 'OptOut';
    const NO = 'No';
    const IHAVENEVERACCEPTEDPAYMENTCARDS = 'NeverAcceptedPaymentCards';
    const NA = 'N/A';
}
