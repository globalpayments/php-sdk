<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\TrackNumber;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;

class EBTTrackData extends EBT implements ITrackData, IEncryptable
{
    public $discretionaryData;
    /**
     * @var EncryptionData $encryptionData
     */
    public $encryptionData;
    /**
     * @var EntryMethod $entryMethod
     */
    public $entryMethod;
    public $value;
    public $expire;
    public $pan;
    public $purchaseDeviceSequenceNumber;
    /**
     * @var TrackNumber $trackNumber
     */
    public $trackNumber;
}
