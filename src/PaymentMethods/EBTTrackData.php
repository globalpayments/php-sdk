<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;

class EBTTrackData extends EBT implements ITrackData, IEncryptable
{
    public $encryptionData;
    public $entryMethod;
    public $value;
}
