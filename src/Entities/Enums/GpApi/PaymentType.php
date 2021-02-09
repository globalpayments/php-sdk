<?php


namespace GlobalPayments\Api\Entities\Enums\GpApi;


use GlobalPayments\Api\Entities\Enum;

class PaymentType extends Enum
{
    const REFUND = 'REFUND';
    const SALE = 'SALE';
}