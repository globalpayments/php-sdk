<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\Utils\CardUtils;

class DebitTrackData extends Debit implements ITrackData
{
    public mixed $entryMethod = null;
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
    }
}
