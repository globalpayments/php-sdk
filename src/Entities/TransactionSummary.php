<?php

namespace GlobalPayments\Api\Entities;

/**
 * Transaction-level report data
 */
class TransactionSummary
{
    /**
     * The originally requested authorization amount.
     *
     * @var float|string|null
     */
    public $amount;

    /**
     * The originally requested convenience amount.
     *
     * @var float|string|null
     */
    public $convenienceAmt;

    /**
     * The originally requested shipping amount.
     *
     * @var float|string|null
     */
    public $shippingAmt;

    /**
     * The authorization code provided by the issuer.
     *
     * @var string
     */
    public $authCode;

    /**
     * The authorized amount.
     *
     * @var float|string|null
     */
    public $authorizedAmount;

    /**
     * The client transaction ID sent in the authorization request.
     *
     * @var string
     */
    public $clientTransactionId;

    /**
     * The device ID where the transaction was ran; where applicable.
     *
     * @var integer|string
     */
    public $deviceId;

    /**
     * The original response code from the issuer.
     *
     * @var string
     */
    public $issuerResponseCode;

    /**
     * The original response message from the issuer.
     *
     * @var string
     */
    public $issuerResponseMessage;

    /**
     * The authorized card number, masked.
     *
     * @var string
     */
    public $maskedCardNumber;

    /**
     * The gateway transaction ID of the authorization request.
     *
     * @var string
     */
    public $originalTransactionId;

    /**
     * The original response code from the gateway.
     *
     * @var string
     */
    public $gatewayResponseCode;

    /**
     * The original response message from the gateway.
     *
     * @var string
     */
    public $gatewayResponseMessage;

    /**
     * The reference number provided by the issuer.
     *
     * @var string
     */
    public $referenceNumber;

    /**
     * The transaction type.
     *
     * @var string
     */
    public $serviceName;

    /**
     * The settled from the authorization.
     *
     * @var float|string|null
     */
    public $settlementAmount;

    /**
     * The transaction status.
     *
     * @var string
     */
    public $status;

    /**
     * The date/time of the original transaction.
     *
     * @var DateTime
     */
    public $transactionDate;

    /**
     * The gateway transaction ID of the transaction.
     *
     * @var string
     */
    public $transactionId;
}
