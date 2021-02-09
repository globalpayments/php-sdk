<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Utils\StringUtils;

class ManageRefundRequest
{
    public $amount;

    public static function createFromManagementBuilder(ManagementBuilder $builder)
    {
        $request = new ManageRefundRequest();
        $request->amount = !empty($builder->amount) ? StringUtils::toNumeric($builder->amount) : null;

        return $request;
    }
}