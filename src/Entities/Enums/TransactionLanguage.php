<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Language for transaction.
 * Unless overridden by EMV chip in payment card, POS device prompts and receipts will be generated in this language.
 */
class TransactionLanguage extends Enum
{
    const EN_US = 'en-US';
    const EN_CA = 'en-CA';
    const FR_CA = 'fr-CA';
    const EN_AU = 'en-AU';
    const EN_NZ = 'en-NZ';
    const EN_GB = 'en-GB';
}
