<?php

namespace GlobalPayments\Api\Terminals\UPA\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Builders\TerminalBuilder;

class RequestHostFields implements IRequestSubGroup
{
    public ?string $issuerScripts;
    public ?string $issuerAuthData;
    public ?string $hostDecision;

    public function getElementString()
    {
        // Strip null values
        return array_filter((array) $this, function ($val) {
            return !is_null($val);
        });
    }

    public function setParams(TerminalBuilder $builder)
    {
        if (isset($builder->hostData)) {
            $this->hostDecision = $builder->hostData->hostDecision;
            $this->issuerScripts = $builder->hostData->issuerScripts;
            $this->issuerAuthData = $builder->hostData->issuerAuthData;
        }
    }
}