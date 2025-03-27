<?php

namespace GlobalPayments\Api\Gateways\BillPay\Requests;

use DateTime;
use GlobalPayments\Api\Builders\{AuthorizationBuilder, ManagementBuilder};
use GlobalPayments\Api\Entities\{
    Address, 
    Customer,
    HostedPaymentData
};
use GlobalPayments\Api\Entities\BillPay\{Bill, Credentials};
use GlobalPayments\Api\Entities\Enums\{
    AccountType,
    BillPresentment,
    CheckType,
    EmvFallbackCondition,
    EmvLastChipRead,
    HostedPaymentType,
    PaymentMethodType
};
use GlobalPayments\Api\Entities\Exceptions\{BuilderException, UnsupportedTransactionException};
use GlobalPayments\Api\PaymentMethods\{CreditCardData, ECheck};
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Utils\{Element, ElementTree};
use function PHPUnit\Framework\isEmpty;

abstract class BillPayRequestBase
{
    /**
     * @var int
     */
    public $version = 3092;

    /**
     * @var int
     */
    public $applicationId = 3;

    /**
     * @var string
     */
    public $browserType = "PHP SDK";

    /**
     * @var ElementTree
     */
    public $et;

    public function __construct(ElementTree $et)
    {
        $this->et = $et;
    }

    protected function buildCredentials(Element $parent, Credentials $credentials)
    {
        $this->et->subElement($parent, "bdms:BollettaVersion", $this->version);
        /** @var Element */
        $credential = $this->et->subElement($parent, "bdms:Credential");
        $this->et->subElement($credential, "bdms:ApiKey", $credentials->getApiKey());
        $this->et->subElement($credential, "bdms:ApplicationID", $this->applicationId);
        $this->et->subElement($credential, "bdms:Password", $credentials->getPassword());
        $this->et->subElement($credential, "bdms:UserName", $credentials->getUsername());
        $this->et->subElement($credential, "bdms:MerchantName", $credentials->getMerchantName());
    }

    /**
     * Builds the ACH Account section of the request
     * @param Element $parent
     * @param Echeck $eCheck
     * @param float $amountToCharge
     * @param ?float $feeAmount
     */
    protected function buildACHAccount(
        Element $parent, 
        ECheck $eCheck, 
        float $amountToCharge, 
        ?float $feeAmount = null)
    {
        /** @var Element */
        $achAccounts = $this->et->subElement($parent, "bdms:ACHAccountsToCharge");
        $achAccount = $this->et->subElement($achAccounts, "bdms:ACHAccountToCharge");
        $this->et->subElement($achAccount, "bdms:Amount", $amountToCharge);
        $this->et->subElement($achAccount, "bdms:ExpectedFeeAmount", $feeAmount === null ? 0.0 : $feeAmount);
        // PLACEHOLDER: ACHReturnEmailAddress
        $this->et->subElement($achAccount, "bdms:ACHStandardEntryClass", $eCheck->secCode);
        $this->et->subElement($achAccount, "bdms:AccountNumber", $eCheck->accountNumber);
        if ($eCheck->checkType !== null) {
            $this->et->subElement($achAccount, "bdms:AccountType", $this->getDepositType($eCheck->checkType));
        }
        if ($eCheck->accountType !== null) {
            $this->et->subElement($achAccount, "bdms:DepositType", $this->getACHAccountType($eCheck->accountType));
        }
        // PLACEHOLDER: DocumentID
        // PLACEHOLDER: InternalAccountNumber
        $this->et->subElement($achAccount, "bdms:PayorName", $eCheck->checkHolderName);
        $this->et->subElement($achAccount, "bdms:RoutingNumber", $eCheck->routingNumber);
        // PLACEHOLDER: SendEmailOnReturn
        // PLACEHOLDER: SubmitDate
        // PLACEHOLDER: TrackingNumber
    }

