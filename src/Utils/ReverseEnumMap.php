<?php

namespace GlobalPayments\Api\Utils;

use ReflectionClass;

class ReverseEnumMap
{
    public $map;

    public function __construct($valueType)
    {
        $reflector = new ReflectionClass($valueType);
        foreach ($reflector->getConstants() as $constant => $value) {
            $this->map[$value] = $constant;
        }
    }

    public function get($value)
    {
        return isset($this->map[$value]) ? $this->map[$value] : '';
    }
}
