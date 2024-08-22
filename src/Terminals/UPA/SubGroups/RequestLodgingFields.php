<?php

namespace GlobalPayments\Api\Terminals\UPA\SubGroups;

use GlobalPayments\Api\Entities\Enums\LodgingItemType;
use GlobalPayments\Api\Entities\LodgingData;
use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;

class RequestLodgingFields implements IRequestSubGroup
{
    /** @var int Lodging system generated value used to group and manage charges during a stay. */
    public int $folioNumber;

    /** @var int Duration of stay */
    public int $stayDuration;

    /** @var string Check in date Format: MMDDYYYY */
    public string $checkInDate;

    /** @var string Checkout date Format: MMDDYYYY  */
    public string $checkOutDate;

    /** @var int Daily room rate */
    public int $dailyRate;

    /** @var int Indicates whether or not the customer has a preferred status */
    public int $preferredCustomer;

    public $extraChargeTypes;
    /** @var string Total extra charge amount information; this defines the portion of the total amount provided
     * as part of this request that was specifically for lodging extra charges */
    public string $extraChargeTotal;

    public function setParams(LodgingData $lodgingData, string $command)
    {
        switch ($command) {
            case UpaMessageId::UPDATE_LODGING_DETAILS:
                $this->folioNumber = $lodgingData->folioNumber;
                $this->mapExtraCharges($lodgingData->extraCharges);
                break;
            default:
                break;
        }
    }

    private function mapExtraCharges(array $extraCharges)
    {
        $extraChargeTypes = array_fill(1, 10, 0);
        foreach ($extraCharges as $type) {
            if (isset($extraChargeTypes[$type])) {
                $extraChargeTypes[$type] = 1;
            }
        }
        $this->extraChargeTotal = number_format(array_sum($extraChargeTypes),2);
        $this->extraChargeTypes = json_decode(json_encode(array_values($extraChargeTypes), JSON_FORCE_OBJECT));
    }

    public function getElementString()
    {
        // TODO: Implement getElementString() method.
    }
}