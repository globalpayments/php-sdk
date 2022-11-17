<?php

namespace GlobalPayments\Api\Builders\RequestBuilder;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiAuthorizationRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiManagementRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiPayFacRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiReportRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpApi\GpApiSecure3DRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpEcom\GpEcomAuthorizationRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpEcom\GpEcomManagementRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpEcom\GpEcomRecurringRequestBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\GpEcom\GpEcomReportRequestBuilder;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;

class RequestBuilderFactory
{
    public static $processes = [
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
            GpApiSecure3DRequestBuilder::class,
            GpApiPayFacRequestBuilder::class
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