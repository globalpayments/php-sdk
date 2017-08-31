<?php

namespace GlobalPayments\Api\Entities\Exceptions;

class ApiException extends \Exception
{
    /**
     * Instantiates a new object
     *
     * @param string $message The exception message to throw.
     * @param \Exception $innerException The previous exception used for
     *                                   the exception chaining.
     */
    public function __construct(
        $message,
        \Exception $innerException = null
    ) {
        parent::__construct($message, 0, $innerException);
    }
}
