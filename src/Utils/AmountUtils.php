<?php

namespace GlobalPayments\Api\Utils;

class AmountUtils
{

    /**
     * Should be used on all dollar amounts w/TransIT gateway to avoid gateway errors
     *
     * @param mixed $amount
     *
     * @return float
     */
    public static function transitFormat(float $amount)
    {
        return number_format($amount, 2, '.', '');
    }
}
