<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\Utils\CardUtils;

class CreditTrackData extends Credit implements ITrackData
{
    public ?string $value = null;
    public ?string $discretionaryData = null;
    public ?string $expiry = null;
    public ?string $pan = null;
    public ?string $purchaseDeviceSequenceNumber = null;
    public mixed $trackNumber = null;
    public ?string $trackData = null;

    public function setTrackData($value)
    {
        if (empty($this->value)) {
            $this->setValue($value);
        } else {
            $this->trackData = $value;
        }
    }

    public function setValue($value)
    {
        $this->value = $value;
        CardUtils::parseTrackData($this);
        $this->cardType = CardUtils::getCardType($this->pan);
        $this->isFleet = CardUtils::isFleet($this->cardType, $this->pan);
                
        if ($this->cardType == 'WexFleet' && $this->discretionaryData != null &&
                strlen($this->discretionaryData) >= 8) {
            $this->purchaseDeviceSequenceNumber = substr($this->discretionaryData, 3, 8);
        }
    }
}
