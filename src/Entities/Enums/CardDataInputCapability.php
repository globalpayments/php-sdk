<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CardDataInputCapability extends Enum
{
    const UNKNOWN                           = 'UNKNOWN';
    const NO_TERMINAL_MANUAL                = 'NO_TERMINAL_MANUAL';
    const MAGSTRIPE_READ_ONLY               = 'MAGSTRIPE_READ_ONLY';
    const OCR                               = 'OCR';
    const ICC_CHIP_READ_ONLY                = 'ICC_CHIP_READ_ONLY';
    const KEYED_ENTRY_ONLY                  = 'KEYED_ENTRY_ONLY';
    const MAGSTRIPE_CONTACTLESS_ONLY        = 'MAGSTRIPE_CONTACTLESS_ONLY';
    const MAGSTRIPE_KEYED_ENTRY_ONLY        = 'MAGSTRIPE_KEYED_ENTRY_ONLY';
    const MAGSTRIPE_ICC_KEYED_ENTRY_ONLY    = 'MAGSTRIPE_ICC_KEYED_ENTRY_ONLY';
    const MAGSTRIPE_ICC_ONLY                = 'MAGSTRIPE_ICC_ONLY';
    const ICC_KEYED_ENTRY_ONLY              = 'ICC_KEYED_ENTRY_ONLY';
    const ICC_CHIP_CONTACT_CONTACTLESS      = 'ICC_CHIP_CONTACT_CONTACTLESS';
    const ICC_CONTACTLESS_ONLY              = 'ICC_CONTACTLESS_ONLY';
    const OTHER_CAPABILITY_FOR_MASTERCARD   = 'OTHER_CAPABILITY_FOR_MASTERCARD';
    const MAGSTRIPE_SIGNATURE_FOR_AMEX_ONLY = 'MAGSTRIPE_SIGNATURE_FOR_AMEX_ONLY';
}
