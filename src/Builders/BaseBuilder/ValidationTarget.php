<?php

namespace GlobalPayments\Api\Builders\BaseBuilder;

use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class ValidationTarget
{
    /**
     * All Validations
     *
     * @var Validations
     */
    public mixed $parent = null;

    /**
     * Validation type
     *
     * @var TransactionType
     */
    public mixed $type = null;

    /**
     * Validation modifier
     *
     * @var TransactionModifier
     */
    public mixed $modifier = null;

    /**
     * Property to validate
     *
     * @var string
     */
    public ?string $property = null;

    /**
     * Specified validations to test against the property's value
     *
     * @var ValidationClause
     */
    public mixed $clause = null;

    /**
     * Instantiates a new object
     *
     * @param Validations $parent All validations
     * @param TransactionType $type Validation type
     * @param TransactionModifier $modifier Validation modifier
     *
     * @return
     */
    public function __construct(Validations $parent, $type, $modifier)
    {
        $this->parent = $parent;
        $this->type = $type;
        $this->modifier = $modifier;
    }

    /**
     * Sets the validation's transaction modifier
     *
     * @param TransactionModifier|int $modifier Validation modifier
     *
     * @return ValidationTarget
     */
    public function with($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * Creates a new `ValidationClause` to specify validations on the
     * given property.
     *
     * @param string $targetProperty Property to validate
     *
     * @return ValidationClause
     */
    public function check($targetProperty)
    {
        $this->property = $targetProperty;
        $this->clause = new ValidationClause($this->parent, $this);
        return $this->clause;
    }

    /**
     * Creates a new `ValidationClause` to specify conditions for future
     * validations checked against the given property.
     *
     * @param string $targetProperty Property to validate
     *
     * @return ValidationClause
     */
    public function when($targetProperty)
    {
        $this->property = $targetProperty;
        $this->clause = new ValidationClause($this->parent, $this, true);
        return $this->clause;
    }
}
