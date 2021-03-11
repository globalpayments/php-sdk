<?php

namespace GlobalPayments\Api\Entities\PayFac;

class BankAccountData
{
    /**
     * ISO 3166 standard 3-character country code.
     *
     * @var string
     */
    public $accountCountryCode;

    /**
     * Merchant/Individual Name.
     *
     * @var string
     */
    public $accountName;
    
    /**
     * Financial institution account number.
     *
     * @var string
     */
    public $accountNumber;
    
    /**
     * Valid values are: Personal and Business
     *
     * @var string
     */
    public $accountOwnershipType;
    
    /**
     * Valid values are:
            C – Checking
            S – Savings
            G – General Ledger
     *
     * @var string
     */
    public $accountType;
    
    /**
     * Name of financial institution
     *
     * @var string
     */
    public $bankName;
    
    /**
     * Financial institution routing number. Must be a valid ACH routing number
     *
     * @var string
     */
    public $routingNumber;
    
    /**
     * Bank account-holder’s name. *Required if payment method is a bank account.
     *
     * @var string
     */
    public $accountHolderName;
}