    /**
     * Builds a list of BillPay Bill Transactions from a list of Bills
     * 
     * @param Element $parent
     * @param array $bills
     * @param string $billLabel
     * @param string $amountLabel
     */
    protected function buildBillTransactions(Element $parent, array $bills, string $billLabel, string $amountLabel)
    {
        foreach ($bills as $bill) {  
            /** @var Element */
            $billTransaction = $this->et->subElement($parent, $billLabel);
            $this->et->subElement($billTransaction, "bdms:BillType", $bill->getBillType());
            $this->et->subElement($billTransaction, "bdms:ID1", $bill->getIdentifier1());
            $this->et->subElement($billTransaction, "bdms:ID2", $bill->getIdentifier2());
            $this->et->subElement($billTransaction, "bdms:ID3", $bill->getIdentifier3());
            $this->et->subElement($billTransaction, "bdms:ID4", $bill->getIdentifier4());
            $this->et->subElement($billTransaction, $amountLabel, $bill->getAmount());
        }
    }

    /**
     * Builds a BillPay ClearTextCredit card from CreditCardData
     * 
     * @param Element $parent
     * @param CreditCardData $card
     * @param float $amountToCharge
     * @param ?float $feeAmount
     * @param ?EmvFallbackCondition $condition
     * @param ?EmvLastChipRead $lastRead
     * @param ?Address $address
     */
    protected function buildClearTextCredit(Element $parent, CreditCardData $card, float $amountToCharge,
                                            ?float $feeAmount = null, ?EmvFallbackCondition $condition = null,
                                            ?EmvLastChipRead $lastRead = null, ?Address $address = null) 
    {
        $isEmvFallback = $condition !== null && $condition === EmvFallbackCondition::CHIP_READ_FAILURE;
        $isPreviousEmvFallback = $lastRead != null && $lastRead === EmvLastChipRead::FAILED;

        /** @var Element */
        $clearTextCards = $this->et->subElement($parent, "bdms:ClearTextCreditCardsToCharge");
        /** @var Element */
        $clearTextCard = $this->et->subElement($clearTextCards, "bdms:ClearTextCardToCharge");
        $this->et->subElement($clearTextCard, "bdms:Amount", $amountToCharge);
        $this->et->subElement($clearTextCard, "bdms:CardProcessingMethod", "Credit");
        $this->et->subElement($clearTextCard, "bdms:ExpectedFeeAmount", $feeAmount === null ? 0.0 : $feeAmount);

        /** @var Element */
        $clearTextCredit = $this->et->subElement($clearTextCard, "bdms:ClearTextCreditCard");

        /** @var Element */
        $cardHolder = $this->et->subElement($clearTextCredit, "pos:CardHolderData");
        $this->buildAccountHolderData(
            $cardHolder,
            $address,
            $card->cardHolderName
        );

        $this->et->subElement($clearTextCredit, "pos:CardNumber", $card->number);
        $this->et->subElement($clearTextCredit, "pos:ExpirationMonth", $card->expMonth);
        $this->et->subElement($clearTextCredit, "pos:ExpirationYear", $card->expYear);
        $this->et->subElement($clearTextCredit, "pos:IsEmvFallback", $this->serializeBooleanValues($isEmvFallback));
        $this->et->subElement($clearTextCredit, "pos:PreviousEmvAlsoFallback", $this->serializeBooleanValues($isPreviousEmvFallback));
        $this->et->subElement($clearTextCredit, "pos:VerificationCode", $card->cvn);
    }

    /**
     * Builds the account billing information
     * 
     * @param Element $parent The XML element to attach.
     * @param Address $address The billing address of the customer.
     * @param string $nameOnAccount The name on the payment account.
     * 
     * @return void
     */
    protected function buildAccountHolderData(Element $parent, ?Address $address, string $nameOnAccount) 
    {
        $this->et->subElement($parent, "pos:NameOnCard", $nameOnAccount);
        if ($address !== null) {
            $this->et->subElement($parent, "pos:City", $address->city);
            $this->et->subElement($parent, "pos:Address", $address->streetAddress1);
            $this->et->subElement($parent, "pos:State", $address->state);
            $this->et->subElement($parent, "pos:Zip", $address->postalCode);
        }
    }

