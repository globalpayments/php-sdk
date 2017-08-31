<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;

class DebitTrackData extends Debit implements ITrackData
{
    public $entryMethod;
    public $value;
}
