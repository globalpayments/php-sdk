<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Utils\StringUtils;

class ManageCaptureRequest
{
    public $amount;
    public $gratuity;

    public static function createFromManagementBuilder(ManagementBuilder $builder)
    {
        $request = new ManageCaptureRequest();
        $request->amount = StringUtils::toNumeric($builder->amount);
        $request->gratuity = StringUtils::toNumeric($builder->gratuity);

        return $request;
    }
}