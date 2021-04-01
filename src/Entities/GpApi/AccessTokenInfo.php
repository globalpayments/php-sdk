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
}