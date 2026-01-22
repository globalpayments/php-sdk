<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\Enums\BankList;
use GlobalPayments\Api\Entities\Address;

class Bank
{
    /**
     * @var string
     */
    public ?string $code = null;

    /** @var BankList */
    public mixed $name = null;

    /** @var Address */
    public ?Address $address = null;

    /**
     * @var string
     */
    public ?string $identifierCode = null;

    /**
     * @var string
     */
    public ?string $iban = null;

    /**
     * @var string
     */
    public ?string $accountNumber = null;
}