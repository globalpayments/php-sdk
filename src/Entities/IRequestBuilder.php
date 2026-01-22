<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Builders\BaseBuilder;

interface IRequestBuilder
{
    public function buildRequest(BaseBuilder $builder, mixed $config): mixed;

    public function buildRequestFromJson(mixed $jsonRequest, mixed $config): mixed;

    public static function canProcess(?BaseBuilder $builder = null): bool;
}
