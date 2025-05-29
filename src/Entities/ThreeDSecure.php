<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\ExemptionReason;
use GlobalPayments\Api\PaymentMethods\Interfaces\ISecureCheck;

class ThreeDSecure implements ISecureCheck
{
    /**
     * @var string
     */
    public $acsTransactionId;

    /**
     * @var string
     */
    public $acsEndVersion;

    /**
     * @var string
     */
    public $acsStartVersion;

    /**
     * @var array
     */
    public $acsInfoIndicator;

    /** @var string */
    public $acsInterface;


    /** @var string */
    public $acsUiTemplate;

    /**
     * The algorithm used
     *
     * @var int
     */
    public $algorithm;

    /**
     * @var string
     */
    public $authenticationSource;

    /**
     * @var string
     */
    public $authenticationType;

    /**
     * @var string
     */
    public $authenticationValue;

    /**
     * @var string
     */
    public $cardHolderResponseInfo;

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
        $this->getMerchantData()->add('amount', $this->amount, false);
    }

    /**
     * Consumer authentication (3DSecure) verification value
     *
     * @var string
     */
    public $cavv;

    /**
     * @var bool
     */
    public $challengeMandated;

    /**
     * string
     */
    public $challengeReturnUrl;

    /**
     * @var MessageExtension[]
     */
    public $messageExtension;

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
     * @var string
     */
    public $decoupledResponseIndicator;

    /**
     * @var string
     */
    public $directoryServerTransactionId;

    /**
     * @var string
     */
    public $directoryServerEndVersion;

    /**
     * @var string
     */
    public $directoryServerStartVersion;

    /**
     * Consumer authentication (3DSecure) electronic commerce indicator
     *
     * @var int
     */
    public $eci;

    /**
     * The authentication 3DSecure status
     *
     * @var string
     */
    public $threeDSecure_status; 

    /**
     * The enrollment status
     *
     * @var string
     */
    public $enrolled;

    /**
     * The exempt status
     *
     * @var string
     */
    public $exemptStatus;

    /**
     * The exempt reason
     *
     * @var ExemptionReason
     */
    public $exemptReason;

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
        if ($this->merchantData->hasKey('version')) {
            $this->version = $this->merchantData->getValue('version');
        }
    }

    /**
     * @var string
     */
    public $messageCategory;

    /**
     * @var string
     */
    public $messageVersion;

    /**
     * @var string
     */
    public $messageType;

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
    public $sdkInterface;

    /**
     * @var enum
     */
    public $sdkUiType;

    /**
     * @var string
     */
    public $secureCode;

    /**
     * @var string
     */
    public $serverTransactionId;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $statusReason;

    /**
     * @var enum
     */
    public $ucafIndicator;

    /** @var Secure3dVersion */
    private $version;

    /** @return Secure3dVersion */
    public function getVersion()
    {
        return $this->version;
    }

    /** @return void */
    public function setVersion($version)
    {
        $this->version = $version;
        $this->merchantData->add('version', $version, false);
    }

    /**
     * @var string
     */
    public $whitelistStatus;

    /**
     * Consumer authentication (3DSecure) transaction ID
     *
     * @var string
     */
    public $xid;

    /**
     * @var string
     */
    public $sessionDataFieldName;

    /**
     * @var string
     */
    public $liabilityShift;

    /**
     * Reference Number assigned by the ACS.
     *
     * @var string
     */
    public $acsReferenceNumber;

    /**
     * The reference created by the 3DSecure provider to identify the specific authentication attempt.
     *
     * @var string
     */
    public $providerServerTransRef;

    public function __construct()
    {
        $this->paymentDataType = '3DSecure';
        if (empty($this->merchantData)) {
            $this->merchantData = new MerchantDataCollection();
        }
    }

    /**
     * @return void
     */
    public function merge(ThreeDSecure $secureEcom)
    {
        if (!empty($secureEcom)) {
            $this->acsTransactionId = $this->mergeValue($this->acsTransactionId, $secureEcom->acsTransactionId);
            $this->acsEndVersion = $this->mergeValue($this->acsEndVersion, $secureEcom->acsEndVersion);
            $this->acsStartVersion = $this->mergeValue($this->acsStartVersion, $secureEcom->acsStartVersion);
            $this->acsInterface = $this->mergeValue($this->acsInterface, $secureEcom->acsInterface);
            $this->acsUiTemplate = $this->mergeValue($this->acsUiTemplate, $secureEcom->acsUiTemplate);
            $this->algorithm = $this->mergeValue($this->algorithm, $secureEcom->algorithm);
            $this->amount = $this->mergeValue($this->amount, $secureEcom->amount);
            $this->authenticationSource = $this->mergeValue($this->authenticationSource, $secureEcom->authenticationSource);
            $this->authenticationType = $this->mergeValue($this->authenticationType, $secureEcom->authenticationType);
            $this->authenticationValue = $this->mergeValue($this->authenticationValue, $secureEcom->authenticationValue);
            $this->cardHolderResponseInfo = $this->mergeValue($this->cardHolderResponseInfo, $secureEcom->cardHolderResponseInfo);
            $this->cavv = $this->mergeValue($this->cavv, $secureEcom->cavv);
            $this->challengeMandated = $this->mergeValue($this->challengeMandated, $secureEcom->challengeMandated);
            $this->messageExtension = $this->mergeValue($this->messageExtension, $secureEcom->messageExtension);
            $this->currency = $this->mergeValue($this->currency, $secureEcom->currency);
            $this->decoupledResponseIndicator = $this->mergeValue($this->decoupledResponseIndicator, $secureEcom->decoupledResponseIndicator);
            $this->directoryServerTransactionId = $this->mergeValue($this->directoryServerTransactionId, $secureEcom->directoryServerTransactionId);
            $this->directoryServerEndVersion = $this->mergeValue($this->directoryServerEndVersion, $secureEcom->directoryServerEndVersion);
            $this->directoryServerStartVersion = $this->mergeValue($this->directoryServerStartVersion, $secureEcom->directoryServerStartVersion);
            $this->eci = $this->mergeValue($this->eci, $secureEcom->eci);
            $this->enrolled = $this->mergeValue($this->enrolled, $secureEcom->enrolled);
            $this->issuerAcsUrl = $this->mergeValue($this->issuerAcsUrl, $secureEcom->issuerAcsUrl);
            $this->messageCategory = $this->mergeValue($this->messageCategory, $secureEcom->messageCategory);
            $this->messageVersion = $this->mergeValue($this->messageVersion, $secureEcom->messageVersion);
            $this->orderId = $this->mergeValue($this->orderId, $secureEcom->orderId);
            $this->payerAuthenticationRequest = $this->mergeValue($this->payerAuthenticationRequest, $secureEcom->payerAuthenticationRequest);
            $this->paymentDataSource = $this->mergeValue($this->paymentDataSource, $secureEcom->paymentDataSource);
            $this->paymentDataType = $this->mergeValue($this->paymentDataType, $secureEcom->paymentDataType);
            $this->sdkInterface = $this->mergeValue($this->sdkInterface, $secureEcom->sdkInterface);
            $this->sdkUiType = $this->mergeValue($this->sdkUiType, $secureEcom->sdkUiType);
            $this->serverTransactionId = $this->mergeValue($this->serverTransactionId, $secureEcom->serverTransactionId);
            $this->status = $this->mergeValue($this->status, $secureEcom->status);
            $this->statusReason = $this->mergeValue($this->statusReason, $secureEcom->statusReason);
            $this->version = $this->mergeValue($this->version, $secureEcom->version);
            $this->whitelistStatus = $this->mergeValue($this->whitelistStatus, $secureEcom->whitelistStatus);
            $this->xid = $this->mergeValue($this->xid, $secureEcom->xid);
            $this->messageType = $this->mergeValue($this->messageType, $secureEcom->messageType);
            $this->sessionDataFieldName = $this->mergeValue($this->sessionDataFieldName, $secureEcom->sessionDataFieldName);
            $this->challengeReturnUrl = $this->mergeValue($this->challengeReturnUrl, $secureEcom->challengeReturnUrl);
            $this->exemptStatus = $this->mergeValue($this->exemptStatus, $secureEcom->exemptStatus);
            $this->exemptReason = $this->mergeValue($this->exemptReason, $secureEcom->exemptReason);
            $this->liabilityShift = $this->mergeValue($this->liabilityShift, $secureEcom->liabilityShift);
            $this->acsReferenceNumber = $this->mergeValue($this->acsReferenceNumber, $secureEcom->acsReferenceNumber);
            $this->providerServerTransRef = $this->mergeValue($this->providerServerTransRef, $secureEcom->providerServerTransRef);
        }
    }

    /**
     * @return mixed
     */
    public function mergeValue($currentValue, $mergeValue)
    {
        if ($mergeValue == null) {
            return $currentValue;
        }
        return $mergeValue;
    }
}
