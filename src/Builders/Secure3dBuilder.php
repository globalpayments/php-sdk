<?php

namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Enums\DecoupledFlowRequest;
use GlobalPayments\Api\Entities\MobileData;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Gateways\GpApiConnector;
use GlobalPayments\Api\Gateways\GpEcomConnector;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\ISecure3d;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\MerchantDataCollection;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Enums\AgeIndicator;
use GlobalPayments\Api\Entities\Enums\AuthenticationRequestType;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\MessageCategory;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;

class Secure3dBuilder extends SecureBuilder
{
    /** @var string */
    public $applicationId;
    /** @var AuthenticationRequestType */
    public $authenticationRequestType;
    /** @var ChallengeRequestIndicator */
    public $challengeRequestIndicator;
    /** @var string */
    public $customerEmail;
    /** @var DecoupledFlowRequest */
    public $decoupledFlowRequest;
    /** @var integer */
    public $decoupledFlowTimeout;
    /** @var string */
    public $decoupledNotificationUrl;
    /** @var string */
    public $encodedData;
    /** @var string */
    public $ephemeralPublicKey;
    /** @var int */
    public $maximumTimeout;
    /** @var MerchantDataCollection */
    public $merchantData;
    /** @var MessageCategory */
    public $messageCategory;
    /** @var AuthenticationRequestType */
    public $merchantInitiatedRequestType;
    /** @var MessageVersion */
    public $messageVersion;
    /** @var MethodUrlCompletion */
    public $methodUrlCompletion;
    /** @var string */
    public $payerAuthenticationResponse;
    /** @var SdkInterface */
    public $sdkInterface;
    /** @var string */
    public $sdkTransactionId;
    /** @var array<SdkUiType> */
    public $sdkUiTypes;
    /** @var ThreeDSecure */
    public $threeDSecure;

    /** @var TransactionModifier */
    public $transactionModifier = TransactionModifier::NONE;
//    /** @var Secure3dVersion */
//    public $version;
    /** @var string */
    public $whitelistStatus;
    /**
     * @var bool
     */
    public $enableExemptionOptimization;

    /** @var MobileData */
    public $mobileData;

    /**
     * @var StoredCredential
     */
    public $storedCredential;

    public function __construct($transactionType)
    {
        parent::__construct();
        $this->authenticationSource = AuthenticationSource::BROWSER;
        $this->authenticationRequestType = AuthenticationRequestType::PAYMENT_TRANSACTION;
        $this->messageCategory = MessageCategory::PAYMENT_AUTHENTICATION;
        $this->transactionType = $transactionType;
    }

    /** @return string */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /** @return AuthenticationRequestType */
    public function getAuthenticationRequestType()
    {
        return $this->authenticationRequestType;
    }

    /** @return address */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /** @return string */
    public function getChallengeRequestIndicator()
    {
        return $this->challengeRequestIndicator;
    }

    /** @return string */
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    /** @return string */
    public function getEncodedData()
    {
        return $this->encodedData;
    }

    /** @return string */
    public function getEphemeralPublicKey()
    {
        return $this->ephemeralPublicKey;
    }

    /** @return int */
    public function getMaximumTimeout()
    {
        return $this->maximumTimeout;
    }

    /** @return MerchantDataCollection */
    public function getMerchantData()
    {
        return $this->merchantData;
    }

    /** @return MessageCategory */
    public function getMessageCategory()
    {
        return $this->messageCategory;
    }

    /** @return AuthenticationRequestType */
    public function getMerchantInitiatedRequestType()
    {
        return $this->merchantInitiatedRequestType;
    }

    /** @return MessageVersion */
    public function getMessageVersion()
    {
        return $this->messageVersion;
    }

    /** @return MethodUrlCompletion */
    public function getMethodUrlCompletion()
    {
        return $this->methodUrlCompletion;
    }

    /** @return string */
    public function getPayerAuthenticationResponse()
    {
        return $this->payerAuthenticationResponse;
    }

    /** @return SdkInterface */
    public function getSdkInterface()
    {
        return $this->sdkInterface;
    }

