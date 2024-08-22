<?php

namespace GlobalPayments\Api\Entities\Exceptions;

class MessageException extends ApiException
{
    /**
     * A message to/from the device caused an error.
     *
     * @param string $message The exception message to throw.
     * @param \Exception|null $innerException The previous exception used for the exception chaining.
     */
    public function __construct(string $message, \Exception $innerException = null)
    {
        parent::__construct($message, $innerException);
    }
}