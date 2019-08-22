<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class SdkUiType extends Enum
{
    const TEXT = 'TEXT';
    const SINGLE_SELECT = 'SINGLE_SELECT';
    const MULTI_SELECT = 'MULTI_SELECT';
    const OOB = 'OOB';
    const HTML_OTHER = 'HTML_OTHER';
}
