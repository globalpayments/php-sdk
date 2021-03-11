<?php
namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\Entities\Address;

class PayFacResponseData
{
    
    public $accountNumber;
    public $password;
    public $sourceEmail;
    public $beneficialOwnerDataResult;
    public $authToken;
    public $recAccountNum;
    public $amount;
    public $transNum;
    public $pending;
    public $secondaryAmount;
    public $secondaryTransNum;
    public $accountStatus;
    public $physicalAddress;
    public $affiliation;
    public $aPIReady;
    public $currencyCode;
    public $expiration;
    public $signupDate;
    public $tier;
    public $visaCheckoutMerchantID;
    public $creditCardTransactionLimit;
    public $creditCardMonthLimit;
    public $aCHPaymentPerTranLimit;
    public $aCHPaymentMonthLimit;
    public $creditCardMonthlyVolume;
    public $aCHPaymentMonthlyVolume;
    public $reserveBalance;
    public $masterPassCheckoutMerchantID;
    
    // Account balance
    public $pendingAmount;
    public $reserveAmount;
    public $aCHOut;
    public $flashFunds;
    
    //PayFac response
    public $transactionId;
    public $transactionNumber;
    
    public function __construct()
    {
        $this->physicalAddress = new Address();
        $this->aCHOut = new AccountBalanceResponseData();
        $this->flashFunds = new AccountBalanceResponseData();
    }
}
