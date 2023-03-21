<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\PaymentMethods\TransactionReference;

/**
 * Transaction response.
 *
 * @property string $authorizationCode The authorization code provided by the issuer.
 * @property string $clientTransactionId The client transaction ID supplied in the request.
 * @property string $checkSaleId The check sale ID supplied in the request.
 * @property string $checkRefundId The check refund ID supplied in the request.
 * @property string $orderId The order ID supplied in the request.
 * @property PaymentMethodType $paymentMethodType The type of payment made in the request.
 * @property string $transactionId The transaction ID.
 * @property AlternativePaymentResponse $alternativePaymentResponse The APM response
 * @property BNPLResponse $bnplResponse The BNP response
 */
class Transaction
{
    /**
     * The authorized amount.
     *
     * @var string
     */
    public $authorizedAmount;

    /**
     * The available balance of the payment method.
     *
     * @var string
     */
    public $availableBalance;

    /**
     * The address verification service (AVS) response code.
     *
     * @var string
     */
    public $avsResponseCode;

    /**
     * The address verification service (AVS) response message.
     *
     * @var string
     */
    public $avsResponseMessage;

    /**
     * The balance on the account after the transaction.
     *
     * @var string
     */
    public $balanceAmount;

    /**
     * Summary of the batch.
     *
     * @var BatchSummary
     */
    public $batchSummary;

    /**
     * The type of card used in the transaction.
     *
     * @var string
     */
    public $cardSecurityResponse;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->brand
     * The type of card used in the transaction.
     *
     * @var string
     */
    public $cardType;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->maskedNumberLast4
     * The last four digits of the card number used in
     * the transaction.
     *
     * @var string
     */
    public $cardLast4;

    /**
     * The consumer authentication (3DSecure) verification
     * value response code.
     *
     * @var string
     */
    public $cavvResponseCode;

    /**
     * The commercial indicator for Level II/III.
     *
     * @var string
     */
    public $commercialIndicator;

    /**
     * The card verification number (CVN) response code.
     *
     * @var string
     */
    public $cvnResponseCode;

    /**
     * The card verification number (CVN) response message.
     *
     * @var string
     */
    public $cvnResponseMessage;

    /**
     * The EMV response from the issuer.
     *
     * @var string
     */
    public $emvIssuerResponse;

    /**
     * The host response date
     *
     * @var DateTime
     */
    public $hostResponseDate;

    /**
     * @var bool
     */
    public $multiCapture;

    /**
     * @var int
     */
    public $multiCapturePaymentCount;

    /**
     * @var int
     */
    public $multiCaptureSequence;

    /**
     * The original Transaction Type holds additional flag of type.
     *
     * @var string
     */
    public $originalTransactionType;

    /**
     * The remaining points on the account after the transaction.
     *
     * @var string
     */
    public $pointsBalanceAmount;

    /**
     * The recurring profile data code.
     *
     * @var string
     */
    public $recurringDataCode;

    /**
     * The reference number provided by the issuer.
     *
     * @var string
     */
    public $referenceNumber;

    /**
     * The original response code from the issuer/gateway.
     *
     * @var string
     */
    public $responseCode;

    /**
     * The original response message from the issuer/gateway.
     *
     * @var string
     */
    public $responseMessage;

    /** @var array */
    public $splitTenderBalanceDueAmt;

    /** @var array */
    public $responseValues;

    /** @var string */
    public $schemeId;

    /**
     * The response from ThreeDSecure
     *
     * @internal
     * @var ThreeDSecure
     */
    public $threeDSecure;

    /**
     * The timestamp of the transaction.
     *
     * @var string
     */
    public $timestamp;

    /**
     * The transaction descriptor.
     *
     * @var string
     */
    public $transactionDescriptor;

    /**
     * The payment token returned in the transaction.
     *
     * @var string
     */
    public $token;

    /** @var PaymentMethodUsageMode */
    public $tokenUsageMode;

    /**
     * The transaction reference.
     *
     * @internal
     * @var TransactionReference
     */
    public $transactionReference;

    /**
     * The gift card.
     *
     * @internal
     * @var GiftCard
     */
    public $giftCard;

    /** @var DccRateData */
    public $dccRateData;

    /**
     * The Dcc Response
     *
     * @internal
     * @var FraudManagementResponse
     */
    public $fraudFilterResponse;

    /**
     * The address verification service (AVS) address response code.
     *
     * @var string
     */
    public $avsAddressResponse;

