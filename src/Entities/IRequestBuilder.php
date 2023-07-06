<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Builders\BaseBuilder;

interface IRequestBuilder
{
    public function buildRequest(BaseBuilder $builder, $config);

    public function buildRequestFromJson($jsonRequest, $config);

    public static function canProcess($builder = null);
}
