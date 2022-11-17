<?php

namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\Entities\Enums\UserStatus;
use GlobalPayments\Api\Entities\Enums\UserType;

class UserReference
{
    /**
     * @var string
     */
    public $userId;

    /** @var UserType */
    public $userType;

    /** @var UserStatus */
    public $userStatus;
}