<?php

namespace GlobalPayments\Api\Entities\TransactionApi;

use GlobalPayments\Api\Entities\Request;

class TransactionApiRequest extends Request
{
    const CREDITSALE      = 'creditsales';
    const CREDITSALEREF   = 'creditsales/reference_id';
    const CREDITSALEVOID  = 'voids';
    const CREDITAUTH      = 'creditauths';
    const CHECKREFUND     = 'checkrefunds';
    const CHECKREFUNDREF  = 'checkrefunds/reference_id';
    const CHECKSALES      = 'checksales';
    const CHECKSALESREF   = 'checksales/reference_id';
    const CREDITREFUND    = 'creditreturns';
    const CREDITREFUNDREF = 'creditreturns/reference_id';
}
