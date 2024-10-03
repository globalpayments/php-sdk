<?php

namespace GlobalPayments\Api\Entities\Reporting;

use GlobalPayments\Api\Entities\AddressCollection;
use GlobalPayments\Api\Entities\Enums\MerchantAccountStatus;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\MerchantAccountType;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;

class MerchantAccountSummary extends BaseSummary
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var MerchantAccountType
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /** @var MerchantAccountStatus */
    public $status;

    /** @var Channel */
    public $channels;

    /** @var array */
    public $permissions;

    /** @var array */
    public $countries;

    /** @var array */
    public $currencies;

    /** @var array<PaymentMethodName> */
    public $paymentMethods;

    /** @var array */
    public $configurations;

    /** @var AddressCollection */
    public $addresses;
}