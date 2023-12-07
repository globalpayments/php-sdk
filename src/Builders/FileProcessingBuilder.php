<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\FileProcessingActionType;
use GlobalPayments\Api\Entities\Exceptions\NotImplementedException;
use GlobalPayments\Api\Entities\FileProcessor;
use GlobalPayments\Api\ServicesContainer;

class FileProcessingBuilder extends BaseBuilder
{
    /** @var FileProcessingActionType  */
    public string $actionType;

    public string $resourceId;

    public string $transactionType;

    public function __construct($transactionType)
    {
        parent::__construct();

        $this->actionType = $transactionType;
        $this->transactionType = $transactionType;
    }

    public function execute(string $configName = 'default') : FileProcessor
    {
        $this->validate();
        $client = ServicesContainer::instance()->getFileProcessingClient($configName);

        return $client->processFileUpload($this);
    }

    public function withResourceId(string $resourceId) : FileProcessingBuilder
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    protected function setupValidations()
    {
        $this->validations->of(
            FileProcessingActionType::GET_DETAILS
        )
            ->check('resourceId')->isNotNull();
    }
}