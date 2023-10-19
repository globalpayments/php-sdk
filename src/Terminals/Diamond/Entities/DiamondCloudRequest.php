<?php

namespace GlobalPayments\Api\Terminals\Diamond\Entities;

use GlobalPayments\Api\Entities\Request;

class DiamondCloudRequest extends Request
{
    const SALE = '/sale';
    const SALE_RETURN = '/return';
    const AUTH = '/auth';
    const VOID = '/void';
    const CAPTURE_EU = '/authComplete';
    const CAPTURE = '/capture';
    const CANCEL_AUTH = '/authCancel';
    const INCREASE_AUTH = '/authIncreasing';
    const TIP_ADJUST = '/tip';
    const EBT_FOOD = '/ebtFood';
    const EBT_RETURN = '/ebtReturn';
    const EBT_BALANCE = '/ebtBalance';
    const GIFT_REDEEM = '/giftRedeem';
    const GIFT_BALANCE = '/giftBalance';
    const GIFT_RELOAD = '/giftReload';
    const RECONCILIATION = '/reconciliation';
}