<?php

namespace GlobalPayments\Api\Builders\RequestBuilder;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\{
    GpApiAuthorizationRequestBuilder,
    GpApiManagementRequestBuilder,
    GpApiPayFacRequestBuilder,
    GpApiReportRequestBuilder,
    GpApiSecureRequestBuilder
};
use GlobalPayments\Api\Builders\RequestBuilder\GpEcom\{
    GpEcomAuthorizationRequestBuilder,
    GpEcomManagementRequestBuilder,
    GpEcomRecurringRequestBuilder,
    GpEcomReportRequestBuilder
};
use GlobalPayments\Api\Builders\RequestBuilder\TransactionApi\{
    TransactionApiReportRequestBuilder,
    TransactionApiManagementRequestBuilder,
    TransactionApiAuthorizationRequestBuilder
};

use GlobalPayments\Api\Entities\Enums\GatewayProvider;

class RequestBuilderFactory
{
    private static $processes = [
        GatewayProvider::GP_ECOM => [
            GpEcomRecurringRequestBuilder::class,
            GpEcomAuthorizationRequestBuilder::class,
            GpEcomReportRequestBuilder::class,
            GpEcomManagementRequestBuilder::class,
        ],
        GatewayProvider::GP_API => [
            GpApiAuthorizationRequestBuilder::class,
            GpApiManagementRequestBuilder::class,
            GpApiReportRequestBuilder::class,
            GpApiSecureRequestBuilder::class,
            GpApiPayFacRequestBuilder::class
        ],
        GatewayProvider::TRANSACTION_API => [
            TransactionApiReportRequestBuilder::class,
            TransactionApiManagementRequestBuilder::class,
            TransactionApiAuthorizationRequestBuilder::class
        ]
    ];

    public function getRequestBuilder(BaseBuilder $builder, $gatewayProvider)
    {
        if (!isset(self::$processes[$gatewayProvider])) {
            return null;
        }
        foreach (self::$processes[$gatewayProvider] as $gateway => $processName) {
            if (call_user_func(array($processName, 'canProcess'), $builder)) {
                return new $processName;
            }
        }

        return null;
    }
}
