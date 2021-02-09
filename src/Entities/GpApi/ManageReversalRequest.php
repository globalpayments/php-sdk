<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Utils\StringUtils;

class ManageReversalRequest
{
    public $amount;

    public static function createFromManagementBuilder(ManagementBuilder $builder)
    {
        $request = new ManageReversalRequest();
        $request->amount = !empty($builder->amount) ? StringUtils::toNumeric($builder->amount) : null;

        return $request;
    }
}