<?php

namespace GlobalPayments\Api\Entities;

class FraudRuleCollection
{
    /**
     * @var array<FraudRule>
     */
    public $rules;

    public function __construct()
    {
        $this->rules = [];
    }

    /**
     * @param string $key
     * @param string $mode
     */
    public function addRule($key, $mode)
    {
        if ($this->hasRule($key)) {
            return;
        }

        $rule = new FraudRule();
        $rule->key = $key;
        $rule->mode = $mode;

        array_push($this->rules, $rule);
    }

    private function hasRule($key)
    {
        $neededObject = array_filter(
            $this->rules,
            function ($e) use (&$key) {
                return $e->key == $key;
            }
        );

        return !empty($neededObject);
    }
}