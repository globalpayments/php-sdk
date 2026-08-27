<?php

namespace GlobalPayments\Api\Entities\Reporting;

class ActionSummary extends BaseSummary
{
    /** @var string */
    public $id;

    /** @var string */
    public $type;

    /** @var \DateTime */
    public $timeCreated;

    /** @var string */
    public $resource;

    /** @var string */
    public $resourceId;

    /** @var string */
    public $resourceStatus;

    /** @var string */
    public $version;

    /** @var string */
    public $httpResponseCode;

    /** @var string */
    public $responseCode;

    /** @var string */
    public $responseDetailedCode;

    /** @var string */
    public $responseDetailedMessage;

    /** @var string */
    public $appId;

    /** @var string */
    public $appName;

    /** @var string */
    public $accountName;

    /** @var string */
    public $accountId;

    /** @var string */
    public $resourceParentId;

    /** @var string */
    public $resourceRequestUrl;

    /** @var string */
    public $email;

    /** @var string */
    public $sourceLocation;

    /** @var string */
    public $destinationLocation;

    /** @var string */
    public $messageReceived;

    /** @var string */
    public $messageSent;

    /** @var string */
    public $metrics;

    /** @var string */
    public $totalTimeMilliseconds;

    /** @var string */
    public $totalTimeDownstreamMilliseconds;

    /** @var \GlobalPayments\Api\Entities\Action */
    public $action;

    /** Populated from message_received (request payload from GET /actions/{id}). */
    public ?string $rawRequest = null;
    
    /** Populated from message_sent (response payload from GET /actions/{id}). */
    public ?string $rawResponse = null;
}