<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class BatchService
{
    public static function closeBatch()
    {
        $response = (new ManagementBuilder(TransactionType::BATCH_CLOSE))->execute();
        return new BatchSummary();
    }
}
