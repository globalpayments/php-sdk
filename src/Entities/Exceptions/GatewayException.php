<?php

namespace GlobalPayments\Api\Entities\Exceptions;

class GatewayException extends ApiException
{
    /**
     * Response code
     *
     * @var string
     */
    public $responseCode;

    /**
     * Response message
     *
     * @var string
     */
    public $responseMessage;

    /**
     * Instantiates a new object
     *
     * @param string $message The exception message to throw.
     * @param \Exception $innerException The previous exception used for
     *                                   the exception chaining.
     */
    public function __construct(
        $message,
        $responseCode = null,
        $responseMessage = null,
        \Exception $innerException = null
    ) {
        $this->responseCode = $responseCode;
        $this->responseMessage = $responseMessage;

        parent::__construct($message, $innerException);
    }
}
