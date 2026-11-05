<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use Zend\I18n\Validator\DateTime;

/**
 * DCC data
 */
class DccRateData
{
    /**
     * The amount
     *
     * @var float|string|null
     */
    public $cardHolderAmount;
    
    /**
     * The currency
     *
     * @var string
     */
    public $cardHolderCurrency;
    
    /**
     * The name of the CCP (Currency Conversion Processor) the request is to be sent to
     *
     * @var DccProcessor
     */
    public $dccProcessor;
    
    /**
     * Rate Offered for the Exchange
     *
     * @var string
     */
    public $cardHolderRate;
    
    /**
     * Rate type, 'S' for authorisation transactions (Sale). 'R' for Refunds.
     *
     * @var DccRateType
     */
    public $dccRateType;
    
    /**
     * The type of currency conversion rate obtained. This is usually set to 1 but can contain other values.
     * Please consult with your Currency Conversion Processor.
     *
     * @var string
     */
    public $dccType;
    
    /**
     * The orderId
     *
     * @var string
     */
    public $orderId;
    /** @var string */
    public $dccId;
    /** @var string */
    public $commissionPercentage;
    /** @var string */
    public $exchangeRateSourceName;
    /** @var DateTime */
    public $exchangeRateSourceTimestamp;
    /** @var float|string|null */
    public $merchantAmount;
    /** @var string */
    public $merchantCurrency;
    /** @var string */
    public $marginRatePercentage;

    /** @var string|null */
    public ?string $model = null;

    /** @var string|null */
    public ?string $status = null;

    /** @var string|null */
    public ?string $country = null;

    /** @var string|null */
    public ?string $channel = null;

    /** @var string|null */
    public ?string $transactionType = null;

    /** @var string|null */
    public ?string $minorUnits = null;

    /** @var string|null */
    public ?string $formattedAmount = null;

    /** @var string|null */
    public ?string $merchantId = null;

    /** @var string|null */
    public ?string $merchantName = null;

    /** @var string|null */
    public ?string $accountId = null;

    /** @var string|null */
    public ?string $accountName = null;

    /** @var string|null */
    public ?string $reference = null;

    /** @var string|null */
    public ?string $paymentMethodResult = null;

    /** @var string|null */
    public ?string $paymentMethodMessage = null;

    /** @var string|null */
    public ?string $paymentMethodEntryMode = null;

    /** @var string|null */
    public ?string $paymentMethodName = null;

    /** @var Card|null */
    public ?Card $cardDetails = null;

    /** @var Action|null */
    public ?Action $action = null;
}
