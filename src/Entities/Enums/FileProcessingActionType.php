<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class FileProcessingActionType extends Enum
{
    const CREATE_UPLOAD_URL = 1;
    const GET_DETAILS = 2;
}