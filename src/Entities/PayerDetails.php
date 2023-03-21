<?php

namespace GlobalPayments\Api\Entities;

class PayerDetails
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $email;

    /** @var Address */
    public $billingAddress;

    /** @var Address */
    public $shippingAddress;
}