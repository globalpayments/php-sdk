<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\BrowserData;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\AgeIndicator;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\CustomerAuthenticationMethod;
use GlobalPayments\Api\Entities\Enums\DeliveryTimeFrame;
use GlobalPayments\Api\Entities\Enums\OrderTransactionType;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\PreOrderIndicator;
use GlobalPayments\Api\Entities\Enums\PriorAuthenticationMethod;
use GlobalPayments\Api\Entities\Enums\ReorderIndicator;
use GlobalPayments\Api\Entities\Enums\ShippingMethod;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

abstract class SecureBuilder extends BaseBuilder
{
    /** @var string|float */
    private $amount;

    /** @var string */
    private $currency;

    /** @var \DateTime */
    private $orderCreateDate;

    /** @var OrderTransactionType */
    private $orderTransactionType;

    /** @var string */
    private $orderId;

    /** @var string */
    private $referenceNumber;

    /** @var bool */
    private $addressMatchIndicator;

    /** @var Address */
    private $shippingAddress;

    /** @var ShippingMethod */
    private $shippingMethod;

    /** @var bool */
    private $shippingNameMatchesCardHolderName;

    /** @var DateTime */
    private $shippingAddressCreateDate;

    /** @var AgeIndicator */
    public $shippingAddressUsageIndicator;

    /** @var float */
    private $giftCardAmount;

    /** @var int */
    private $giftCardCount;

    /** @var string */
    private $giftCardCurrency;

    /** @var string */
    private $deliveryEmail;

    /** @var DeliveryTimeFrame */
    private $deliveryTimeframe;

    /** @var DateTime */
    private $preOrderAvailabilityDate;

    /** @var PreOrderIndicator */
    private $preOrderIndicator;

    /** @var ReorderIndicator */
    private $reorderIndicator;

    /** @var string */
    private $customerAccountId;

    /** @var AgeIndicator */
    private $accountAgeIndicator;

    /** @var \DateTime */
    private $accountChangeDate;

    /** @var \DateTime */
    private $accountCreateDate;

    /** @var AgeIndicator */
    private $accountChangeIndicator;

    /** @var \DateTime */
    private $passwordChangeDate;

    /** @var AgeIndicator */
    private $passwordChangeIndicator;

    /** @var array */
    private $phoneList;

    /** @var string */
    private $homeCountryCode;

    /** @var string */
    private $homeNumber;

    /** @var string */
    private $workCountryCode;

    /** @var string */
    private $workNumber;

    /** @var string */
    private $mobileCountryCode;

    /** @var string */
    private $mobileNumber;

    /** @var DateTime */
    private $paymentAccountCreateDate;

    /** @var AgeIndicator */
    private $paymentAgeIndicator;

    /** @var bool */
    private $previousSuspiciousActivity;

    /** @var int */
    private $numberOfPurchasesInLastSixMonths;

    /** @var int */
    private $numberOfTransactionsInLast24Hours;

    /** @var int */
    private $numberOfAddCardAttemptsInLast24Hours;

    /** @var int */
    private $numberOfTransactionsInLastYear;

    /** @var BrowserData */
    private $browserData;

    /** @var string */
    private $priorAuthenticationData;

    /** @var PriorAuthenticationMethod */
    private $priorAuthenticationMethod;

    /** @var string */
    private $priorAuthenticationTransactionId;

    /** @var \DateTime */
    private $priorAuthenticationTimestamp;

    /** @var int */
    private $maxNumberOfInstallments;

    /** @var \DateTime */
    private $recurringAuthorizationExpiryDate;

    /** @var int */
    private $recurringAuthorizationFrequency;

    /** @var string */
    private $customerAuthenticationData;

    /** @var CustomerAuthenticationMethod */
    private $customerAuthenticationMethod;

    /** @var \DateTime */
    private $customerAuthenticationTimestamp;

    /** @var string */
    public $idempotencyKey;