    /** @return string */
    public function getSdkTransactionId()
    {
        return $this->sdkTransactionId;
    }

    /** @return array<SdkUiType> */
    public function getSdkUiTypes()
    {
        return $this->sdkUiTypes;
    }

    /**
     * @return string|null
     */
    public function getServerTransactionId()
    {
        if (!empty($this->threeDSecure)) {
            return $this->threeDSecure->serverTransactionId;
        }
        return null;
    }

    /** @return ThreeDSecure */
    public function getThreeDSecure()
    {
        return $this->threeDSecure;
    }

    /** @return TransactionType */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /** @return Secure3dVersion */
    public function getVersion()
    {
        if (!empty($this->threeDSecure)) {
            return $this->threeDSecure->getVersion();
        }
        return null;
    }

    /**
     * @return DecoupledFlowRequest
     */
    public function getDecoupledFlowRequest()
    {
        return $this->decoupledFlowRequest;
    }

    /**
     * @return int
     */
    public function getDecoupledFlowTimeout()
    {
        return $this->decoupledFlowTimeout;
    }

    /**
     * @return string
     */
    public function getDecoupledNotificationUrl()
    {
        return $this->decoupledNotificationUrl;
    }

    /**
     * @return string
     */
    public function getWhitelistStatus()
    {
        return $this->whitelistStatus;
    }

    // HELPER METHOD FOR THE CONNECTOR

    /** @return bool */
    public function hasMobileFields()
    {
        return (
            !empty($this->applicationId) ||
            $this->ephemeralPublicKey != null ||
            $this->maximumTimeout != null ||
            $this->getReferenceNumber() != null ||
            !empty($this->sdkTransactionId) ||
            !empty($this->encodedData) ||
            $this->sdkInterface != null ||
            $this->sdkUiTypes != null
        );
    }

    /** @return bool */
    public function hasPriorAuthenticationData()
    {
        return (
            $this->getPriorAuthenticationMethod() != null ||
            !empty($this->getPriorAuthenticationTransactionId()) ||
            $this->getPriorAuthenticationTimestamp() != null ||
            !empty($this->getPriorAuthenticationData())
        );
    }

    /** @return bool */
    public function hasRecurringAuthData()
    {
        return (
            $this->getMaxNumberOfInstallments() != null ||
            $this->getRecurringAuthorizationFrequency() != null ||
            $this->getRecurringAuthorizationExpiryDate() != null
        );
    }

    /** @return bool */
    public function hasPayerLoginData()
    {
        return (
            !empty($this->getCustomerAuthenticationData()) ||
            $this->getCustomerAuthenticationTimestamp() != null ||
            $this->getCustomerAuthenticationMethod() != null
        );
    }

    /** @return Secure3dBuilder */
    public function withApplicationId($applicationId)
    {
        $this->applicationId = $applicationId;
        return $this;
    }


