<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class SummaryType extends Enum
{
    const APPROVED = 'Approved';
    const PARTIALLY_APPROVED = 'PartiallyApproved';
    const VOID_APPROVED = 'VoidApproved';
    const PENDING = 'Pending';
    const VOID_PENDING = 'VoidPending';
    const DECLINED = 'Declined';
    const VOID_DECLINED = 'VoidDeclined';
    const OFFLINE_APPROVED = 'OfflineApproved';
}