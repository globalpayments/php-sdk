<?php


namespace GlobalPayments\Api\Entities\Enums\GpApi;


use GlobalPayments\Api\Entities\Enum;

class EntryMode extends Enum
{
    const MOTO = 'MOTO';
    const ECOM = 'ECOM';
    const IN_APP = 'IN_APP';
    const CHIP = 'CHIP';
    const SWIPE = 'SWIPE';
    const MANUAL = 'MANUAL';
    const CONTACTLESS_CHIP = 'CONTACTLESS_CHIP';
    const CONTACTLESS_SWIPE = 'CONTACTLESS_SWIPE';
}