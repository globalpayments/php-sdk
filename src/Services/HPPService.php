<?php
namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\HPPData;
use GlobalPayments\Api\Entities\Enums\{Environment, TransactionType};
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;


class HPPService
{
    /**
     * Create a hosted payment page
     * Main static method for creating hosted payment pages using dedicated transaction type
     *
     * @param HPPData $HPPData
     * @throws ArgumentException if no HPPData is provided
     * @return AuthorizationBuilder
     */
    public static function create(HPPData $HPPData): AuthorizationBuilder
    {
        if (!$HPPData) {
            throw new ArgumentException('Hosted payment page data is required');
        }

        return (new AuthorizationBuilder(TransactionType::HOSTED_PAYMENT_PAGE))
            ->withHostedPaymentData($HPPData);
    }    
}
