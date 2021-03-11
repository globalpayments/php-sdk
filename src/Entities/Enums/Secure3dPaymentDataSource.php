<?php
namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class Secure3dPaymentDataSource extends Enum
{
    const AMEX_3DSECURE = 'AMEX 3DSecure';
    const APPLEPAY = 'ApplePay';
    const APPLEPAYAPP = 'ApplePayApp';
    const APPLEPAYWEB = 'ApplePayWeb';
    const GOOGLEPAYAPP = 'GooglePayApp';
    const GOOGLEPAYWEB = 'GooglePayWeb';
    const DISCOVER_3DSECURE = 'Discover 3DSecure';
    const MASTERCARD_3DSECURE = 'MasterCard 3DSecure';
    const VISA_3DSECURE = 'Visa 3DSecure';
    const UPEXPRESS_3DSECURE = 'UPExpress 3DSecure';
    const UPSECUREPLUS_3DSECURE = 'UPSecurePlus 3DSecure';
}
