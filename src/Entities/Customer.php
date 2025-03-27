<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;

/**
 * A customer resource.
 *
 * Mostly used in recurring scenarios.
 */
class Customer extends RecurringEntity
{
    /**
     * Customer's title
     *
     * @var string
     */
    public $title;

    /**
     * Customer's first name
     *
     * @var string
     */
    public $firstName;

    /**
     * Customer's last name
     *
     * @var string
     */
    public $lastName;

    /**
     * Customer's middle name
     *
     * @var string
     */
    public $middleName;

    /**
     * Customer's company
     *
     * @var string
     */
    public $company;

    /**
     * Business Name
     *
     * @var string
     */
    public $businessName;

    /**
     * Customer's password
     *
     * @var string
     */
    public $customerPassword;

    /**
     * Customer's date of birth
     *
     * @var string
     */
    public $dateOfBirth;

    /**
     * Customer's domain name
     *
     * @var string
     */
    public $domainName;

    /**
     * Customer's device finger print
     *
     * @var string
     */
    public $deviceFingerPrint;

    /**
     * Customer's address
     *
     * @var Address
     */
    public $address;

    /**
     * Customer's home phone number
     *
     * @var string
     */
    public $homePhone;

    /**
     * Customer's work phone number
     *
     * @var string
     */
    public $workPhone;

    /**
     * Customer's fax phone number
     *
     * @var string
     */
    public $fax;

    /**
     * Customer's mobile phone number
     *
     * @var string
     */
    public $mobilePhone;

    /**
     * Customer's email address
     *
     * @var string
     */
    public $email;

    /**
     * Customer comments
     *
     * @var string
     */
    public $comments;

    /**
     * Customer's department within its organization
     *
     * @var string
     */
    public $department;

    /**
     * Customer resource's status
     *
     * @var string
     */
    public $status;

    /** @var PhoneNumber */
    public $phone;

    /** @var array<CustomerDocument> */
    public $documents = [];

    public array $paymentMethods = [];

    /**
     * Adds a payment method to the customer
     *
     * @param string $paymentId An application derived ID for the payment method
     * @param IPaymentMethod $paymentMethod The payment method
     * @param ?StoredCredential $storedCredential
     *
     * @return RecurringPaymentMethod
     */
    public function addPaymentMethod($paymentId, IPaymentMethod $paymentMethod, ?StoredCredential $storedCredential = null)
    {
        $nameOnAccount = sprintf('%s %s', $this->firstName, $this->lastName);
        if (empty(str_replace(' ', '', $nameOnAccount))) {
            $nameOnAccount = $this->company;
        }

        $payment = new RecurringPaymentMethod($paymentMethod);
        $payment->address = $this->address;
        $payment->customerKey = $this->key;
        $payment->id = $paymentId;
        $payment->nameOnAccount = $nameOnAccount;
        $payment->storedCredential = $storedCredential;
        $this->paymentMethods[$paymentId] = $paymentMethod;
        return $payment;
    }
}
