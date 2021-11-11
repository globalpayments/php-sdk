<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\FraudRuleCollection;

class HostedPaymentConfig
{
    /** @var Boolean */
    public $cardStorageEnabled;

    /** @var Boolean */
    public $dynamicCurrencyConversionEnabled;

    /** @var Boolean */
    public $displaySavedCards;

    /** @var FraudFilterMode  */
    public $fraudFilterMode = FraudFilterMode::NONE;

    /** @var FraudRuleCollection */
    public $fraudFilterRules;

    /** @var String */
    public $language;

    /** @var String */
    public $paymentButtonText;

    /** @var String */
    public $postDimensions;

    /** @var String */
    public $postResponse;

    /** @var String */
    public $responseUrl;

    /** @var Boolean */
    public $requestTransactionStabilityScore;

    /** @var HppVersion */
    public $version;

    /** @var boolean */
    public $directCurrencyConversionEnabled;
}
