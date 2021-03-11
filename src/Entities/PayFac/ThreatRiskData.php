<?php

namespace GlobalPayments\Api\Entities\PayFac;

class ThreatRiskData
{
    /**
     * SourceIp of Merchant, see ProPay Fraud Detection Solutions Manual.
     *
     * @var string
     */
    public $merchantSourceIp;

    /**
     * Threat Metrix Policy, see ProPay Fraud Detection Solutions Manual.
     *
     * @var string
     */
    public $threatMetrixPolicy;
    
    /**
     * SessionId for Threat Metrix, see ProPay Fraud Detection Solutions Manual
     *
     * @var string
     */
    public $threatMetrixSessionId;
}
