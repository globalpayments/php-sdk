<?php

namespace GlobalPayments\Api\Mapping;

use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\EmvLastChipRead;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;

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

    /**
     * @param GatewayProvider $gateway
     * @param StoredCredentialInitiator $value
     * @return string|null
     */
    public static function mapStoredCredentialInitiator($gateway, $value)
    {
        switch ($gateway) {
            case GatewayProvider::GP_API:
                switch ($value) {
                    case StoredCredentialInitiator::CARDHOLDER:
                        return strtoupper(StoredCredentialInitiator::PAYER);
                    case StoredCredentialInitiator::MERCHANT:
                        return strtoupper(StoredCredentialInitiator::MERCHANT);
                    default:
                        return null;
                }
            default:
                return null;
        }
    }

    /**
     * @param GatewayProvider $gateway
     * @param EmvLastChipRead $value
     * @return string|null
     */
    public static function mapEmvLastChipRead($gateway, $value)
    {
        switch ($gateway) {
            case GatewayProvider::GP_API:
                switch ($value) {
                    case EmvLastChipRead::SUCCESSFUL:
                        return 'PREV_SUCCESS';
                    case EmvLastChipRead::FAILED:
                        return 'PREV_FAILED';
                    default:
                        return null;
                }
            default:
                return null;
        }
    }

    public static function mapCardType($gateway, $value)
    {
        switch ($gateway) {
            case GatewayProvider::GP_ECOM:
            case GatewayProvider::GP_API:
                switch ($value) {
                    case 'DinersClub':
                        return CardType::DINERS;
                    default:
                        return $value;
                }
            default:
                return null;
        }
    }
}