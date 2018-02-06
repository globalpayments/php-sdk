<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Builders\BaseBuilder\Validations;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Transaction;

abstract class BaseBuilder
{
    /**
     * Builder validations
     *
     * @var Validations
     */
    protected $validations;

    /**
     * Has builder been executed
     *
     * @var bool
     */
    protected $executed;

    /**
     * Instantiates a new builder
     *
     * @return
     */
    public function __construct()
    {
        $this->validations = new Validations();
        $this->setupValidations();
    }

    /**
     * Executes the builder
     *
     * @return Transaction
     */
    public function execute()
    {
        $this->validate();
        return new Transaction();
    }

    /**
     * Used to setup validations for the builder.
     *
     * @return void
     */
    abstract protected function setupValidations();

    /**
     * Validates the builder based on validations in `$validations`
     *
     * @return void
     */
    protected function validate()
    {
        array_map(
            [$this, 'maybeRunValidationKeyRules'],
            array_keys($this->validations->rules)
        );
    }

    /**
     * Runs validations for `$key`
     *
     * @param mixed $key Validation rules key
     *
     * @throws BuilderException
     * @return void
     */
    protected function maybeRunValidationKeyRules($key)
    {
        if (($key & $this->transactionType) !== $this->transactionType) {
            return;
        }

        foreach ($this->validations->rules[$key] as $validation) {
            if (null === $validation->clause) {
                continue;
            }

            if ($this->transactionModifier === $validation->modifier
                && !call_user_func($validation->clause->callback, $this)
            ) {
                throw new BuilderException($validation->clause->message);
            }
        }
    }
}
