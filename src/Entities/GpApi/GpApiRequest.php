<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Entities\Request;

class GpApiRequest extends Request
{
    const ACCESS_TOKEN_ENDPOINT = '/accesstoken';
    const TRANSACTION_ENDPOINT = '/transactions';
    const PAYMENT_METHODS_ENDPOINT = '/payment-methods';
    const VERIFICATIONS_ENDPOINT = '/verifications';
    const DEPOSITS_ENDPOINT = '/settlement/deposits';
    const DISPUTES_ENDPOINT = '/disputes';
    const SETTLEMENT_DISPUTES_ENDPOINT = '/settlement/disputes';
    const SETTLEMENT_TRANSACTIONS_ENDPOINT = '/settlement/transactions';
    const AUTHENTICATIONS_ENDPOINT = '/authentications';
    const BATCHES_ENDPOINT = '/batches';
    const ACTIONS_ENDPOINT = '/actions';
    const MERCHANT_MANAGEMENT_ENDPOINT = '/merchants';
    const DCC_ENDPOINT = '/currency-conversions';
    const PAYLINK_ENDPOINT = '/links';
    const RISK_ASSESSMENTS = '/risk-assessments';
}