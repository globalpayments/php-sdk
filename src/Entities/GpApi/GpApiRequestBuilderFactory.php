<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;

class GpApiRequestBuilderFactory
{
    public static $processes = [
        GpApiSecure3DRequestBuilder::class,
        GpApiReportRequestBuilder::class,
        GpApiManagementRequestBuilder::class,
        GpApiAuthorizationRequestBuilder::class
    ];

    public function getRequestBuilder(BaseBuilder $builder)
    {
        foreach (self::$processes as $processName) {
            if (call_user_func(array($processName, 'canProcess'), $builder)) {
                return new $processName;
            }
        }

        return null;
    }
}