    /** @return Secure3dBuilder */
    public function withAuthenticationRequestType($value)
    {
        $this->authenticationRequestType = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withChallengeRequestIndicator($challengeRequestIndicator)
    {
        $this->challengeRequestIndicator = $challengeRequestIndicator;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withCustomerEmail($value)
    {
        $this->customerEmail = $value;
        return $this;
    }

    /**
     * @param bool
     * @return Secure3dBuilder
     */
    public function withDecoupledFlowRequest($decoupledFlowRequest)
    {
        $this->decoupledFlowRequest = $decoupledFlowRequest;
        return $this;
    }

    /**
     * @param  $decoupledFlowTimeout
     * @return Secure3dBuilder
     */
    public function withDecoupledFlowTimeout($decoupledFlowTimeout)
    {
        $this->decoupledFlowTimeout = $decoupledFlowTimeout;
        return $this;
    }

    /**
     * @param $decoupledNotificationUrl
     * @return Secure3dBuilder
     */
    public function withDecoupledNotificationUrl($decoupledNotificationUrl)
    {
        $this->decoupledNotificationUrl = $decoupledNotificationUrl;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withEncodedData($encodedData)
    {
        $this->encodedData = $encodedData;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withEphemeralPublicKey($ephemeralPublicKey)
    {
        $this->ephemeralPublicKey = $ephemeralPublicKey;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMaximumTimeout($maximumTimeout)
    {
        $this->maximumTimeout = $maximumTimeout;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMerchantData(MerchantDataCollection $value)
    {
        $this->merchantData = $value;
        if (!empty($this->merchantData)) {
            if (empty($this->threeDSecure)) {
                $this->threeDSecure = new ThreeDSecure();
            }
            $this->threeDSecure->setMerchantData($value);
        }
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMessageCategory($value)
    {
        $this->messageCategory = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMerchantInitiatedRequestType($merchantInitiatedRequestType)
    {
        $this->merchantInitiatedRequestType = $merchantInitiatedRequestType;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMessageVersion($value)
    {
        $this->messageVersion = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMethodUrlCompletion($value)
    {
        $this->methodUrlCompletion = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPayerAuthenticationResponse($value)
    {
        $this->payerAuthenticationResponse = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withPaymentMethod(?IPaymentMethod $value)
    {
        $this->paymentMethod = $value;
        if ($this->paymentMethod instanceof ISecure3d) {
            $secureEcom = $this->paymentMethod->threeDSecure;
            if (!empty($secureEcom)) {
                $this->threeDSecure = $secureEcom;
            }
        }
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withSdkInterface($sdkInterface)
    {
        $this->sdkInterface = $sdkInterface;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withSdkTransactionId($sdkTransactionId)
    {
        $this->sdkTransactionId = $sdkTransactionId;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withSdkUiTypes($sdkUiTypes)
    {
        $this->sdkUiTypes = $sdkUiTypes;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withServerTransactionId($value)
    {
        if (empty($this->threeDSecure)) {
            $this->threeDSecure = new ThreeDSecure();
        }
        $this->threeDSecure->serverTransactionId = $value;
        return $this;
    }

    /** @return Secure3dBuilder */
    public function withThreeDSecure(ThreeDSecure $threeDSecure)
    {
        $this->threeDSecure = $threeDSecure;
        return $this;
    }

    /**
     * @param bool
     * @return Secure3dBuilder
     */
    public function withWhitelistStatus($whitelistStatus)
    {
        if ($whitelistStatus == true) {
            $this->whitelistStatus = "TRUE";
        } else {
            $this->whitelistStatus = "FALSE";
        }
        return $this;
    }

    /**
     * @return Secure3dBuilder
     */
    public function withStoredCredential($storedCredential)
    {
        $this->storedCredential = $storedCredential;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return Secure3dBuilder
     */
    public function withEnableExemptionOptimization($value)
    {
        $this->enableExemptionOptimization = $value;

        return $this;
    }

    /** @return Secure3dBuilder */
    public function withMobileData($value)
    {
        $this->mobileData = $value;
        return $this;
    }

    /**
     * @throws ApiException
     * @return ThreeDSecure */
    public function execute($configName = 'default', $version = Secure3dVersion::ANY)
    {
        // TODO Get validations working
        // parent::execute();

        // setup return object
        $rvalue = $this->threeDSecure;
        if (empty($rvalue)) {
            $rvalue = new ThreeDSecure();
            $rvalue->setVersion($version);
        }

        // working version
        if ($rvalue->getVersion() != null) {
            $version = $rvalue->getVersion();
        }

        // get the provider
        $provider = ServicesContainer::instance()->getSecure3d($configName, $version);
        if (
            $version === Secure3dVersion::ONE &&
            (
                $provider instanceof GpApiConnector ||
                $provider instanceof GpEcomConnector
            )
        ) {
            throw new BuilderException(sprintf("3D Secure %s is no longer supported!", $version));
        }
        if (!empty($provider)) {
            $canDowngrade = false;
            if (
                $provider->getVersion() === Secure3dVersion::TWO &&
                $version === Secure3dVersion::ANY &&
                (!$provider instanceof GpEcomConnector && !$provider instanceof GpApiConnector)
            ) {
                try {
                    $oneProvider = ServicesContainer::instance()->getSecure3d($configName, Secure3dVersion::ONE);
                    $canDowngrade = (bool)(!empty($oneProvider));
                } catch (ConfigurationException $exc) {
                    // NOT CONFIGURED
                }
            }

            // process the request, capture any exceptions which might have been thrown
            $response = null;
            try {
                $response = $provider->processSecure3d($this);

                if (empty($response) && (bool)$canDowngrade) {
                    return $this->execute($configName, Secure3dVersion::ONE);
                }
            } catch (GatewayException $exc) {
                // check for not enrolled
                if ($exc->responseCode != null) {
                    if ($exc->responseCode == '110' && $provider->getVersion() === Secure3dVersion::ONE) {
                        return $rvalue;
                    }
                    if ($provider instanceof GpApiConnector) {
                        throw $exc;
                    }
                } elseif ((bool)$canDowngrade && $this->transactionType === TransactionType::VERIFY_ENROLLED) { // check if we can downgrade
                    return $this->execute($configName, Secure3dVersion::ONE);
                } else { // throw exception
                    throw $exc;
                }
            }

            // check the response
            if (!empty($response)) {
                switch ($this->transactionType) {
                    case TransactionType::VERIFY_ENROLLED:
                        if (!empty($response->threeDSecure)) {
                            $rvalue = $response->threeDSecure;
                            if (in_array($rvalue->enrolled, ['True', 'Y', true], true)) {
                                $rvalue->setAmount($this->getAmount());
                                $rvalue->setCurrency($this->getCurrency());
                                $rvalue->setOrderId($response->orderId);
                                $rvalue->setVersion($provider->getVersion());
                            } elseif ((bool)$canDowngrade) {
                                return $this->execute($configName, Secure3dVersion::ONE);
                            }
                        } elseif ((bool)$canDowngrade) {
                            return $this->execute($configName, Secure3dVersion::ONE);
                        }
                        break;
                    case TransactionType::INITIATE_AUTHENTICATION:
                    case TransactionType::VERIFY_SIGNATURE: {
                        $rvalue->merge($response->threeDSecure);
                    } break;
                }
            }
        }

        return $rvalue;
    }

    /** @return void */
    public function setupValidations()
    {
        $this->validations->of(TransactionType::VERIFY_ENROLLED)
            ->check('paymentMethod')->isNotNull();

        $this->validations->of(TransactionType::VERIFY_ENROLLED)
            ->when('paymentMethod')->isNotNull()
            ->check('paymentMethod')->isInstanceOf(ISecure3d::class);

        $this->validations->of(TransactionType::VERIFY_SIGNATURE)
            ->when('version')->isEqualTo(Secure3dVersion::ONE)
            ->check('threeDSecure')->isNotNull()
            ->when('version')->isEqualTo(Secure3dVersion::ONE)
            ->check('payerAuthenticationResponse')->isNotNull();

        $this->validations->of(TransactionType::VERIFY_SIGNATURE)
            ->when('version')->isEqualTo(Secure3dVersion::TWO)
            ->check('serverTransactionId')->isNotNull();

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->check('threeDSecure')->isNotNull();

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('paymentMethod')->isNotNull()
            ->check('paymentMethod')->isInstanceOf(ISecure3d::class);

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('merchantInitiatedRequestType')->isNotNull()
            ->check('merchantInitiatedRequestType')->isNotEqualTo(AuthenticationRequestType::PAYMENT_TRANSACTION);

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('accountAgeIndicator')->isNotNull()
            ->check('accountAgeIndicator')->isNotEqualTo(AgeIndicator::NO_CHANGE);

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('passwordChangeIndicator')->isNotNull()
            ->check('passwordChangeIndicator')->isNotEqualTo(AgeIndicator::NO_ACCOUNT);

        $this->validations->of(TransactionType::INITIATE_AUTHENTICATION)
            ->when('shippingAddressUsageIndicator')->isNotNull()
            ->check('shippingAddressUsageIndicator')->isNotEqualTo(AgeIndicator::NO_CHANGE)
            ->when('shippingAddressUsageIndicator')->isNotNull()
            ->check('shippingAddressUsageIndicator')->isNotEqualTo(AgeIndicator::NO_ACCOUNT);
    }
}
