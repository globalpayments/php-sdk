<?php
//Entity class for hosted payment page's order properties

namespace GlobalPayments\Api\Entities;

class HPPOrder
{
    /**
     * Amount to be charged, must be smallest common denominator for the currency (e.g. cents for USD)
     * @var string|null
     */
    public ?string $amount = null;
    /**
     * Currency code in ISO 4217 format (e.g. 'USD', 'EUR')
     * @var string|null
     */
    public ?string $currency = null;
    /**
     * Reference for the order, will be shown on realcontroll
     * @var string|null
     */
    public ?string $reference = null;
    /**
     * Configuration for the transaction, including channel, capture mode, allowed payment methods, etc.
     * @var HPPTransactionConfiguration|null
     */
    public ?HPPTransactionConfiguration $HPPTransactionConfiguration = null;
    /**
     * Configuration for the payment method, including allowed payment methods, billing address requirements, etc.
     * @var HPPPaymentMethodConfiguration|null
     */
    public ?HPPPaymentMethodConfiguration $HPPPaymentMethodConfiguration = null;
    /**
     * Users shipping address object
     * @var Address|null
     */
    public ?Address $shippingAddress = null;
    /**
     * Users Shipping phone number object
     * @var PhoneNumber|null
     */
    public ?PhoneNumber $shippingPhone = null;

    /**
     * Validate the hosted payment order data
     * @return errors Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields validation
        if (!$this->amount || strlen($this->amount) === 0) {
            $errors[] = 'Amount is required for hosted payment order';
        } elseif (!is_numeric($this->amount) || floatval($this->amount) <= 0) {
            $errors[] = 'Amount must be a positive number';
        }

        if (empty($this->currency)) {
            $errors[] = 'Currency is required for hosted payment order';
        } elseif (strlen($this->currency) !== 3) {
            $errors[] = 'Currency must be a 3-character code';
        }

        // Transaction configuration validation
        if ($this->HPPTransactionConfiguration) {
            $transactionErrors = $this->HPPTransactionConfiguration->validate();
            $errors = array_merge($errors, $transactionErrors);
        }

        // Payment method configuration validation
        if ($this->HPPPaymentMethodConfiguration) {
            $paymentMethodErrors = $this->HPPPaymentMethodConfiguration->validate();
            $errors = array_merge($errors, $paymentMethodErrors);
        }

        return $errors;
    }
}
