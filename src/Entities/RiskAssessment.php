<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\RiskAssessmentStatus;
use GlobalPayments\Api\PaymentMethods\Interfaces\ISecureCheck;

class RiskAssessment implements ISecureCheck
{
    /**
     * A unique identifier for the risk assessment
     * @var string
     */
    public $id;

    /**
     * Time indicating when the object was created
     * @var \DateTime
     */
    public $timeCreated;

    /**
     * Indicates where the risk assessment is in its lifecycle.
     * @var RiskAssessmentStatus
     */
    public $status;

    /**
     * The amount associated with the risk assessment.
     * @var float
     */
    public $amount;

    /**
     * The currency of the amount in ISO-4217(alpha-3)
     * @var string
     */
    public $currency;
    /**
     * A unique identifier for the merchant set by Global Payments
     * @var string
     */
    public $merchantId;

    /**
     * A meaningful label for the merchant set by Global Payments.
     * @var string
     */
    public $merchantName;

    /**
     * A unique identifier for the merchant account set by Global Payments.
     * @var string
     */
    public $accountId;

    /**
     * A meaningful label for the merchant account set by Global Payments.
     * @var string
     */
    public $accountName;

    /**
     * Merchant defined field to reference the risk assessment resource.
     * @var string
     */
    public $reference;

    /**
     * The result from the risk assessment service.
     * @var string
     */
    public $responseCode;

    /**
     * The result message from the risk assessment service that describes the result given.
     * @var string
     */
    public $responseMessage;

    /**
     * @var Card
     */
    public $cardDetails;

    /**
     * @var ThirdPartyResponse
     */
    public $thirdPartyResponse;

    /**
     * A unique identifier for the object created by Global Payments.
     * @var string
     */
    public $actionId;
}