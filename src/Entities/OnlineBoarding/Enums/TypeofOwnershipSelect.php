<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding\Enums;

use GlobalPayments\Api\Entities\Enum;

class TypeofOwnershipSelect extends Enum
{
    const SOLEPROPRIETORSHIP = 'Sole Proprietorship';
    const GOVERNMENTMUNICIPALITY = 'Government';
    const PARTNERSHIP = 'Partnership';
    const CORPORATION = 'Corporation';
    const LLC = 'LLC';
    const NONPROFIT = 'Non-Profit';
}
