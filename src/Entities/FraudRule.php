<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\FraudFilterMode;

class FraudRule
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var FraudFilterMode
     */
    public $mode;

    /** @var string */
    public $description;

    /** @var string */
    public $result;
}