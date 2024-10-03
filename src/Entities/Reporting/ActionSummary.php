<?php

namespace GlobalPayments\Api\Entities\Reporting;

class ActionSummary extends BaseSummary
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var \DateTime
     */
    public $timeCreated;

    /**
     * @var string
     */
    public $resource;

    /**
     * @var string
     */
    public $resourceId;

    /**
     * @var string
     */
    public $resourceStatus;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $httpResponseCode;

    /**
     * @var string
     */
    public $responseCode;

    /**
     * @var string
     */
    public $appId;

    /**
     * @var string
     */
    public $appName;

    /**
     * @var string
     */
    public $accountName;

    /**
     * @var string
     */
    public $accountId;

    public ?string $rawRequest = null;
    public ?string $rawResponse = null;
}