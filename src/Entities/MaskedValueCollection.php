<?php

namespace GlobalPayments\Api\Entities;

class MaskedValueCollection
{
    protected array $maskValues = [];

    private function getValues() : array
    {
        return $this->maskValues;
    }

    public function hideValue($key, $value, $unmaskedLastChars = 0, $unmaskedFirstChars = 0) : array
    {
        $this->addValue($key ,$value, $unmaskedLastChars, $unmaskedFirstChars);

        return $this->getValues();
    }

    protected function addValue($key, $value, $unmaskedLastChars = 0, $unmaskedFirstChars = 0) : bool
    {
        if (!$this->validateValue($value) || in_array($value, $this->maskValues)) {
            return false;
        }
        $this->maskValues[$key] = $this->disguise($value, $unmaskedLastChars, $unmaskedFirstChars);

        return true;
    }

    protected function validateValue($value) : bool
    {
        if (empty($value) || is_array($value) || is_object($value)) {
            return false;
        }

        return true;
    }

    private function disguise($value, $unmaskedLastChars = 0, $unmaskedFirstChars = 0,  $maskSymbol = 'X')
    {
        $value = filter_var($value, FILTER_UNSAFE_RAW);
        $unmaskedLastChars = (int) $unmaskedLastChars;
        $maskSymbol = filter_var($maskSymbol, FILTER_UNSAFE_RAW);

        // not enough chars to unmask ?
        if (abs($unmaskedLastChars) >= strlen($value)) {
            $unmaskedLastChars = 0;
        }

        // at least half must be masked ?
        if (abs($unmaskedLastChars) > strlen($value)/2) {
            $unmaskedLastChars = round($unmaskedLastChars/2);
        }

        // leading unmasked chars
        if ($unmaskedLastChars < 0) {
            $unmasked = substr($value, 0, -$unmaskedLastChars);
            return $unmasked . str_repeat($maskSymbol,
                    strlen($value) - strlen($unmasked)
                );
        }
        $unmaskedFirstValue = substr($value, 0, $unmaskedFirstChars);
        $unmaskedLastValue = $unmaskedLastChars ? substr($value, -$unmaskedLastChars) : '';

        return $unmaskedFirstValue .
            str_repeat($maskSymbol, strlen($value) - $unmaskedFirstChars - $unmaskedLastChars)  .
            $unmaskedLastValue;
    }
}