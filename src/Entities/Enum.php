<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use ReflectionClass;

abstract class Enum
{
    /**
     * Validates a desired value exists as a constant on the class
     *
     * @param mixed $value Value to validate
     *
     * @throws ArgumentException
     * @return mixed
     */
    public static function validate($value)
    {
        $reflector = new ReflectionClass(static::class);

        foreach ($reflector->getConstants() as $allowedValue) {
            if ($value === $allowedValue) {
                return $allowedValue;
            }
        }

        throw new ArgumentException(
            sprintf(
                'Invalid value `%s` on enum `%s`',
                $value,
                static::class
            )
        );
    }
}
