<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\Risk;

/**
 * Fraud Management DecisionManager data
 */
class DecisionManager
{
    /**
     * @var string
     */
    public $billToHostName;

    /**
     * @var bool
     */
    public $billToHttpBrowserCookiesAccepted;

    /**
     * @var string
     */
    public $billToHttpBrowserEmail;

    /**
     * @var string
     */
    public $billToHttpBrowserType;

    /**
     * @var string
     */
    public $billToIpNetworkAddress;

    /**
     * @var string
     */
    public $businessRulessCoreThresHold;

    /**
     * @var string
     */
    public $billToPersonalId;

    /**
     * @var string
     */
    public $decisionManagerProfile;

    /**
     * @var string
     */
    public $invoiceHeaderTenderType;

    /**
     * @var Risk
     */
    public $itemHostHedge;

    /**
     * @var Risk
     */
    public $itemNonsensicalHedge;

    /**
     * @var Risk
     */
    public $itemObscenitiesHedge;

    /**
     * @var Risk
     */
    public $itemPhoneHedge;

    /**
     * @var Risk
     */
    public $itemTimeHedge;

    /**
     * @var Risk
     */
    public $itemVelocityHedge;

    /**
     * @var bool
     */
    public $invoiceHeaderIsGift;

    /**
     * @var bool
     */
    public $invoiceHeaderReturnsAccepted;
}
