<?php

namespace GlobalPayments\Api\Terminals;

class ValidationRequest
{
    private array $mandatoryParams = [];

    public function setMandatoryParams(array $params)
    {
        $this->mandatoryParams = $params;
    }

    public function getMandatoryParams()
    {
        return $this->mandatoryParams;
    }

    public function validate($request)
    {
        $missingParams = "";
        foreach ($this->getMandatoryParams() as $param) {
            if (strpos(json_encode($request), $param) === false) {
                $missingParams .= $param . ', ';
            }
        }
        if (!empty($missingParams)) {
            $missingParams = rtrim($missingParams, ', ');
            return $missingParams;
        }

        return true;
    }
}