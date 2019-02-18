<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class ShoppingCartPlugin extends Enum
{
    const MAGENTO = 'Magento';
    const WOO_COMMERCE = 'WooCommerce';
    const WORD_PRESS = 'WordPress';
    const BIG_COMMERCE = 'BigCommerce';
    const SHOPIFY = 'Shopify';
    const OS_COMMERCE = 'OsCommerce';
    const X_CART = 'X-Cart';
    const GRAVITY_FORMS = 'Gravity Forms';
    const OTHER = 'Other';
}