    /** @var AuthenticationSource */
    protected $authenticationSource;

    /** @var IPaymentMethod */
    public $paymentMethod;

    /** @var TransactionType */
    public $transactionType;

    /** @var Address */
    public $billingAddress;


    /**************************************GETTERS**************************************/

    /** @return string|float */
    public function getAmount()
    {
        return $this->amount;
    }

    /** @return string */
    public function getCurrency()
    {
        return $this->currency;
    }

    /** @return AuthenticationSource */
    public function getAuthenticationSource()
    {
        return $this->authenticationSource;
    }

    /** @return DateTime */
    public function getOrderCreateDate()
    {
        return $this->orderCreateDate;
    }

    /** @return string */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /** @return OrderTransactionType */
    public function getOrderTransactionType()
    {
        return $this->orderTransactionType;
    }

    /** @return string */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }


    /** @return bool */
    public function  isAddressMatchIndicator()
    {
        return $this->addressMatchIndicator;
    }

    /** @return Address */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /** @return ShippingMethod */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /** @return bool */
    public function getShippingNameMatchesCardHolderName()
    {
        return $this->shippingNameMatchesCardHolderName;
    }


    /** @return DateTime */
    public function getShippingAddressCreateDate()
    {
        return $this->shippingAddressCreateDate;
    }


    /** @return AgeIndicator */
    public function getShippingAddressUsageIndicator()
    {
        return $this->shippingAddressUsageIndicator;
    }

    /** @return int */
    public function getGiftCardCount()
    {
        return $this->giftCardCount;
    }

    /** @return string */
    public function getGiftCardCurrency()
    {
        return $this->giftCardCurrency;
    }

    /** @return float */
    public function getGiftCardAmount()
    {
        return $this->giftCardAmount;
    }

    /** @return string */
    public function getDeliveryEmail()
    {
        return $this->deliveryEmail;
    }

    /** @return DeliveryTimeFrame */
    public function getDeliveryTimeframe()
    {
        return $this->deliveryTimeframe;
    }

    /** @return DateTime */
    public function getPreOrderAvailabilityDate()
    {
        return $this->preOrderAvailabilityDate;
    }

    /** @return PreOrderIndicator */
    public function getPreOrderIndicator()
    {
        return $this->preOrderIndicator;
    }

    /** @return ReorderIndicator */
    public function getReorderIndicator()
    {
        return $this->reorderIndicator;
    }

    /** @return string */
    public function getCustomerAccountId()
    {
        return $this->customerAccountId;
    }

    /** @return AgeIndicator */
    public function getAccountAgeIndicator()
    {
        return $this->accountAgeIndicator;
    }

    /** @return DateTime */
    public function getAccountChangeDate()
    {
        return $this->accountChangeDate;
    }

    /** @return DateTime */
    public function getAccountCreateDate()
    {
        return $this->accountCreateDate;
    }

    /** @return AgeIndicator */
    public function getAccountChangeIndicator()
    {
        return $this->accountChangeIndicator;
    }


    /** @return DateTime */
    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }

    /** @return AgeIndicator */
    public function getPasswordChangeIndicator()
    {
        return $this->passwordChangeIndicator;
    }


    /** @return string */
    public function getHomeCountryCode()
    {
        return $this->homeCountryCode;
    }

    /** @return string */
    public function getHomeNumber()
    {
        return $this->homeNumber;
    }

    /** @return string */
    public function getWorkCountryCode()
    {
        return $this->workCountryCode;
    }

    /** @return string */
    public function getWorkNumber()
    {
        return $this->workNumber;
    }


    /** @return string */
    public function getMobileCountryCode()
    {
        return $this->mobileCountryCode;
    }

    /** @return string */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }


    /** @return DateTime */
    public function getPaymentAccountCreateDate()
    {
        return $this->paymentAccountCreateDate;
    }

    /** @return AgeIndicator */
    public function getPaymentAgeIndicator()
    {
        return $this->paymentAgeIndicator;
    }


    /** @return bool */
    public function getPreviousSuspiciousActivity()
    {
        return $this->previousSuspiciousActivity;
    }


    /** @return int */
    public function getNumberOfPurchasesInLastSixMonths()
    {
        return $this->numberOfPurchasesInLastSixMonths;
    }


    /** @return int */
    public function getNumberOfTransactionsInLast24Hours()
    {
        return $this->numberOfTransactionsInLast24Hours;
    }

    /** @return int */
    public function getNumberOfAddCardAttemptsInLast24Hours()
    {
        return $this->numberOfAddCardAttemptsInLast24Hours;
    }

    /** @return int */
    public function getNumberOfTransactionsInLastYear()
    {
        return $this->numberOfTransactionsInLastYear;
    }


    /** @return BrowserData */
    public function getBrowserData()
    {
        return $this->browserData;
    }


    /** @return string */
    public function getPriorAuthenticationData()
    {
        return $this->priorAuthenticationData;
    }

    /** @return PriorAuthenticationMethod */
    public function getPriorAuthenticationMethod()
    {
        return $this->priorAuthenticationMethod;
    }

    /** @return string */
    public function getPriorAuthenticationTransactionId()
    {
        return $this->priorAuthenticationTransactionId;
    }

    /** @return \DateTime */
    public function getPriorAuthenticationTimestamp()
    {
        return $this->priorAuthenticationTimestamp;
    }

    /** @return int */
    public function getMaxNumberOfInstallments()
    {
        return $this->maxNumberOfInstallments;
    }


    /** @return \DateTime */
    public function getRecurringAuthorizationExpiryDate()
    {
        return $this->recurringAuthorizationExpiryDate;
    }

    /** @return int */
    public function getRecurringAuthorizationFrequency()
    {
        return $this->recurringAuthorizationFrequency;
    }


    /** @return string */
    public function getCustomerAuthenticationData()
    {
        return $this->customerAuthenticationData;
    }

    /** @return CustomerAuthenticationMethod */
    public function getCustomerAuthenticationMethod()
    {
        return $this->customerAuthenticationMethod;
    }

    /** @return \DateTime */
    public function getCustomerAuthenticationTimestamp()
    {
        return $this->customerAuthenticationTimestamp;
    }

    /************************************************SETTERS****************************/
    /**
     * @param IPaymentMethod $value
     * @return $this
     */
    abstract public function withPaymentMethod(IPaymentMethod $value);


    /**
     * @param $transactionType
     * @return $this
     */
    public function withTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * @param float|string $value
     * @return $this
     */
    public function withAmount($value)
    {
        $this->amount = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function withCurrency(string $value)
    {
        $this->currency = $value;
        return $this;
    }

    /**
     * @param AuthenticationSource $value
     * @return $this
     */
    public function withAuthenticationSource($value)
    {
        $this->authenticationSource = $value;
        return $this;
    }

    /**
     * @param DateTime $value
     * @return $this
     */
    public function withOrderCreateDate($value)
    {
        $this->orderCreateDate = $value;
        return $this;
    }

    /**
     * @param string $referenceNumber
     * @return $this
     */
    public function withReferenceNumber($referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function withAddressMatchIndicator($value)
    {
        $this->addressMatchIndicator = $value;
        return $this;
    }

    /**
     * @param Address $address
     * @param string $type
     * @return $this
     */
    public function withAddress(Address $address, $type = AddressType::BILLING)
    {
        if ($type === AddressType::BILLING) {
            $this->billingAddress = $address;
        } else {
            $this->shippingAddress = $address;
        }
        return $this;
    }

    /**
     * @param float $giftCardAmount
     * @return $this
     */
    public function withGiftCardAmount($giftCardAmount)
    {
        $this->giftCardAmount = $giftCardAmount;

        return $this;
    }

    /**
     * @param int $giftCardCount
     * @return $this
     */
    public function withGiftCardCount($giftCardCount)
    {
        $this->giftCardCount = $giftCardCount;
        return $this;
    }

    /**
     * @param string $giftCardCurrency
     * @return $this
     */
    public function withGiftCardCurrency($giftCardCurrency)
    {
        $this->giftCardCurrency = $giftCardCurrency;
        return $this;
    }

    /**
     * @param string $deliveryEmail
     * @return $this
     */
    public function withDeliveryEmail($deliveryEmail)
    {
        $this->deliveryEmail = $deliveryEmail;
        return $this;
    }

    /**
     * @param DeliveryTimeFrame $deliveryTimeframe
     * @return $this
     */
    public function withDeliveryTimeFrame($deliveryTimeframe)
    {
        $this->deliveryTimeframe = $deliveryTimeframe;
        return $this;
    }

    /**
     * @param ShippingMethod $shippingMethod
     * @return $this
     */
    public function withShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * @param bool $shippingNameMatchesCardHolderName
     * @return $this
     */
    public function withShippingNameMatchesCardHolderName($shippingNameMatchesCardHolderName)
    {
        $this->shippingNameMatchesCardHolderName = $shippingNameMatchesCardHolderName;
        return $this;
    }

    /**
     * @param \DateTime $shippingAddressCreateDate
     * @return $this
     */
    public function withShippingAddressCreateDate($shippingAddressCreateDate)
    {
        $this->shippingAddressCreateDate = $shippingAddressCreateDate;
        return $this;
    }

    /**
     * @param $shippingAddressUsageIndicator
     * @return $this
     */
    public function withShippingAddressUsageIndicator($shippingAddressUsageIndicator)
    {
        $this->shippingAddressUsageIndicator = $shippingAddressUsageIndicator;
        return $this;
    }

    /**
     * @param $preOrderAvailabilityDate
     * @return $this
     */
    public function withPreOrderAvailabilityDate($preOrderAvailabilityDate)
    {
        $this->preOrderAvailabilityDate = $preOrderAvailabilityDate;
        return $this;
    }

    /**
     * @param $preOrderIndicator
     * @return $this
     */
    public function withPreOrderIndicator($preOrderIndicator)
    {
        $this->preOrderIndicator = $preOrderIndicator;
        return $this;
    }


    /**
     * @param ReorderIndicator $reorderIndicator
     * @return $this
     */
    public function withReorderIndicator($reorderIndicator)
    {
        $this->reorderIndicator = $reorderIndicator;
        return $this;
    }

    /**
     * @param OrderTransactionType $orderTransactionType
     * @return $this
     */
    public function withOrderTransactionType($orderTransactionType)
    {
        $this->orderTransactionType = $orderTransactionType;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function withOrderId($value)
    {
        $this->orderId = $value;
        return $this;
    }

    /**
     * @param string $customerAccountId
     * @return $this
     */
    public function withCustomerAccountId($customerAccountId)
    {
        $this->customerAccountId = $customerAccountId;
        return $this;
    }

    /**
     * @param AgeIndicator $ageIndicator
     * @return $this
     */
    public function withAccountAgeIndicator($ageIndicator)
    {
        $this->accountAgeIndicator = $ageIndicator;
        return $this;
    }

    /**
     * @param DateTime $accountChangeDate
     * @return $this
     */
    public function withAccountChangeDate($accountChangeDate)
    {
        $this->accountChangeDate = $accountChangeDate;
        return $this;
    }

    /**
     * @param DateTime $accountCreateDate
     * @return $this
     */
    public function withAccountCreateDate($accountCreateDate)
    {
        $this->accountCreateDate = $accountCreateDate;
        return $this;
    }


    /**
     * @param AgeIndicator $accountChangeIndicator
     * @return $this
     */
    public function withAccountChangeIndicator($accountChangeIndicator)
    {
        $this->accountChangeIndicator = $accountChangeIndicator;
        return $this;
    }


    /**
     * @param DateTime $passwordChangeDate
     * @return $this
     */
    public function withPasswordChangeDate($passwordChangeDate)
    {
        $this->passwordChangeDate = $passwordChangeDate;

        return $this;
    }

    /**
     * @param AgeIndicator $passwordChangeIndicator
     * @return $this
     */
    public function withPasswordChangeIndicator($passwordChangeIndicator)
    {
        $this->passwordChangeIndicator = $passwordChangeIndicator;
        return $this;
    }

    /**
     * @param string $phoneCountryCode
     * @param string $number
     * @param PhoneNumberType $type
     *
     * @return $this
     */
    public function withPhoneNumber($phoneCountryCode, $number, $type)
    {
        $phoneNumber = new PhoneNumber($phoneCountryCode, $number, $type);
        $this->phoneList[$type] = $phoneNumber;
        switch ($phoneNumber->type) {
            case PhoneNumberType::HOME:
                $this->homeNumber = $number;
                $this->homeCountryCode = $phoneCountryCode;
                break;
            case PhoneNumberType::WORK:
                $this->workNumber = $number;
                $this->workCountryCode = $phoneCountryCode;
                break;
            case PhoneNumberType::MOBILE:
                $this->mobileNumber = $number;
                $this->mobileCountryCode = $phoneCountryCode;
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * @deprecated  Will be replaced with method withPhoneNumber($phoneCountryCode, $number, $type)
     * @param string $countryCode
     * @param string $number
     *
     * @return $this
     */
    public function withHomeNumber($countryCode, $number)
    {
        $this->homeCountryCode = $countryCode;
        $this->homeNumber = $number;

        return $this;
    }

    /**
     * @deprecated  Will be replaced with method withPhoneNumber($phoneCountryCode, $number, $type)
     *
     * @param string $countryCode
     * @param string $number
     * @return $this
     */
    public function withWorkNumber($countryCode, $number)
    {
        $this->workCountryCode = $countryCode;
        $this->workNumber = $number;
        return $this;
    }

    /**
     * @deprecated  Will be replaced with method withPhoneNumber($phoneCountryCode, $number, $type)
     *
     * @param string $countryCode
     * @param string $number
     * @return $this
     */
    public function withMobileNumber($countryCode, $number)
    {
        $this->mobileCountryCode = $countryCode;
        $this->mobileNumber = $number;
        return $this;
    }

    /**
     * @param $paymentAccountCreateDate
     * @return $this
     */
    public function withPaymentAccountCreateDate($paymentAccountCreateDate)
    {
        $this->paymentAccountCreateDate = $paymentAccountCreateDate;
        return $this;
    }

    /**
     * @param AgeIndicator $paymentAgeIndicator
     * @return $this
     */
    public function withPaymentAccountAgeIndicator($paymentAgeIndicator)
    {
        $this->paymentAgeIndicator = $paymentAgeIndicator;
        return $this;
    }

    /**
     * @param $previousSuspiciousActivity
     * @return $this
     */
    public function withPreviousSuspiciousActivity($previousSuspiciousActivity)
    {
        $this->previousSuspiciousActivity = $previousSuspiciousActivity;
        return $this;
    }

    /**
     * @param string $numberOfPurchasesInLastSixMonths
     * @return $this
     */
    public function withNumberOfPurchasesInLastSixMonths($numberOfPurchasesInLastSixMonths)
    {
        $this->numberOfPurchasesInLastSixMonths = $numberOfPurchasesInLastSixMonths;
        return $this;
    }

    /**
     * @param int $numberOfTransactionsInLast24Hours
     * @return $this
     */
    public function withNumberOfTransactionsInLast24Hours($numberOfTransactionsInLast24Hours)
    {
        $this->numberOfTransactionsInLast24Hours = $numberOfTransactionsInLast24Hours;
        return $this;
    }

    /**
     * @param int $numberOfAddCardAttemptsInLast24Hours
     * @return $this
     */
    public function withNumberOfAddCardAttemptsInLast24Hours($numberOfAddCardAttemptsInLast24Hours)
    {
        $this->numberOfAddCardAttemptsInLast24Hours = $numberOfAddCardAttemptsInLast24Hours;
        return $this;
    }

    /**
     * @param int $numberOfTransactionsInLastYear
     * @return $this
     */
    public function withNumberOfTransactionsInLastYear($numberOfTransactionsInLastYear)
    {
        $this->numberOfTransactionsInLastYear = $numberOfTransactionsInLastYear;
        return $this;
    }

    /**
     * @param BrowserData $value
     * @return $this
     */
    public function withBrowserData($value)
    {
        $this->browserData = $value;
        return $this;
    }

    /**
     * @param string $priorAuthenticationData
     * @return $this
     */
    public function withPriorAuthenticationData($priorAuthenticationData)
    {
        $this->priorAuthenticationData = $priorAuthenticationData;
        return $this;
    }

    /**
     * @param PriorAuthenticationMethod $priorAuthenticationMethod
     * @return $this
     */
    public function withPriorAuthenticationMethod($priorAuthenticationMethod)
    {
        $this->priorAuthenticationMethod = $priorAuthenticationMethod;
        return $this;
    }

    /**
     * @param string $priorAuthencitationTransactionId
     * @return $this
     */
    public function withPriorAuthenticationTransactionId($priorAuthencitationTransactionId)
    {
        $this->priorAuthenticationTransactionId = $priorAuthencitationTransactionId;
        return $this;
    }

    /**
     * @param \DateTime $priorAuthenticationTimestamp
     * @return $this
     */
    public function withPriorAuthenticationTimestamp($priorAuthenticationTimestamp)
    {
        $this->priorAuthenticationTimestamp = $priorAuthenticationTimestamp;
        return $this;
    }

    /**
     * @param int $maxNumberOfInstallments
     * @return $this
     */
    public function withMaxNumberOfInstallments($maxNumberOfInstallments)
    {
        $this->maxNumberOfInstallments = $maxNumberOfInstallments;
        return $this;
    }

    /**
     * @param \DateTime $recurringAuthorizationExpiryDate
     * @return $this
     */
    public function withRecurringAuthorizationExpiryDate($recurringAuthorizationExpiryDate)
    {
        $this->recurringAuthorizationExpiryDate = $recurringAuthorizationExpiryDate;
        return $this;
    }

    /**
     * @param int $recurringAuthorizationFrequency
     * @return $this
     */
    public function withRecurringAuthorizationFrequency($recurringAuthorizationFrequency)
    {
        $this->recurringAuthorizationFrequency = $recurringAuthorizationFrequency;
        return $this;
    }

    /**
     * @param string $customerAuthenticationData
     * @return $this
     */
    public function withCustomerAuthenticationData($customerAuthenticationData)
    {
        $this->customerAuthenticationData = $customerAuthenticationData;
        return $this;
    }

    /**
     * @param CustomerAuthenticationMethod $customerAuthenticationMethod
     * @return $this
     */
    public function withCustomerAuthenticationMethod($customerAuthenticationMethod)
    {
        $this->customerAuthenticationMethod = $customerAuthenticationMethod;
        return $this;
    }

    /**
     * @param \DateTime $customerAuthenticationTimestamp
     * @return $this
     */
    public function withCustomerAuthenticationTimestamp($customerAuthenticationTimestamp)
    {
        $this->customerAuthenticationTimestamp = $customerAuthenticationTimestamp;
        return $this;
    }

    /**
     * @param string $value
     * @return SecureBuilder
     */
    public function withIdempotencyKey(string $value)
    {
        $this->idempotencyKey = $value;

        return $this;
    }
}