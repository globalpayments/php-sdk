<?php


namespace GlobalPayments\Api\Entities\Enums;


use GlobalPayments\Api\Entities\Enum;

class ExemptStatus extends Enum
{
    const LOW_VALUE = "LOW_VALUE";
    const TRANSACTION_RISK_ANALYSIS = "TRANSACTION_RISK_ANALYSIS";
    const TRUSTED_MERCHANT = "TRUSTED_MERCHANT";
    const SECURE_CORPORATE_PAYMENT = "SECURE_CORPORATE_PAYMENT";
    const SCA_DELEGATION = "SCA_DELEGATION";
}