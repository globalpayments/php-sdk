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

    /** @var string|null */
    public ?string $errorCode = null;

    /** @var string|null */
    public ?string $detailedErrorCode = null;

    /** @var string|null */
    public ?string $detailedErrorDescription = null;

    /** @var int|null */
    public ?int $httpStatusCode = null;

    /** @var array<string,string>|null */
    public ?array $responseHeaders = null;

    /**
     * Instantiates a new object
     *
     * @param string $message The exception message to throw.
     * @param null|\Exception $innerException The previous exception used for
     *                                   the exception chaining.
     */
    public function __construct(
        $message,
        $responseCode = null,
        $responseMessage = null,
        ?\Exception $innerException = null
    ) {
        $this->responseCode = $responseCode;
        $this->responseMessage = $responseMessage;

        parent::__construct($message, $innerException);
    }
}
