<?php
namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class SAFReportType extends Enum
{

    const APPROVED = "APPROVED SAF SUMMARY";

    const PENDING = "PENDING SAF SUMMARY";

    const DECLINED = "DECLINED SAF SUMMARY";

    const OFFLINE_APPROVED = "OFFLINE APPROVED SAF SUMMARY";

    const PARTIALLY_APPROVED = "PARTIALLY APPROVED  SAF SUMMARY";

    const APPROVED_VOID = "APPROVED SAF VOID SUMMARY";

    const PENDING_VOID = "PENDING SAF VOID SUMMARY";

    const DECLINED_VOID = "DECLINED SAF VOID SUMMARY";
}
