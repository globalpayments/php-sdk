<?php

namespace GlobalPayments\Api\Builders\BaseBuilder;

use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class Validations
{
    /**
     * Validation rules
     *
     * @var array
     */
    public $rules;

    /**
     * Instantiates a new object
     *
     * @return
     */
    public function __construct()
    {
        $this->rules = [];
    }

    /**
     * Creates a new `ValidationTarget` for the given
     * transaction type mask.
     *
     * @param TransactionType|int $type Mask of transaction types
     * @param TransactionModifier|int $modifier Transaction modifier
     *
     * @return ValidationTarget
     */
    public function of($type, $modifier = TransactionModifier::NONE)
    {
        if (!array_key_exists($type, $this->rules)) {
            $this->rules[$type] = [];
        }

        $target = new ValidationTarget($this, $type, $modifier);
        $this->rules[$type][] = $target;
        return $target;
    }
}
