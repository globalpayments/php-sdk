<?php
//Entity class for hosted payment page's order properties

namespace GlobalPayments\Api\Entities;

class HPPOrder
{
    public const ALLOWED_SURCHARGE_CARD_TYPES = ['DEBIT', 'CREDIT', 'COMMERCIAL'];

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
     * Surcharge configuration array with card type and amount
     * @var array|null
     */
    public ?array $surcharge = null;

    /**
     * Validate the hosted payment order data
     * @return errors Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        $amount = $this->amount !== null ? trim($this->amount) : '';

        // Required fields validation
        if ($amount === '') {
            $errors[] = 'Amount is required for hosted payment order';
        } elseif (!is_numeric($amount) || floatval($amount) <= 0) {
            $errors[] = 'Amount must be a positive number';
        } elseif (!preg_match('/^\d+$/', $amount)) {
            $errors[] = 'Amount must be a whole-number string in minor units';
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

        // Surcharge validation
        if ($this->surcharge !== null) {
            if (!is_array($this->surcharge)) {
                $errors[] = 'Surcharge must be an array of entries';
            } else {
                foreach ($this->surcharge as $index => $surcharge) {
                    if (!is_array($surcharge)) {
                        $errors[] = "Invalid surcharge entry at index {$index}. Each surcharge must be an array with 'card_type' and 'amount'.";
                        continue;
                    }

                    $rawCardType = $surcharge['card_type'] ?? '';
                    $rawAmount = $surcharge['amount'] ?? '';
                    if (!is_scalar($rawCardType) || is_bool($rawCardType) || !is_scalar($rawAmount) || is_bool($rawAmount)) {
                        $errors[] = "Invalid surcharge entry at index {$index}. card_type and amount must be strings.";
                        continue;
                    }
                    $cardType = strtoupper(trim((string)$rawCardType));
                    $amount = trim((string)$rawAmount);

                    if ($cardType === '' || $amount === '') {
                        $errors[] = "Invalid surcharge entry at index {$index}. Both card_type and amount are required.";
                        continue;
                    }

                    if (!in_array($cardType, self::ALLOWED_SURCHARGE_CARD_TYPES, true)) {
                        $errors[] = "Invalid surcharge card type '{$cardType}'. Allowed values: " . implode(', ', self::ALLOWED_SURCHARGE_CARD_TYPES);
                    }

                    if (!preg_match('/^\d+$/', $amount)) {
                        $errors[] = 'Invalid surcharge amount. Amount must be a whole-number string in minor units.';
                    }
                }
            }
        }

        return $errors;
    }
}
