<?php

namespace GlobalPayments\Api\Entities;

class OrderDetails
{
    /** @var string|float */
    public $insuranceAmount;

    /** @var boolean */
    public $hasInsurance;

    /** @var string|float */
    public $handlingAmount;

    /** @var string */
    public $description;
}