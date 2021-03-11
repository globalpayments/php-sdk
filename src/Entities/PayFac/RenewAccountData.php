<?php

namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\PaymentMethods\CreditCardData;

class RenewAccountData
{
    /**
     * Supplying a value will change the accounts tier under the affiliation upon renewal.
     * If not passed the tier will not be changed
     *
     * @var string
     */
    public $tier;

        
    /**
     * Credit Card Details
     *
     * @var GlobalPayments\Api\PaymentMethods\CreditCardData
     */
    public $creditCard;
    
    /**
     * The US zip code of the credit card. 5 or 9 digits without a dash for US cards. Omit for international credit cards
     *
     * @var string
     */
    public $zipCode;
    
    /**
     * Used to pay for an account via ACH and monthly renewal. Financial institution account number.
     * *Required if using ACH to pay renewal fee
     *
     * @var string
     */
    public $paymentBankAccountNumber;
    
    
    /**
     * Used to pay for an account via ACH and monthly renewal. Financial institution routing number. Must be a valid ACH routing number. *Required if using ACH to pay renewal fee.
     *
     * @var string
     */
    public $paymentBankRoutingNumber;
    
    /**
     * Used to pay for an account via ACH and monthly renewal. Valid values are: Checking and Savings
     *
     * @var string
     */
    public $paymentBankAccountType;
    
    
    public function __construct()
    {
        $this->creditCard = new CreditCardData();
    }
}
