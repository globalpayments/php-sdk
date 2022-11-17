<?php

namespace GlobalPayments\Api\Entities\Reporting;

use GlobalPayments\Api\Entities\Enums\UserStatus;
use GlobalPayments\Api\Entities\UserLinks;

class MerchantSummary
{
    /**
     * A unique identifier for the object created by Global Payments. The first 3 characters identifies the resource
     * an id relates to.
     *
     * @var string
     */
    public $id;

    /**
     * The label to identify the merchant
     *
     * @var string
     */
    public $name;

    /**
     * @var UserStatus
     */
    public $status;

    /**
     * @var array<UserLinks>
     */
    public $links;
}