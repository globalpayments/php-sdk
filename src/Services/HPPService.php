<?php
namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\HPPData;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
/**
 * Service class for creating hosted payment pages
 */

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
        return (new AuthorizationBuilder(TransactionType::HOSTED_PAYMENT_PAGE))
            ->withHostedPaymentData($HPPData);
    }    
}
