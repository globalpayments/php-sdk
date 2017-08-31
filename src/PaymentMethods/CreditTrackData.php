<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;

class CreditTrackData extends Credit implements ITrackData
{
    public $entryMethod;
    public $value;
}
