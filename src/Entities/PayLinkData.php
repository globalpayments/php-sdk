<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\PayLinkStatus;
use GlobalPayments\Api\Entities\Enums\PayLinkType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodUsageMode;
use PHPStanVendor\Nette\Utils\DateTime;

/**
 * A PayLink resource.
 */
class PayLinkData
{
    /**
     * Describes the type of link that will be created.
     *
     * @var PayLinkType
     */
    public $type;

    /**
     * Indicates whether the link can be used once or multiple times
     *
     * @var PaymentMethodUsageMode
     */
    public $usageMode;

    /** @var array<string> */
    public $allowedPaymentMethods;

    /**
     * The number of the times that the link can be used or paid.
     * @var integer
     */
    public $usageLimit;

    /** @var PayLinkStatus */
    public $status;

    /**
     * A descriptive name for the link. This will be visible to the customer on the payment page.
     *
     * @var string
     */
    public $name;

    /**
     * Indicates if you want to capture the customers shipping information on the hosted payment page.
     * If you enable this field you can also set an optional shipping fee in the shipping_amount.
     * @var boolean
     */
    public $isShippable;

    /**
     * Indicates the cost of shipping when the shippable field is set to YES.
     * @var float
     */
    public $shippingAmount;

    /**
     * Indicates the date and time after which the link can no longer be used or paid.
     * @var DateTime
     */
    public $expirationDate;

    /**
     * Images that will be displayed to the customer on the payment page.
     * @var array<string>
     */
    public $images;

    /**
     * The merchant URL that the customer will be redirected to.
     *
     * @var string
     */
    public $returnUrl;

    /**
     * The merchant URL (webhook) to notify the merchant of the latest status of the transaction
     *
     * @var string
     */
    public $statusUpdateUrl;

    /**
     * The merchant URL that the customer will be redirected to if they chose to cancel
     *
     * @var string
     */
    public $cancelUrl;
}
