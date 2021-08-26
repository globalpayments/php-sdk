<?php

namespace GlobalPayments\Api\Mapping;

use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;

class EnumMapping
{
    /**
     * @param GatewayProvider $gateway
     * @param AccountType $accountType
     *
     * @return string|null
     */
    public static function mapAccountType($gateway, $accountType)
    {
        if ($gateway === GatewayProvider::GP_API) {
            switch ($accountType) {
                case AccountType::SAVINGS:
                    return 'SAVING';
                case AccountType::CHECKING:
                    return 'CHECKING';
                case AccountType::CREDIT:
                    return 'CREDIT';
                default:
                    return null;
            }
        }
    }

    /**
     * @param GatewayProvider $gateway
     * @param EncyptedMobileType $type
     *
     * @return string|null
     */
    public static function mapDigitalWalletType($gateway, $type)
    {
        if ($gateway === GatewayProvider::GP_API) {
            switch ($type) {
                case EncyptedMobileType::APPLE_PAY:
                    return 'APPLEPAY';
                case EncyptedMobileType::GOOGLE_PAY:
                    return 'PAY_BY_GOOGLE';
                default:
                    return null;
            }
        }
    }

}