    /**
     * Builds a BillPay token to charge from any payment method
     * 
     * @param Element $parent The parent XML element to attach to
     * @param IPaymentMethod $paymentMethod The token to pay
     * @param float $amount The amount to charge
     * @param ?float $feeAmount The expected fee amount to charge
     */
    protected function buildTokenToCharge(
        Element $parent,
        IPaymentMethod $paymentMethod,
        float $amount,
        ?float $feeAmount = null
    )
    {
        /** @var Element */
        $tokensToCharge = $this->et->subElement($parent, "bdms:TokensToCharge");
        $tokenToCharge = $this->et->subElement($tokensToCharge, "bdms:TokenToCharge");

        $this->et->subElement($tokenToCharge, "bdms:Amount", $amount);
        $this->et->subElement(
            $tokenToCharge, 
            "bdms:CardProcessingMethod", 
            $this->getCardProcessingMethod($paymentMethod->getPaymentMethodType())
        );
        $this->et->subElement($tokenToCharge, "bdms:ExpectedFeeAmount", $feeAmount);

        if ($paymentMethod instanceof ECheck) {
            $this->et->subElement($tokenToCharge, "bdms:ACHStandardEntryClass", $paymentMethod->secCode);
        }
        $this->et->subElement($tokenToCharge, "bdms:Token", $paymentMethod->token);
    }

    /**
     * Builds the BillPay transaction object
     * 
     * @param Element $parent
     * @param AuthorizationBuilder $builder
     */
    protected function buildTransaction(Element $parent, AuthorizationBuilder $builder) 
    {
        /** @var Element */
        $transaction = $this->et->subElement($parent, "bdms:Transaction");
        $this->et->subElement($transaction, "bdms:Amount", $builder->amount);
        $this->et->subElement($transaction, "bdms:FeeAmount", $builder->convenienceAmount);
        $this->et->subElement($transaction, "bdms:MerchantInvoiceNumber", $builder->invoiceNumber);
        $this->et->subElement($transaction, "bdms:MerchantTransactionDescription", $builder->description);
        $this->et->subElement($transaction, "bdms:MerchantTransactionID", $builder->clientTransactionId);

        if ($builder->customerData === null) {
            return;
        }

        $customer = $builder->customerData;
        $this->et->subElement($transaction, "bdms:PayorEmailAddress", $customer->email);
        $this->et->subElement($transaction, "bdms:PayorFirstName", $customer->firstName);
        $this->et->subElement($transaction, "bdms:PayorLastName", $customer->lastName);
        $this->et->subElement($transaction, "bdms:PayorPhoneNumber", $customer->homePhone);

        if ($customer->address === null) {
            return;
        }

        $address = $customer->address;
        $this->et->subElement($transaction, "bdms:PayorAddress", $address->streetAddress1);
        $this->et->subElement($transaction, "bdms:PayorCity", $address->city);
        $this->et->subElement($transaction, "bdms:PayorCountry", $address->country);
        $this->et->subElement($transaction, "bdms:PayorPostalCode", $address->postalCode);
        $this->et->subElement($transaction, "bdms:PayorState", $address->state);

    }

    protected function buildCustomer(Element $parent, Customer $customer)
    {
        $this->et->subElement($parent, "bdms:EmailAddress", $customer->email);
        $this->et->subElement($parent, "bdms:FirstName", $customer->firstName);
        $this->et->subElement($parent, "bdms:LastName", $customer->lastName);
        $this->et->subElement($parent, "bdms:MerchantCustomerID", $customer->id);
        $this->et->subElement($parent, "bdms:MobilePhone", $customer->mobilePhone);
        $this->et->subElement($parent, "bdms:Phone", $customer->homePhone);

        if ($customer->address === null) {
            return;
        }

        $address = $customer->address;
        $this->et->subElement($parent, "bdms:Address", $address->streetAddress1);
        $this->et->subElement($parent, "bdms:City", $address->city);
        $this->et->subElement($parent, "bdms:Country", $address->country);
        $this->et->subElement($parent, "bdms:Postal", $address->postalCode);
        $this->et->subElement($parent, "bdms:State", $address->state);
    }

    /**
     * Validates that the AuthorizationBuilder is configured correctly for a Bill Payment
     */
    protected function validateTransaction(AuthorizationBuilder $builder) 
    {
        /** @var array<string> */
        $validationErrors = array();

        if ($builder->bills === null || count($builder->bills) === 0) {
            array_push($validationErrors, "Bill Payments must have at least one bill to pay.");
        } else {
            $billSum = 0.0;
            foreach($builder->bills as $bill) {
                $billSum = $billSum + $bill->getAmount();
            }

            $amountFloat = (float) $builder->amount;
            if($amountFloat !== $billSum) {
                array_push($validationErrors, "The sum of the bill amounts must match the amount charged.");
            }
        }

        if ($builder->currency !== "USD") {
            array_push($validationErrors, "Bill Pay only supports currency USD.");
        }

        if (!(count($validationErrors) === 0)) {
            $this->throwBuilderException($validationErrors);
        }
    }

