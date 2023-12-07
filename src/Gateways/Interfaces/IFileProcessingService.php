<?php

namespace GlobalPayments\Api\Gateways\Interfaces;

use GlobalPayments\Api\Builders\FileProcessingBuilder;
use GlobalPayments\Api\Entities\FileProcessor;

interface IFileProcessingService
{
    public function processFileUpload(FileProcessingBuilder $builder) : FileProcessor;
}