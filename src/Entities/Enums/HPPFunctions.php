<?php
namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Enumeration class for Hosted Payment Page functions, goes in the root of the request
 * 
 * Describes the function the hosted payment page can have
 * Note: comments taken from the documentation
 */
class HPPFunctions extends Enum
{
    /**
     * A page that allows a user to view all transactions within an established timeframe.
     *
     * @var string
     */
    const TRANSACTION_REPORT = 'TRANSACTION_REPORT';
    
    /**
     * A page that allows for standard payment processing.
     *
     * @var string
     */
    const PAYMENT_PROCESSING = 'PAYMENT_PROCESSING';
    
    /**
     * A page that allows for credential exchange.
     *
     * @var string
     */
    const CREDENTIAL_EXCHANGE = 'CREDENTIAL_EXCHANGE';
    
    /**
     *  Merchant report that will display all transactions in a Global Payments automated sweep.
     *
     * @var string
     */
    const DEPOSIT_REPORT = 'DEPOSIT_REPORT';
    
    /**
     * A page that allows a merchant to update the primary payout bank information on an account.
     *
     * @var string
     */
    const UPDATE_PRIMARY_ACCOUNT = 'UPDATE_PRIMARY_ACCOUNT';
    
    /**
     * A page that allows a merchant to validate the micro deposits that have been deposited to the primary payout payment method on file.
     *
     * @var string
     */
    const CONFIRM_VALIDATION_DEPOSITS = 'CONFIRM_VALIDATION_DEPOSITS';
    
    /**
     * A page that allows a merchant to trigger 2 micro deposits to the primary bank account on file to validate the bank account.
     *
     * @var string
     */
    const SEND_VALIDATION_DEPOSITS = 'SEND_VALIDATION_DEPOSITS';
    
    /**
     * A page that allows a merchant to update the payout card on file.
     *
     * @var string
     */
    const UPDATE_PAYOUT_CARD = 'UPDATE_PAYOUT_CARD';
    
    /**
     * A page that allows a merchant to initiate a payout to the payout card on file.
     *
     * @var string
     */
    const FUND_PAYOUT_CARD = 'FUND_PAYOUT_CARD';
    
    /**
     * A page that allows a merchant to initiate a payout to the primary payout payment method on file.
     *
     * @var string
     */
    const FUND_PRIMARY_PAYOUT = 'FUND_PRIMARY_PAYOUT';
    
    /**
     * A page that allows a merchant to send funds to another merchant.
     *
     * @var string
     */
    const FMA_TRANSFER = 'FMA_TRANSFER';
    
    /**
     * A page that allows a merchant to pull funds from a bank account into the funds management account.
     *
     * @var string
     */
    const CREDIT_FMA = 'CREDIT_FMA';
    
    /**
     * A page that allows a merchant to schedule a funds payout to their primary payout payment method on file.
     *
     * @var string
     */
    const SCHEDULED_FUND = 'SCHEDULED_FUND';
    
    /**
     * A page that allows a merchant to update the business information associated on their merchant account.
     *
     * @var string
     */
    const UPDATE_BUSINESS_INFORMATION = 'UPDATE_BUSINESS_INFORMATION';
    
    /**
     * A page that allows a merchant to update the phone and addresses associated with a merchant account.
     *
     * @var string
     */
    const UPDATE_ADDRESS_PHONE = 'UPDATE_ADDRESS_PHONE';
    
    /**
     * A page that allows a merchant to update the pin to access the IVR line for their merchant account.
     *
     * @var string
     */
    const UPDATE_PIN = 'UPDATE_PIN';
    
    /**
     * A page that allows a merchant to update the primary email address associated with a merchant account.
     *
     * @var string
     */
    const UPDATE_EMAIL = 'UPDATE_EMAIL';
    
    /**
     * A page that allows a merchant to view information about their prepaid debit card linked to their merchant account balance.
     *
     * @var string
     */
    const PREPAID_CARD_INDEX = 'PREPAID_CARD_INDEX';
    
    /**
     * A page that allows a merchant to agree to terms and conditions and request a prepaid debit card linked to the balance in a merchant account.
     *
     * @var string
     */
    const REQUEST_PREPAID_CARD = 'REQUEST_PREPAID_CARD';
    
    /**
     * A page that allows a merchant to activate a prepaid debit card linked to the balance in their merchant account.
     *
     * @var string
     */
    const ACTIVATE_PREPAID_CARD = 'ACTIVATE_PREPAID_CARD';
    
    /**
     * A page that allows a merchant to mark the prepaid debit card linked to their merchant balance lost or stolen.
     *
     * @var string
     */
    const LOST_PREPAID_CARD = 'LOST_PREPAID_CARD';
    
    /**
     * A page that allows a merchant to update the 4 digit pin associated with the prepaid debit card to access funds in the merchant account.
     *
     * @var string
     */
    const UPDATE_PREPAID_CARD_PIN = 'UPDATE_PREPAID_CARD_PIN';
    
    /**
     * A page that allows a merchant to request an updated prepaid debit card to access the funds in the merchant account.
     *
     * @var string
     */
    const REISSUE_PREPAID_CARD = 'REISSUE_PREPAID_CARD';
    
    /**
     * A page that allows a merchant to edit/update the account activation fee payment method on file.
     *
     * @var string
     */
    const UPDATE_PAYMENT_METHOD = 'UPDATE_PAYMENT_METHOD';
    
    /**
     * A page that will allow a merchant upload documents related to merchant identity validation, underwriting and risk review.
     *
     * @var string
     */
    const DOCUMENT_UPLOAD = 'DOCUMENT_UPLOAD';
    
    /**
     * A page that will display all active disputed transactions and allow document uploads to respond to the listed disputes for a merchant.
     *
     * @var string
     */
    const CHARGEBACK_REPORT = 'CHARGEBACK_REPORT';
    
    /**
     * A page that allows a user to set criteria and generate a report.
     *
     * @var string
     */
    const ADVANCED_TRANSACTION_SEARCH = 'ADVANCED_TRANSACTION_SEARCH';
    
    /**
     * A page that will show the consolidated fees for a merchant within an established timeframe.
     *
     * @var string
     */
    const CONSOLIDATED_FEES = 'CONSOLIDATED_FEES';
    
    /**
     * A page that displays the processing limits and processing fees on a merchant account.
     *
     * @var string
     */
    const LIMITS_RATES_FEES = 'LIMITS_RATES_FEES';
    
    /**
     * A page that allows a merchant to review fees they will be charged, as is required by PSR (Payment Systems Regulator).
     *
     * @var string
     */
    const GB_FEES_REPORT = 'GB_FEES_REPORT';
}
