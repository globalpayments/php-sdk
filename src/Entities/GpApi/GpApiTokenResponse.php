<?php

namespace GlobalPayments\Api\Entities\GpApi;

class GpApiTokenResponse
{
    public $token;
    public $type;
    public $timeCreated;
    public $secondsToExpire;
    public $appId;
    public $appName;
    public $email;
    public $merchantId;
    public $merchantName;
    public $accounts;
    public $dataAccountName;
    public $disputeManagementAccountName;
    public $tokenizationAccountName;
    public $transactionProcessingAccountName;

    const DATA_ACCOUNT_NAME_PREFIX = 'DAA_';
    const DISPUTE_MANAGEMENT_ACCOUNT_NAME_PREFIX = 'DIA_';
    const TOKENIZATION_ACCOUNT_NAME_PREFIX = 'TKA_';
    const TRANSACTION_PROCESSING_ACCOUNT_NAME_PREFIX = 'TRA_';
    const RIKS_ASSESSMENT_ACCOUNT_NAME_PREFIX = 'RAA_';

    public function __construct($response)
    {
        $response = json_decode($response);
        $this->mapResponseValues($response);
    }

    /**
     * @param string $accountPrefix
     *
     * @return null|string
     */
    private function getAccountName($accountPrefix)
    {
        /**
         * @var GpApiAccount $account
         */
        foreach ($this->accounts as $account) {
            if (!empty($account->id) && substr($account->id, 0, 4) === $accountPrefix) {
                return $account->name;
            }
        }

        return null;
    }

    /**
     * @param string $accountPrefix
     *
     * @return null|string
     */
    private function getAccountID(string $accountPrefix)
    {
        /**
         * @var GpApiAccount $account
         */
        foreach ($this->accounts as $account) {
            if (!empty($account->id) && substr($account->id, 0, 4) === $accountPrefix) {
                return $account->id;
            }
        }

        return null;
    }

    public function getDataAccountName()
    {
        return $this->getAccountName(self::DATA_ACCOUNT_NAME_PREFIX);
    }

    public function getDataAccountID()
    {
        return $this->getAccountID(self::DATA_ACCOUNT_NAME_PREFIX);
    }

    public function getDisputeManagementAccountName()
    {
        return $this->getAccountName(self::DISPUTE_MANAGEMENT_ACCOUNT_NAME_PREFIX);
    }

    public function getDisputeManagementAccountID()
    {
        return $this->getAccountID(self::DISPUTE_MANAGEMENT_ACCOUNT_NAME_PREFIX);
    }

    public function getTokenizationAccountName()
    {
        return $this->getAccountName(self::TOKENIZATION_ACCOUNT_NAME_PREFIX);
    }

    public function getTokenizationAccountID()
    {
        return $this->getAccountID(self::TOKENIZATION_ACCOUNT_NAME_PREFIX);
    }

    public function getTransactionProcessingAccountName()
    {
        return $this->getAccountName(self::TRANSACTION_PROCESSING_ACCOUNT_NAME_PREFIX);
    }

    public function getTransactionProcessingAccountID()
    {
        return $this->getAccountID(self::TRANSACTION_PROCESSING_ACCOUNT_NAME_PREFIX);
    }

    public function getRiskAssessmentAccountName()
    {
        return $this->getAccountName(self::RIKS_ASSESSMENT_ACCOUNT_NAME_PREFIX);
    }

    public function getRiskAssessmentAccountID()
    {
        return $this->getAccountID(self::RIKS_ASSESSMENT_ACCOUNT_NAME_PREFIX);
    }

    public function getToken()
    {
        return $this->token;
    }

    private function mapResponseValues($response)
    {
        $this->token = $response->token;
        $this->type = $response->type;
        $this->appId = $response->app_id;
        $this->appName = $response->app_name;
        $this->timeCreated = $response->time_created;
        $this->secondsToExpire = $response->seconds_to_expire;
        $this->email = $response->email;
        if (!empty($response->scope)) {
            $this->merchantId = $response->scope->merchant_id;
            $this->merchantName = $response->scope->merchant_name;
            foreach ($response->scope->accounts as $account) {
                $this->accounts[] = new GpApiAccount($account->id, $account->name);
            }
        }
    }
}