    /**
     * @param Array<Bill> $bills
     */
    protected function validateBills(array $bills) {
        /** @var array<string> */
        $validationErrors = array();

        if ($bills === null || count($bills) === 0) {
            array_push($validationErrors, "At least one Bill required to Load Bills.");
        } else {
            foreach($bills as $bill) { 
                if ($bill->getAmount() <= 0) {
                    array_push($validationErrors, "Bills require an amount greater than zero.");
                    break;
                }
            }
        }

        if (!(count($validationErrors) === 0)) {
            $this->throwBuilderException($validationErrors);
        }
    }

    protected function validateReversal(ManagementBuilder $builder) {
        /** @var array<string> */
        $validationErrors = array();

        if (!($builder->paymentMethod instanceof TransactionReference) || isEmpty($builder->paymentMethod->transactionId)) {
            array_push($validationErrors, "A transaction to reverse must be provided.");
        } else {
            $isPositiveInteger = (int)$builder->paymentMethod->transactionId;
            if ($isPositiveInteger < 1) {
                array_push($validationErrors, "The transaction id to reverse must be a positive integer.");
            }
        }

        if ($builder->bills !== null || !isEmpty($builder->bills)) { 
            /** @var Bill[] */
            $bills = $builder->bills;

            $billSum = 0.0;
            foreach($bills as $bill) {
                $billSum = $billSum + $bill->getAmount();
            }

            if($builder->amount != $billSum) {
                array_push($validationErrors, "The sum of the bill amounts must match the amount to reverse.");
            }
        }

        if (!isEmpty($validationErrors)) {
            $this->throwBuilderException($validationErrors);
        }
    }

    protected function validateLoadSecurePay(?HostedPaymentData $hostedPaymentData) {
        /** @var array<string> */
        $validationErrors = array();

        if ($hostedPaymentData === null) {
            array_push($validationErrors, "HostedPaymentData Required");
        } else {
            if ($hostedPaymentData->bills === null || count($hostedPaymentData->bills) === 0) {
                array_push($validationErrors, "At least one Bill required to Load Bills.");
            } else {
                foreach($hostedPaymentData->bills as $bill) { 
                    if ($bill->getAmount() <= 0) {
                        array_push($validationErrors, "Bills require an amount greater than zero.");
                        break;
                    }
                }
            }

            if ($hostedPaymentData->hostedPaymentType === null || $hostedPaymentData->hostedPaymentType == HostedPaymentType::NONE) {
                array_push($validationErrors, "You must set a valid HostedPaymentType.");
            }
        }

        if (!(count($validationErrors) === 0)) {
            $this->throwBuilderException($validationErrors);
        }
    }
    
    protected function getDateFormatted(DateTime $date): string
    {
        $milliseconds = substr($date->format('u'), 0, 3);
        $formattedDate = $date->format("Y-m-d\TH:i:s") . ".$milliseconds" . $date->format('P');
        return $formattedDate;
    }

    /**
     * These methods are here to convert SDK enums
     * Into values that BillPay will recognize
     */
    protected function getBillPresentmentType(string $billPresentment): string
    {
        switch ($billPresentment) {
            case BillPresentment::FULL:
                    return "Full";
            default:
                throw new UnsupportedTransactionException("Bill Presentment Type of " . $billPresentment . " is not supported");
        }
    }

    protected function getDepositType(int $deposit): string
    {   
        switch ($deposit) {
            case CheckType::BUSINESS:
                return "Business";
            case CheckType::PERSONAL:
                return "Personal";
            case CheckType::PAYROLL:
            default:
                throw new UnsupportedTransactionException("eCheck Deposit Type of " . $deposit . " is not supported. ");
        }
    }

