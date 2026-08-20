<?php

namespace GlobalPayments\Api\Entities\GpApi;

class AccessTokenInfo
{
    /**
     * @var string
     */
    public $accessToken;

    /** @var string */
    public $merchantId;

    /** @var string|null */
    public ?string $merchantName = null;

    /** @var string|null */
    public ?string $email = null;

    /** @var string|null */
    public ?string $appId = null;

    /** @var string|null */
    public ?string $appName = null;

    /** @var string|null */
    public ?string $tokenType = null;

    /** @var string|null */
    public ?string $timeCreated = null;

    /** @var int|null */
    public ?int $secondsToExpire = null;

    /** @var string|null */
    public ?string $intervalToExpire = null;

    /**
     * @var string
     */
    public $dataAccountName;
    /**
     * @var string
     */
    public $disputeManagementAccountName;
    /**
     * @var string
     */
    public $tokenizationAccountName;
    /**
     * @var string
     */
    public $transactionProcessingAccountName;

    /** @var string */
    public $riskAssessmentAccountName;

    /**
     * @var string
     */
    public $dataAccountID;
    /**
     * @var string
     */
    public $disputeManagementAccountID;
    /**
     * @var string
     */
    public $tokenizationAccountID;
    /**
     * @var string
     */
    public $transactionProcessingAccountID;

    /** @var string */
    public $riskAssessmentAccountID;
    /** @var string */
    public $merchantManagementAccountName;

    /** @var string */
    public $merchantManagementAccountID;

    public ?string $fileProcessingAccountID;

    public ?string $fileProcessingAccountName;
}