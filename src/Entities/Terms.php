<?php

namespace GlobalPayments\Api\Entities;

class Terms
{
    public ?string $id = null;
    public ?string $timeUnit = null;
    public ?string $timeUnitNumbers = null;
    
    // Visa installment specific fields
    public ?string $time_unit = null;
    public ?int $max_time_unit_number = null;
    public ?string $max_amount = null;
}