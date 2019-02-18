<?php

namespace GlobalPayments\Api\Entities\Exceptions;

class ValidationException extends ApiException
{
    public $serialVersionUID = '3915500990858182587L';
    public $validationErrors;
    
    /**
     * Instantiates a new object
     *
     * @param string $validationErrors The exception message to throw.
     */
    public function __construct($validationErrors)
    {
        parent::__construct($validationErrors);
    }
}
