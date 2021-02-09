<?php


namespace GlobalPayments\Api\Entities\Enums\GpApi;


use GlobalPayments\Api\Entities\Enum;

class DisputeStage extends Enum
{
    const RETRIEVAL = 'RETRIEVAL';
    const CHARGEBACK = 'CHARGEBACK';
    const REVERSAL = 'REVERSAL';
    const SECOND_CHARGEBACK = 'SECOND_CHARGEBACK';
    const PRE_ARBITRATION = 'PRE_ARBITRATION';
    const ARBITRATION = 'ARBITRATION';
    const PRE_COMPLIANCE = 'PRE_COMPLIANCE';
    const COMPLIANCE = 'COMPLIANCE';
    const GOODFAITH = 'GOODFAITH';
}