<?php

namespace GlobalPayments\Api\Entities;

interface IInstallmentEntity
{
    /**
     * Creates a resource
     * @param string $configName
     */
    public function create(string $configName);
}