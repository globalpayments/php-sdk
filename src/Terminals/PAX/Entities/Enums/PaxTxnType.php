<?php

namespace GlobalPayments\Api\Terminals\PAX\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class PaxTxnType extends Enum
{

    const MENU = '00';
    const SALE_REDEEM = '01';
    const RETURN_REQUEST = '02';
    const AUTH = '03';
    const POSTAUTH = '04';
    const FORCED = '05';
    const ADJUST = '06';
    const WITHDRAWAL = '07';
    const ACTIVATE = '08';
    const ISSUE = '09';
    const ADD = '10';
    const CASHOUT = '11';
    const DEACTIVATE = '12';
    const REPLACE = '13';
    const MERGE = '14';
    const REPORTLOST = '15';
    const VOID = '16';
    const V_SALE = '17';
    const V_RTRN = '18';
    const V_AUTH = '19';
    const V_POST = '20';
    const V_FRCD = '21';
    const V_WITHDRAW = '22';
    const BALANCE = '23';
    const VERIFY = '24';
    const REACTIVATE = '25';
    const FORCED_ISSUE = '26';
    const FORCED_ADD = '27';
    const UNLOAD = '28';
    const RENEW = '29';
    const GET_CONVERT_DETAIL = '30';
    const CONVERT = '31';
    const TOKENIZE = '32';
    const REVERSAL = '99';
}
