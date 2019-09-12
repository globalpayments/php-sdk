<?php

namespace GlobalPayments\Api\Builders\BaseBuilder;

use GlobalPayments\Api\Entities\Exceptions\BuilderException;

class ValidationClause
{
    /**
     * All Validations
     *
     * @var Validations
     */
    public $parent;

    /**
     * Target of this validation clause
     *
     * @var ValidationTarget
     */
    public $target;

    /**
     * Validation clause is a precondition
     *
     * @var bool
     */
    public $precondition;

    /**
     * Callback to test a given property
     *
     * @var callable
     */
    public $callback;

    /**
     * Failed validation message
     *
     * @var string
     */
    public $message;

    /**
     * Instantiates a new object
     *
     * @param Validations $parent All validations
     * @param ValidationTarget $target Current validation target
     *
     * @return
     */
    public function __construct(
        Validations $parent,
        ValidationTarget $target,
        $precondition = false
    ) {
        $this->parent = $parent;
        $this->target = $target;
        $this->precondition = $precondition;
    }

    /**
     * Validates the target property is not null
     *
     * @param string $subProperty Parent of current property
     * @param string $message Validation message to override the default
     *
     * @return ValidationTarget
     */
    public function isNotNull($message = null, $subProperty = null)
    {
        $this->callback = function ($builder) use ($subProperty) {
            $builder = ($subProperty !== null && !empty($builder->{$subProperty}))
                        ? $builder->{$subProperty}
                        : $builder;
            if (!property_exists($builder, $this->target->property)
                && !isset($builder->{$this->target->property})
            ) {
                throw new BuilderException(
                    sprintf(
                        'Property `%s` does not exist on `%s`',
                        $this->target->property,
                        get_class($builder)
                    )
                );
            }
            $value = $builder->{$this->target->property};
            return null !== $value;
        };
        $this->message = !empty($message)
            ? $message
            // TODO: implement a way to expose property name
            : sprintf(
                '%s cannot be null for this transaction type.',
                $this->target->property
            );

        if ($this->precondition) {
            return $this->target;
        }

        return $this->parent->of($this->target->type, $this->target->modifier);
    }
    
    /**
     * Validates the target property is null
     *
     * @param string $subProperty Parent of current property
     * @param string $message Validation message to override the default
     *
     * @return ValidationTarget
     */
    public function isNull($message = null, $subProperty = null)
    {
        $this->callback = function ($builder) use ($subProperty) {
            $builder = ($subProperty == null && empty($builder->{$subProperty}))
                        ? $builder->{$subProperty}
                        : $builder;
            if (!property_exists($builder, $this->target->property)
                && !isset($builder->{$this->target->property})
            ) {
                throw new BuilderException(
                    sprintf(
                        'Property `%s` does not exist on `%s`',
                        $this->target->property,
                        get_class($builder)
                    )
                );
            }
            $value = $builder->{$this->target->property};
            return null == $value;
        };
        $this->message = !empty($message)
            ? $message
            // TODO: implement a way to expose property name
            : sprintf(
                '%s cannot be set for this transaction type.',
                $this->target->property
            );

        if ($this->precondition) {
            return $this->target;
        }

        return $this->parent->of($this->target->type, $this->target->modifier);
    }

    /**
     *
     * @param class $clazz
     * @param string $message
     *
     * @return ValidationTarget
     */
    public function isInstanceOf($clazz, $message = null)
    {
        $this->callback = function ($builder) use ($clazz) {
            if (!($builder->{$this->target->property} instanceof $clazz)) {
                throw new BuilderException(
                    sprintf(
                        '%s must be an instance of the %s class.',
                        $this->target->property,
                        $clazz
                    )
                );
                return false;
            }
            return true;
        };

        $this->message = !empty($message)
            ? $message
            // TODO: implement a way to expose property name
            : sprintf(
                '%s must be an instance of the %s class.',
                $this->target->property,
                $clazz
            );

        if ($this->precondition) {
            return $this->target;
        }

        return $this->parent->of($this->target->type, $this->target->modifier);
    }

    /**
     * Validates the target property is equal to the expected value
     *
     * @param string $expected
     * @param string $message Validation message to override the default
     *
     * @return ValidationTarget
     */
    public function isEqualTo($expected, $message = null)
    {
        $this->callback = function ($builder) use ($expected) {
            if ($builder->{$this->target->property} !== $expected) {
                throw new BuilderException(
                    sprintf(
                        'Property `%s` does not equal the expected value `%s`',
                        $this->target->property,
                        $expected
                    )
                );
                return false;
            }
            return true;
        };
        $this->message = !empty($message)
            ? $message
            // TODO: implement a way to expose property name
            : sprintf(
                'Property `%s` does not equal the expected value `%s`',
                $this->target->property,
                $expected
            );

        if ($this->precondition) {
            return $this->target;
        }

        return $this->parent->of($this->target->type, $this->target->modifier);
    }

    /**
     * Validates the target property is NOT equal to the expected value
     *
     * @param string $expected
     * @param string $message Validation message to override the default
     *
     * @return ValidationTarget
     */
    public function isNotEqualTo($expected, $message = null)
    {
        $this->callback = function ($builder) use ($expected) {
            if ($builder->{$this->target->property} === $expected) {
                throw new BuilderException(
                    sprintf(
                        'Property `%s`is equal to the expected value `%s`',
                        $this->target->property,
                        $expected
                    )
                );
                return false;
            }
            return true;
        };
        $this->message = !empty($message)
            ? $message
            // TODO: implement a way to expose property name
            : sprintf(
                'Property `%s` is equal to the expected value `%s`',
                $this->target->property,
                $expected
            );

        if ($this->precondition) {
            return $this->target;
        }

        return $this->parent->of($this->target->type, $this->target->modifier);
    }
    
    /**
     * Validates the target property is not null in a sub class
     *
     * @param string $subProperty Parent of current property
     * @param string $message Validation message to override the default
     *
     * @return ValidationTarget
     */
    public function isNotNullInSubProperty($subProperty, $message = null)
    {
        return $this->isNotNull($message, $subProperty);
    }
}
