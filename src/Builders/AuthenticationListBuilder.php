<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\ServicesContainer;

class AuthenticationListBuilder extends BaseBuilder
{
    public int $transactionType;

    public int|string|null $transactionModifier = TransactionModifier::NONE;

    public ?int $page = null;

    public ?int $pageSize = null;

    public array $queryParams = [];

    public function __construct()
    {
        parent::__construct();
        $this->transactionType = TransactionType::SEARCH;
    }

    public function execute($configName = 'default'): PagedResult
    {
        $this->validate();
        $client = ServicesContainer::instance()->getClient($configName);
        if (!method_exists($client, 'processAuthenticationList')) {
            throw new UnsupportedTransactionException(
                'Your current gateway does not support authentication list retrieval.'
            );
        }

        return $client->processAuthenticationList($this);
    }

    public function withPaging(int $page, int $pageSize): self
    {
        $this->page = $page;
        $this->pageSize = $pageSize;

        return $this;
    }

    public function withQueryParam(string $key, mixed $value): self
    {
        $this->queryParams[$key] = $value;

        return $this;
    }

    public function withQueryParams(array $queryParams): self
    {
        $this->queryParams = array_merge($this->queryParams, $queryParams);

        return $this;
    }

    protected function setupValidations()
    {
        $this->validations->of(TransactionType::SEARCH)
            ->check('page')->isNotNull()
            ->check('pageSize')->isNotNull();
    }
}