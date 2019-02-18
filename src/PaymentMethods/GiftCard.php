<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\Enums\AliasAction;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\PaymentMethods\Interfaces\IBalanceable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IChargable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPrePayable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IReversable;

/**
 * @property string $alias
 * @property string $number
 * @property string $token
 * @property string $trackData
 */
class GiftCard implements
    IPaymentMethod,
    IPrePayable,
    IBalanceable,
    IReversable,
    IChargable
{
    /**
     * Payment method type
     *
     * @var PaymentMethodType
     */
    public $paymentMethodType = PaymentMethodType::GIFT;

    /**
     * Payment method PIN
     *
     * @var string
     */
    public $pin;

    /**
     * Payment method value
     *
     * @internal
     * @var string
     */
    public $value;

    /**
     * Payment method value type
     *
     * @internal
     * @var string
     */
    public $valueType;

    /**
     * Payment method value types
     *
     * @var string[]
     */
    protected static $valueTypes = [
        'alias',
        'number',
        'token',
        'trackData'
    ];

    /**
     * Adds an alias to the payment method
     *
     * @param string $alias Alias to add
     *
     * @return AuthorizationBuilder
     */
    public function addAlias($alias = null)
    {
        return (new AuthorizationBuilder(TransactionType::ALIAS, $this))
            ->withAlias(AliasAction::ADD, $alias);
    }

    /**
     * Activates the payment method with the given amount
     *
     * @param string|float $amount Amount to add
     *
     * @return AuthorizationBuilder
     */
    public function activate($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::ACTIVATE, $this))
            ->withAmount($amount);
    }

    /**
     * Adds value to the payment method
     *
     * @param string|float $amount Amount to add
     *
     * @return AuthorizationBuilder
     */
    public function addValue($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::ADD_VALUE, $this))
            ->withAmount($amount);
    }

    /**
     * Inquires the balance of the payment method
     *
     * @param InquiryType $inquiry Type of inquiry
     *
     * @return AuthorizationBuilder
     */
    public function balanceInquiry($inquiry = null)
    {
        return (new AuthorizationBuilder(TransactionType::BALANCE, $this))
            ->withBalanceInquiryType($inquiry);
    }

    /**
     * Authorizes the payment method and captures the entire authorized amount
     *
     * @param string|float $amount Amount to authorize
     *
     * @return AuthorizationBuilder
     */
    public function charge($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::SALE, $this))
            ->withAmount($amount);
    }

    /**
     * Deactivates the payment method
     *
     * @return AuthorizationBuilder
     */
    public function deactivate()
    {
        return new AuthorizationBuilder(TransactionType::DECLINE, $this);
    }

    /**
     * Removes an alias to the payment method
     *
     * @param string $alias Alias to remove
     *
     * @return AuthorizationBuilder
     */
    public function removeAlias($alias = null)
    {
        return (new AuthorizationBuilder(TransactionType::ALIAS, $this))
            ->withAlias(AliasAction::DELETE, $alias);
    }

    /**
     * Replaces the payment method with the given one
     *
     * @param GiftCard $newCard Replacement gift card
     *
     * @return AuthorizationBuilder
     */
    public function replaceWith($newCard = null)
    {
        return (new AuthorizationBuilder(TransactionType::REPLACE, $this))
            ->withReplacementCard($newCard);
    }

    /**
     * Reverses the payment method
     *
     * @param string|float $amount Amount to reverse
     *
     * @return AuthorizationBuilder
     */
    public function reverse($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::REVERSAL, $this))
            ->withAmount($amount);
    }

    /**
     * Rewards the payment method
     *
     * @param string|float $amount Amount to reward
     *
     * @return AuthorizationBuilder
     */
    public function rewards($amount = null)
    {
        return (new AuthorizationBuilder(TransactionType::REWARD, $this))
            ->withAmount($amount);
    }

    /**
     * Creates a new payment method
     *
     * @param string $alias Alias to use
     *
     * @return GiftCard
     */
    public static function create($alias = null)
    {
        $card = new static();

        $response = (new AuthorizationBuilder(TransactionType::ALIAS, $card))
            ->withAlias(AliasAction::CREATE, $alias)
            ->execute();

        if ($response->responseCode === '00') {
            return $response->giftCard;
        }

        throw new ApiException($response->responseMessage);
    }

    public function __get($name)
    {
        if (!in_array($name, static::$valueTypes)) {
            throw new ArgumentException(sprintf('Property `%s` does not exist on GiftCard', $name));
        }

        return $this->value;
    }

    public function __isset($name)
    {
        return in_array($name, static::$valueTypes) || isset($this->{$name});
    }

    public function __set($name, $value)
    {
        if (!in_array($name, static::$valueTypes)) {
            throw new ArgumentException(sprintf('Property `%s` does not exist on GiftCard', $name));
        }

        $this->value = $value;
        switch ($name) {
            case 'alias':
                $this->valueType = 'Alias';
                return;
            case 'number':
                $this->valueType = 'CardNbr';
                return;
            case 'token':
                $this->valueType = 'TokenValue';
                return;
            case 'trackData':
                $this->valueType = 'TrackData';
                return;
        }
    }
}
