<?php


namespace GlobalPayments\Api\Entities\Enums;


use GlobalPayments\Api\Entities\Enum;

class StoredCredentialReason extends Enum
{
    const INCREMENTAL = 'INCREMENTAL';
    const RESUBMISSION = 'RESUBMISSION';
    const REAUTHORIZATION = 'REAUTHORIZATION';
    const DELAYED = 'DELAYED';
    const NO_SHOW = 'NO_SHOW';
}