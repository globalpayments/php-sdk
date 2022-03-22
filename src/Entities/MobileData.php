<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\SdkInterface;
use GlobalPayments\Api\Entities\Enums\SdkUiType;

class MobileData
{
    /** @var string */
    public $encodedData;

    /** @var string */
    public $applicationReference;

    /** @var SdkInterface */
    public $sdkInterface;

    /** @var array<SdkUiType> */
    public $sdkUiTypes;

    public $ephemeralPublicKey;

    /** @var integer */
    public $maximumTimeout;

    /** @var string */
    public $referenceNumber;

    /** @var string */
    public $sdkTransReference;
}