    public $customerReceipt;

    public $merchantReceipt;

    public $transactionKey;

    /*
     * Card on File field response
     * @var string
     *
     */
    public $cardBrandTransactionId;

    /**
     * The response from Propay
     *
     * @var PayFacResponseData
     */
    public $payFacData;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->cardholderName
     */
    public $cardholderName;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->cardNumber
     */
    public $cardNumber;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->maskedCardNumber
     */
    public $maskedCardNumber;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->cardExpMonth
     */
    public $cardExpMonth;

    /**
     * @deprecated  Will soon be replaced with $cardDetails->cardExpYear
     */
    public $cardExpYear;

    /**
     * Used for ACH transactions
     *
     * @var string
     */
    public $accountType;

    /**
     * Used for ACH transactions
     *
     * @var string
     */
    public $accountNumberLast4;

    /** @var string */
    public $fingerprint;

    /** @var string */
    public $fingerprintIndicator;

    /**
     * The bank transfer / open banking response data
     *
     * @var BankPaymentResponse
     */
    public $bankPaymentResponse;

    /** @var PayLinkResponse */
    public $payLinkResponse;

    /** @var CardIssuerResponse $cardIssuerResponse */
    public $cardIssuerResponse;

    /** @var PayerDetails */
    public $payerDetails;

    /** @var Card */
    public $cardDetails;

    /**
     * Creates a `Transaction` object from a stored transaction ID.
     *
     * Used to expose management requests on the original transaction
     * at a later date/time. If `$orderId` is not necessary, `$paymentMethodType`
     * can be sent as the second argument.
     *
     * @param string $transactionId The original transaction ID
     * @param string $orderId The original transaction's order ID (optional)
     * @param PaymentMethodType $paymentMethodType The original payment method type.
     *     Defaults to `PaymentMethodType::CREDIT`.
     *
     * @return Transaction
     */
    public static function fromId($transactionId, $orderId = null, $paymentMethodType = null)
    {
        try {
            $paymentMethodType = PaymentMethodType::validate($orderId);
        } catch (ArgumentException $ex) {
            /** */
        }

        if ($orderId === null && $paymentMethodType === null) {
            $paymentMethodType = PaymentMethodType::CREDIT;
        }

        $txn = new Transaction();
        $txn->transactionReference = new TransactionReference();
        $txn->transactionReference->transactionId = $transactionId;
        $txn->transactionReference->paymentMethodType = $paymentMethodType;
        $txn->transactionReference->orderId = $orderId;
        return $txn;
    }

    public static function fromClientTransactionId($clientTransactionId, $orderId = null, $paymentMethodType = null)
    {
        try {
            $paymentMethodType = PaymentMethodType::validate($orderId);
        } catch (ArgumentException $ex) {
            /** */
        }

        if ($orderId === null && $paymentMethodType === null) {
            $paymentMethodType = PaymentMethodType::CREDIT;
        }

        $txn = new Transaction();
        $txn->transactionReference = new TransactionReference();
        $txn->transactionReference->clientTransactionId = $clientTransactionId;
        $txn->transactionReference->paymentMethodType = $paymentMethodType;
        $txn->transactionReference->orderId = $orderId;
        return $txn;
    }

    /**
     * Creates an additional authorization against the original transaction.
     *
     * @param string|float $amount The additional amount to authorize
     *
     * @return ManagementBuilder
     */
    public function additionalAuth($amount = null)
    {
        return (new ManagementBuilder(TransactionType::AUTH))
            ->withPaymentMethod($this->transactionReference)
            ->withAmount($amount);
    }

    /**
     * Captures the original transaction.
     *
     * @param string|float $amount The amount to capture
     *
     * @return ManagementBuilder
     */
    public function capture($amount = null)
    {
        $builder = (new ManagementBuilder(TransactionType::CAPTURE))
            ->withPaymentMethod($this->transactionReference)
            ->withAmount($amount);

        if ($this->multiCapture) {
            $builder->withMultiCapture($this->multiCaptureSequence, $this->multiCapturePaymentCount);
        }

        return $builder;
    }

    /**
     * Edits the original transaction.
     *
     * @return ManagementBuilder
     */
    public function edit()
    {
        $builder = (new ManagementBuilder(TransactionType::EDIT))
            ->withPaymentMethod($this->transactionReference);

        if ($this->commercialIndicator !== null) {
            $builder = $builder->withModifier(TransactionModifier::LEVEL_II);
        }

        if ($this->cardType !== null) {
            $builder->cardType = $this->cardType;
        }

        return $builder;
    }

