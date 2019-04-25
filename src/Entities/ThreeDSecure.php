<?php

namespace GlobalPayments\Api\Entities;

class ThreeDSecure
{
    /**
     * The algorithm used
     *
     * @var int
     */
    public $algorithm;

    /**
     * @var float
     */
    private $amount;

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return void
     */
    public function setAmount($value)
    {
        $this->amount = $value;
        $this->getMerchantData()->add('amount', (string)$this->amount, false);
    }

    /**
     * Consumer authentication (3DSecure) verification value
     *
     * @var string
     */
    public $cavv;

    /**
     * @var string
     */
    private $currency;

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function setCurrency($value)
    {
        $this->currency = $value;
        $this->merchantData->add('currency', $this->currency, false);
    }

    /**
     * Consumer authentication (3DSecure) electronic commerce indicator
     *
     * @var int
     */
    public $eci;

    /**
     * The enrollment status
     *
     * @var string
     */
    public $enrolled;

    /**
     * The URL of the Issuing Bank's ACS
     *
     * @var string
     */
    public $issuerAcsUrl;

    /**
     * A KVP collection of merchant supplied data
     *
     * @var MerchantDataCollection
     */
    private $merchantData;

    /**
     * @return MerchantDataCollection
     */
    public function getMerchantData()
    {
        if (empty($this->merchantData)) {
            $this->merchantData = new MerchantDataCollection();
        }
        return $this->merchantData;
    }

    /**
     * @return void
     */
    public function setMerchantData($merchantData)
    {
        if (!empty($this->merchantData)) {
            $merchantData->mergeHidden($this->merchantData);
        }

        $this->merchantData = $merchantData;
        if ($this->merchantData->hasKey('amount')) {
            $this->amount = $this->merchantData->getValue('amount');
        }
        if ($this->merchantData->hasKey('currency')) {
            $this->currency = $this->merchantData->getValue('currency');
        }
        if ($this->merchantData->hasKey('orderId')) {
            $this->orderId = $this->merchantData->getValue('orderId');
        }
    }

    /**
     * The order ID used for the initial transaction
     *
     * @var string
     */
    private $orderId;

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return void
     */
    public function setOrderId($value)
    {
        $this->orderId = $value;
        $this->merchantData->add('orderId', $this->orderId, false);
    }

    /**
     * The Payer Authentication Request returned by the Enrollment Server.
     * Must be sent to the Issuing Bank's ACS (Access Control Server) URL.
     *
     * @var string
     */
    public $payerAuthenticationRequest;

    /**
     * Consumer authentication (3DSecure) source
     *
     * @var string
     */
    public $paymentDataSource;

    /**
     * Consumer authentication (3DSecure) type.
     * Default value is "3DSecure"
     *
     * @var string
     */
    public $paymentDataType;

    /**
     * @var string
     */
    public $status;

    /**
     * Consumer authentication (3DSecure) transaction ID
     *
     * @var string
     */
    public $xid;

    public function __construct()
    {
        $this->paymentDataType = '3DSecure';
        if(empty($this->merchantData)){
            $this->merchantData = new MerchantDataCollection();
        }
    }
}
