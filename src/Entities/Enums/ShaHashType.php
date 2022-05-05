<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class ShaHashType extends Enum
{
    const SHA1 = "SHA1";
    const SHA256 = "SHA256";
    const SHA512 = "SHA512";
}