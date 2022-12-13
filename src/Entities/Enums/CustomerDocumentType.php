<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class CustomerDocumentType extends Enum
{
    const NATIONAL = 'NATIONAL';
    const CPF = 'CPF';
    const CPNJ = 'CPNJ';
    const CURP = 'CURP';
    const SSN = 'SSN';
    const DRIVER_LICENSE = 'DRIVER_LICENSE';
    const PASSPORT = 'PASSPORT';
}