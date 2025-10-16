<?php

namespace GlobalPayments\Api\Entities\Exceptions;

class ArgumentException extends ApiException
{
    /**
     * Instantiates a new object
     *
     * @param string $message The exception message to throw.
     * @param null|\Exception $innerException The previous exception used for
     *                                   the exception chaining.
     */
    public function __construct($message, null|\Exception $innerException = null)
    {
        parent::__construct($message, $innerException);
    }
}
