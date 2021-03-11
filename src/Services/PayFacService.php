<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class PayFacService
{
    public static function createAccount()
    {
        return new PayFacBuilder(TransactionType::CREATE_ACCOUNT);
    }
    
    /*
     *   This transaction type requires an X.509 certificate as additional authentication
     *   Only one group should be passed per request.
     *   If a group is passed, the API user must pass all data elements that comprise that group. 
     *   If a value from a group is missing, then the data not included in your request is cleared from the ProPay record.
     *   Allowance to perform an edit of each group is subject to approval. 
     *   If you try to perform an edit for a disallowed group, your request will fail.
     * 
     */
    public static function editAccount()
    {
        return new PayFacBuilder(TransactionType::EDIT);
    }
    /*
     *   This method will reset a ProPay web login password. An email will be sent to the account email address on 
     *   file from customerservice@propay.com containing a temporary password that can be used to login, 
     *   but must be changed to something new by the user at that point.
     *
     */
    public static function resetPassword()
    {
        return new PayFacBuilder(TransactionType::RESET_PASSWORD);
    }
    /*
     *  This method will extend the expiration date of a ProPay account by one year. 
     *  This may also be used to change the tier of an existing account. 
     *
     */
    public static function renewAccount()
    {
        return new PayFacBuilder(TransactionType::RENEW_ACCOUNT);
    }
    /*
     *  This method will update the beneficial owner data for the specified account number. 
     *  This method should be used if the beneficial data was not sent. 
     *  while creating merchant. Note: this method can be used only when the OwnerCount value is passed while creating merchant.
     *
     */
    public static function updateBeneficialOwnershipInfo()
    {
        return new PayFacBuilder(TransactionType::UPDATE_OWNERSHIP_DETAILS);
    }
    /*
     *  This method will remove a ProPay account from an affiliation. The affiliation must have appropriate settings 
     *  to enable this feature. 
     *
     */
    public static function disownAccount()
    {
        return new PayFacBuilder(TransactionType::DEACTIVATE);
    }
    /*
     *  This method can be used to send an image file to ProPay, and is specifically designed to support the documents 
     *  you use to dispute a credit card chargeback. This version of document upload has you “tag” the 
     *  document to a specific transaction that has been charged-back
     *
     */
    public static function uploadDocumentChargeback()
    {
        return new PayFacBuilder(TransactionType::UPLOAD_CHARGEBACK_DOCUMENT);
    }
    /*
     *  This method can be used to send an image file to ProPay. The ProPay Risk team may request that you perform this 
     *  action to underwrite an account that was denied via automated boarding, to increase the processing limit on 
     *  accounts, or to provide data when we’ve had to put an accounts ability to process on hold. 
     *
     */
    public static function UploadDocument()
    {
        return new PayFacBuilder(TransactionType::UPLOAD_DOCUMENT);
    }
    
    public static function obtainSSOKey()
    {
        return new PayFacBuilder(TransactionType::OBTAIN_SSO_KEY);
    }
    
    public static function updateBankAccountOwnershipInfo()
    {
        return new PayFacBuilder(TransactionType::UPDATE_BANK_ACCOUNT_OWNERSHIP);
    }
    
    public static function addFunds()
    {
        return new PayFacBuilder(TransactionType::ADD_FUNDS);
    }
    
    public static function sweepFunds()
    {
        return new PayFacBuilder(TransactionType::SWEEP_FUNDS);
    }
    
    public static function addCardFlashFunds()
    {
        return new PayFacBuilder(TransactionType::ADD_CARD_FLASH_FUNDS);
    }
    
    public static function pushMoneyToFlashFundsCard()
    {
        return new PayFacBuilder(TransactionType::PUSH_MONEY_FLASH_FUNDS);
    }
    
    public static function disburseFunds()
    {
        return new PayFacBuilder(TransactionType::DISBURSE_FUNDS);
    }
    
    public static function spendBack()
    {
        return new PayFacBuilder(TransactionType::SPEND_BACK);
    }
    
    public static function reverseSplitPay()
    {
        return new PayFacBuilder(TransactionType::REVERSE_SPLITPAY);
    }
    
    public static function splitFunds()
    {
        return new PayFacBuilder(TransactionType::SPLIT_FUNDS);
    }
    
    public static function getAccountDetails()
    {
        return new PayFacBuilder(TransactionType::GET_ACCOUNT_DETAILS);
    }
    
    public static function getAccountBalance()
    {
        return new PayFacBuilder(TransactionType::GET_ACCOUNT_BALANCE);
    }
}