    /**
     * Places the original transaction on hold.
     *
     * @return ManagementBuilder
     */
    public function hold()
    {
        return (new ManagementBuilder(TransactionType::HOLD))
            ->withPaymentMethod($this->transactionReference);
    }

    /**
     * Refunds/returns the original transaction.
     *
     * @param string|float $amount The amount to refund/return
     *
     * @return ManagementBuilder
     */
    public function refund($amount = null)
    {
        return (new ManagementBuilder(TransactionType::REFUND))
            ->withPaymentMethod($this->transactionReference)
            ->withAmount($amount);
    }

    /**
     * Refresh the authorization associated with a transaction to get a more recent authcode or
     * reauthorize a transaction reversed in error.
     *
     * @param string|float $amount
     *
     * @return ManagementBuilder
     */
    public function reauthorized($amount = null)
    {
        return (new ManagementBuilder(TransactionType::REAUTH))
            ->withPaymentMethod($this->transactionReference)
            ->withAmount($amount);
    }

    /**
     * Releases the original transaction from a hold.
     *
     * @return ManagementBuilder
     */
    public function release()
    {
        return (new ManagementBuilder(TransactionType::RELEASE))
            ->withPaymentMethod($this->transactionReference);
    }

    /**
     * Reverses the original transaction.
     *
     * @param string|float $amount The original authorization amount
     *
     * @return ManagementBuilder
     */
    public function reverse($amount = null)
    {
        return (new ManagementBuilder(TransactionType::REVERSAL))
            ->withPaymentMethod($this->transactionReference)
            ->withAmount($amount);
    }

    /**
     * Voids the original transaction.
     *
     * @return ManagementBuilder
     */
    public function void($amount = null)
    {
        return (new ManagementBuilder(TransactionType::VOID))
            ->withPaymentMethod($this->transactionReference)
            ->withAmount($amount);
    }

    /**
     * Confirm an original transaction. For now it is used for the APM transactions with PayPal
     *
     * @return ManagementBuilder
     */
    public function confirm($amount = null)
    {
        return (new ManagementBuilder(TransactionType::CONFIRM))
            ->withPaymentMethod($this->transactionReference)
            ->withAmount($amount);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'authorizationCode':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->authCode;
                }
                return null;
            case 'clientTransactionId':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->clientTransactionId;
                }
                return null;
            case 'checkRefundId':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->checkRefundId;
                }
                return null;
            case 'checkSaleId':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->checkSaleId;
                }
                return null;
            case 'orderId':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->orderId;
                }
                return null;
            case 'paymentMethodType':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->paymentMethodType;
                }
                return PaymentMethodType::CREDIT;
            case 'transactionId':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->transactionId;
                }
                return null;
            case 'alternativePaymentResponse':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->alternativePaymentResponse;
                }
                return null;
            case 'bnplResponse':
                if ($this->transactionReference !== null) {
                    return $this->transactionReference->bnplResponse;
                }
                return null;
            default:
                break;
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new ArgumentException(sprintf('Property `%s` does not exist on Transaction', $name));
    }

    public function __isset($name)
    {
        return in_array($name, [
            'transactionId',
            'orderId',
            'authorizationId',
            'paymentMethodType',
            'clientTransactionId',
            'checkRefundId',
            'checkSaleId',
            'alternativePaymentResponse',
            'bnplResponse'
        ]) || isset($this->{$name});
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'authorizationCode':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->authCode = $value;
                return;
            case 'clientTransactionId':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->clientTransactionId = $value;
                return;
            case 'checkRefundId':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->checkRefundId = $value;
                return;
            case 'checkSaleId':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->checkSaleId = $value;
                return;
            case 'orderId':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->orderId = $value;
                return;
            case 'paymentMethodType':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->paymentMethodType = $value;
                return;
            case 'transactionId':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->transactionId = $value;
                return;
            case 'alternativePaymentResponse':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->alternativePaymentResponse = $value;
                return;
            case 'bnplResponse':
                if (!$this->transactionReference instanceof TransactionReference) {
                    $this->transactionReference = new TransactionReference();
                }
                $this->transactionReference->bnplResponse = $value;
                return;
            default:
                break;
        }

        if (property_exists($this, $name)) {
            return $this->{$name} = $value;
        }

        throw new ArgumentException(sprintf('Property `%s` does not exist on Transaction', $name));
    }
}
