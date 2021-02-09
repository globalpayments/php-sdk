<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\Utils\CardUtils;

class DebitTrackData extends Debit implements ITrackData
{
    public $entryMethod;
    public $value;
    public $discretionaryData;
    public $expiry;
    public $pan;
    public $purchaseDeviceSequenceNumber;
    public $trackNumber;
    public $trackData;

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
