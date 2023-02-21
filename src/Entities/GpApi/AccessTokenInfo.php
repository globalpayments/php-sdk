<?php

namespace GlobalPayments\Api\Entities\GpApi;

class AccessTokenInfo
{
    /**
     * @var string
     */
    public $accessToken;

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
}