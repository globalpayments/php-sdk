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
    public ?string $discretionaryData = null;
    /**
     * @var EncryptionData $encryptionData
     */
    public ?EncryptionData $encryptionData = null;
    /**
     * @var EntryMethod $entryMethod
     */
    protected ?string $value = null;
    public ?string $expire = null;
    public ?string $pan = null;
    public ?string $purchaseDeviceSequenceNumber = null;
    /**
     * @var TrackNumber $trackNumber
     */
    public mixed $trackNumber = null;
    protected ?string $trackData = null;

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
