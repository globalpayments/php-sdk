<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;

class BoardingConfig
{
    public $portal;

    public function validate()
    {
        // Service URL
        if (empty($this->portal)) {
            throw new ConfigurationException(
                "Portal should not be empty for this configuration."
            );
        }
    }
}
