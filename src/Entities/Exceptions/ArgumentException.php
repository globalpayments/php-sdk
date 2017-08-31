<?php

namespace GlobalPayments\Api\Entities\Exceptions;

class ArgumentException extends ApiException
{
    /**
     * Instantiates a new object
     *
     * @param string $message The exception message to throw.
     * @param \Exception $innerException The previous exception used for
     *                                   the exception chaining.
     */
    public function __construct($message, \Exception $innerException = null)
    {
        parent::__construct($message, $innerException);
    }
}
