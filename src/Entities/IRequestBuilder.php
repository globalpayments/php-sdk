<?php


namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Builders\BaseBuilder;

interface IRequestBuilder
{
    public function buildRequest(BaseBuilder $builder, $config);

    public static function canProcess($builder);
}