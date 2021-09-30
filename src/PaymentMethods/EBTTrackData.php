<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\TrackNumber;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\Utils\CardUtils;

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
    protected $value;
    public $expire;
    public $pan;
    public $purchaseDeviceSequenceNumber;
    /**
     * @var TrackNumber $trackNumber
     */
    public $trackNumber;
    protected $trackData;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case 'value':
                $this->{$property} = $value;
                CardUtils::parseTrackData($this);
                break;
            case 'trackData':
                if (is_null($this->value)) {
                    $this->value = $value;
                    CardUtils::parseTrackData($this);
                } else {
                    $this->{$property} = $value;
                }
                break;
        }
    }
}