    protected function getACHAccountType(int $accountType): string
    {
        switch ($accountType) {
            case AccountType::CHECKING:
                return "Checking";
            case AccountType::SAVINGS:
                return "Savings";
            default:
                throw new UnsupportedTransactionException("eCheck Account Type of " . $accountType . " is not supported. ");    
        }
    }

    /**
     * @param PaymentMethodTyoe $paymentMethodType
     */
    protected function getCardProcessingMethod(int $paymentMethodType): string
    {
        switch ($paymentMethodType) {
            case PaymentMethodType::CREDIT:
                return "Credit";
            case PaymentMethodType::DEBIT:
                return "Debit";
            // Need to differentiate PINDebit
            default:
                return "Unassigned";
        }
    }

    protected function getPaymentMethodType(int $paymentMethod): string
    {
        switch ($paymentMethod) {
            case PaymentMethodType::CREDIT:
                return "Credit";
            case PaymentMethodType::DEBIT:
                return "Debit";
            case PaymentMethodType::ACH:
                return "ACH";
            default:
                throw new UnsupportedTransactionException();

        }
    }

    protected function getHostedPaymentTypeOrdinal(string $hostedPaymentType): int
    {
        switch ($hostedPaymentType) {
            case HostedPaymentType::NONE:
                return 0;
            case HostedPaymentType::MAKE_PAYMENT:
                return 1;
            case HostedPaymentType::MAKE_PAYMENT_RETURN_TOKEN:
                return 2;
            case HostedPaymentType::GET_TOKEN:
                return 3;
            case HostedPaymentType::MY_ACCOUNT:
                return 4;
            default:
                throw new UnsupportedTransactionException();
        }
    } 

    protected function serializeBooleanValues(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }
        
        return $value ? "true" : "false";
    }

    /**
     * @param array<String> $messages
     */
    protected function throwBuilderException(array $messages) {
        $messageBuilder = "";

        foreach($messages as $m) {
            $messageBuilder = $messageBuilder . $m . " ";
        }

        throw new BuilderException($messageBuilder);
    }

    protected function isNullOrEmpty(?string $param): bool
    {
        if ($param === null || trim($param) === "") {
            return true;
        }

        return false;
    }

    protected function buildQuickPayCardToCharge(Element $parent, CreditCardData $card, float $amountToCharge, Address $address, ?float $feeAmount = null)
    {
        /** @var Element */
        $cardToCharge = $this->et->subElement($parent, "bdms:QuickPayCardToCharge");
        $this->et->subElement($cardToCharge, "bdms:Amount", $amountToCharge);
        $this->et->subElement($cardToCharge, "bdms:CardProcessingMethod", "Credit");
        $this->et->subElement($cardToCharge, "bdms:ExpectedFeeAmount", $feeAmount);

        /** @var Element */
        $cardHolder = $this->et->subElement($cardToCharge, "pos:CardHolderData");
        $this->buildAccountHolderData(
            $cardHolder,
            $address,
            $card->cardHolderName
        );

        $this->et->subElement($cardToCharge, "bdms:ExpirationMonth", $card->expMonth);
        $this->et->subElement($cardToCharge, "bdms:ExpirationYear", $card->expYear);
        $this->et->subElement($cardToCharge, "bdms:QuickPayToken", $card->token);
        $this->et->subElement($cardToCharge, "bdms:VerificationCode", $card->cvn);
    }

    protected function buildQuickPayACHAccountToCharge(Element $parent, ECheck $eCheck, float $amountToCharge, ?float $feeAmount = null)
    {
        /** @var Element */
        $achAccount = $this->et->subElement($parent, "bdms:QuickPayACHAccountToCharge");
        $this->et->subElement($achAccount, "bdms:ACHStandardEntryClass", $eCheck->secCode);
        $this->et->subElement($achAccount, "bdms:AccountType", $this->getDepositType($eCheck->checkType));
        $this->et->subElement($achAccount, "bdms:Amount", $amountToCharge);
        $this->et->subElement($achAccount, "bdms:DepositType", $this->getACHAccountType($eCheck->accountType));
        $this->et->subElement($achAccount, "bdms:ExpectedFeeAmount", $feeAmount);
        $this->et->subElement($achAccount, "bdms:PayorName", $eCheck->checkHolderName);
        $this->et->subElement($achAccount, "bdms:QuickPayToken", $eCheck->token);
    }
}