<?php

namespace GlobalPayments\Api\Entities\PayFac;

class AccountPermissions
{
    /**
     * Account permitted to load funds via ACH. Valid values are: Y and N
     *
     * Required
     *
     * @var string
     */
    public $achIn;
    
    /**
     * Account balance allowed to be pushed to on-file DDA. Affects automatic sweeps. Valid values are: Y and N
     *
     * Required
     *
     * @var string
     */
    public $achOut;
    
    /**
     * Valid values are: Y and N
     *
     * Required
     *
     * @var string
     */
    public $ccProcessing;
    
    /**
     * Valid values are: Y and N
     *
     * Required
     *
     * @var string
     */
    public $proPayIn;
    
    /**
     * Valid values are: Y and N
     *
     * Required
     *
     * @var string
     */
    public $proPayOut;
    
    /**
     * Valid values between 0 and 999999999. Expressed as number of pennies in USD or number of account’s currency
     * without decimals.
     *
     * Optional
     *
     * @var string
     */
    public $creditCardMonthLimit;
    
    /**
     * Valid values between 0 and 999999999. Expressed as number of pennies in USD or number of account’s currency
     * without decimals.
     *
     * Optional
     *
     * @var string
     */
    public $creditCardTransactionLimit;
    
    /**
     * Used to update status of ProPay account. Note: the ONLY value that will allow an account to
     * process transactions is ‘ReadyToProcess’ Valid values are:
        * ReadyToProcess
        * FraudAccount
        * RiskwiseDeclined
        * Hold
        * Canceled
        * FraudVictim
        * ClosedEULA
        * ClosedExcessiveChargeback
     *
     * Optional
     *
     * @var GlobalPayments\Api\Entities\Enums\ProPayAccountStatus
     */
    public $merchantOverallStatus;
    
    /**
     * Valid values are Y and N. Please work with ProPay for more information about soft limits feature
     *
     * Optional
     *
     * @var string
     */
    public $softLimitEnabled;
    
    /**
     * Valid values are Y and N. Please work with ProPay for more information about soft limits feature
     *
     * Optional
     *
     * @var string
     */
    public $achPaymentSoftLimitEnabled;
    
    /**
     * Valid values between 0 and 499. Please work with ProPay for more information about soft limits feature
     *
     * Optional
     *
     * @var string
     */
    public $softLimitAchOffPercent;
    
    /**
     * Valid values between 0 and 499. Please work with ProPay for more information about soft limits feature
     *
     * Optional
     *
     * @var string
     */
    public $achPaymentAchOffPercent;
}
