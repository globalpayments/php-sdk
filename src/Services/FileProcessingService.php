<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\FileProcessingBuilder;
use GlobalPayments\Api\Entities\Enums\FileProcessingActionType;
use GlobalPayments\Api\Entities\FileProcessor;

class FileProcessingService
{
    public static function initiate()
    {
        return (new FileProcessingBuilder(FileProcessingActionType::CREATE_UPLOAD_URL))
            ->execute();
    }

    public static function getDetails(string $resourceId) : FileProcessor
    {
        return (new FileProcessingBuilder(FileProcessingActionType::GET_DETAILS))
            ->withResourceId($resourceId)
            ->execute();
    }
}