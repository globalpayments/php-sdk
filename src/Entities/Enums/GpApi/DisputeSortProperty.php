<?php


namespace GlobalPayments\Api\Entities\Enums\GpApi;


use GlobalPayments\Api\Entities\Enum;

class DisputeSortProperty extends Enum
{
    const ID = 'id';
    const ARN = 'arn';
    const BRAND = 'brand';
    const STATUS = 'status';
    const STAGE = 'stage';
    const FROM_STAGE_TIME_CREATED = 'from_stage_time_created';
    const TO_STAGE_TIME_CREATED = 'to_stage_time_created';
    const ADJUSTMENT_FUNDING = 'adjustment_funding';
    const FROM_ADJUSTMENT_TIME_CREATED = 'from_adjustment_time_created';
    const TO_ADJUSTMENT_TIME_CREATED = 'to_adjustment_time_created';
}