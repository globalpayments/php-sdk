<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\Enums\BankList;
use GlobalPayments\Api\Entities\Address;

class Bank
{
    /**
     * @var string
     */
    public $code;

    /** @var BankList */
    public $name;

    /** @var Address */
    public $address;

    /**
     * @var string
     */
    public $identifierCode;

    /**
     * @var string
     */
    public $iban;

    /**
     * @var string
     */
    public $accountNumber;
}