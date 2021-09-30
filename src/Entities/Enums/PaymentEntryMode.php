<?php


namespace GlobalPayments\Api\Entities\Enums;


use GlobalPayments\Api\Entities\Enum;

class PaymentEntryMode extends Enum
{
    const MOTO = 'MOTO';
    const ECOM = 'ECOM';
    const IN_APP = 'IN_APP';
    const CHIP = 'CHIP';
    const SWIPE = 'SWIPE';
    const MANUAL = 'MANUAL';
    const CONTACTLESS_CHIP = 'CONTACTLESS_CHIP';
    const CONTACTLESS_SWIPE = 'CONTACTLESS_SWIPE';
    const PHONE = 'PHONE';
    const MAIL = 'MAIL';
}