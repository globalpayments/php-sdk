<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\Enums\TransactionType;

class BatchService
{
    public static function closeBatch($batchReference = null)
    {
        $response = (new ManagementBuilder(TransactionType::BATCH_CLOSE));
        if (!empty($batchReference)) {
            $response->withBatchReference($batchReference);
        }

        return $response->execute();
    }
}
