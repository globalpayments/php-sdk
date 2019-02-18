<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class AllCardsAcceptedConsume extends Enum
{
    const ALL_CARDS_ACCEPTED = 'AllCardsAccepted';
    const CONSUMER_PREPAID_DEBIT_CHECK_CARDS_ONLY = 'Consumer Prepaid/Debit (Check Cards) Only';
    const CREDIT_BUSINESS_CARDS_ONLY = 'Credit/Business Cards Only';
}
