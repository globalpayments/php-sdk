<?php

namespace GlobalPayments\Api\Entities\TransactionApi;

class TransactionApiData
{
    /**
     * @var string
     */
    public $countryCode;
    public $currency;
    public $ecommerceIndicator;
    public $entryClass;
    public $softDescriptor;
    public $region;
    public $paymentPurposeCode;

    /**
     * @var bool
     */
    public $addressVerificationService = false;
    public $generateReceipt            = false;
    public $partialApproval            = false;
    public $checkVerify                = false;
}
