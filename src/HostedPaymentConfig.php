<?php

namespace GlobalPayments\Api;

use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\Enums\HppVersion;

class HostedPaymentConfig
{

    /** @var Boolean */
    public $cardStorageEnabled;

    /** @var Boolean */
    public $dynamicCurrencyConversionEnabled;

    /** @var Boolean */
    public $displaySavedCards;
    public $fraudFilterMode = FraudFilterMode::NONE;

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
    public $version;

    public function isCardStorageEnabled()
    {
        return $cardStorageEnabled;
    }

    public function setCardStorageEnabled($cardStorageEnabled)
    {
        $this->cardStorageEnabled = $cardStorageEnabled;
    }

    public function isDynamicCurrencyConversionEnabled()
    {
        return $dynamicCurrencyConversionEnabled;
    }

    public function setDynamicCurrencyConversionEnabled($directCurrencyConversionEnabled)
    {
        $this->dynamicCurrencyConversionEnabled = $directCurrencyConversionEnabled;
    }

    public function isDisplaySavedCards()
    {
        return $displaySavedCards;
    }

    public function setDisplaySavedCards($displaySavedCards)
    {
        $this->displaySavedCards = $displaySavedCards;
    }

    public function getFraudFilterMode()
    {
        return $fraudFilterMode;
    }

    public function setFraudFilterMode($fraudFilterMode)
    {
        $this->fraudFilterMode = $fraudFilterMode;
    }

    public function getLanguage()
    {
        return $language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getPaymentButtonText()
    {
        return $paymentButtonText;
    }

    public function setPaymentButtonText($paymentButtonText)
    {
        $this->paymentButtonText = $paymentButtonText;
    }

    public function getPostDimensions()
    {
        return $postDimensions;
    }

    public function setPostDimensions($postDimensions)
    {
        $this->postDimensions = $postDimensions;
    }

    public function getPostResponse()
    {
        return $postResponse;
    }

    public function setPostResponse($postResponse)
    {
        $this->postResponse = $postResponse;
    }

    public function getResponseUrl()
    {
        return $responseUrl;
    }

    public function setResponseUrl($responseUrl)
    {
        $this->responseUrl = $responseUrl;
    }

    public function isRequestTransactionStabilityScore()
    {
        return $requestTransactionStabilityScore;
    }

    public function setRequestTransactionStabilityScore($requestTransactionStabilityScore)
    {
        $this->requestTransactionStabilityScore = $requestTransactionStabilityScore;
    }

    public function getVersion()
    {
        return $version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }
}
