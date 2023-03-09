<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Entities\CustomerDocument;
use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\BankPaymentType;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\DigitalWalletTokenFormat;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\ManualEntryMethod;
use GlobalPayments\Api\Entities\Enums\CaptureMode;
use GlobalPayments\Api\Entities\Enums\PayLinkStatus;
use GlobalPayments\Api\Entities\Enums\PaymentEntryMode;
use GlobalPayments\Api\Entities\Enums\PaymentProvider;
use GlobalPayments\Api\Entities\Enums\PaymentType;
use GlobalPayments\Api\Entities\Enums\PhoneNumberType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\GpApi\DTO\Card;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\PayLinkData;
use GlobalPayments\Api\Entities\PhoneNumber;
use GlobalPayments\Api\Entities\Product;
use GlobalPayments\Api\Gateways\OpenBankingProvider;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\PaymentMethods\BNPL;
use GlobalPayments\Api\PaymentMethods\Credit;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\CardUtils;
use GlobalPayments\Api\Utils\EmvUtils;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\StringUtils;

class GpApiAuthorizationRequestBuilder implements IRequestBuilder
{
    /** @var AuthorizationBuilder */
    private $builder;

    /***
     * @param AuthorizationBuilder $builder
     *
     * @return bool
     */
    public static function canProcess($builder)
    {
        if ($builder instanceof AuthorizationBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param GpApiConfig $config
     * @return GpApiRequest|string
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $this->builder = $builder;
        $requestData = null;
        /** @var AuthorizationBuilder $builder */
        switch ($builder->transactionType) {
            case TransactionType::SALE:
            case TransactionType::REFUND:
            case TransactionType::AUTH:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT;
                $verb = 'POST';
                $requestData =  $this->createFromAuthorizationBuilder($builder, $config);
                break;
            case TransactionType::VERIFY:
                if ($builder->requestMultiUseToken && empty($builder->paymentMethod->token)) {
                    $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT;
                    $verb = 'POST';
                    $requestData = [];
                    $requestData['account_name'] = $config->accessTokenInfo->tokenizationAccountName;
                    $requestData['account_id'] = $config->accessTokenInfo->tokenizationAccountID;
                    $requestData['name'] = $builder->description ? $builder->description : "";
                    $requestData['reference'] = $builder->clientTransactionId ?
                        $builder->clientTransactionId : GenerationUtils::generateOrderId();
                    $requestData['usage_mode'] = $builder->paymentMethodUsageMode;
                    $requestData['fingerprint_mode'] =
                        (!empty($builder->customerData) & !empty($builder->customerData->deviceFingerPrint) ?
                            $builder->customerData->deviceFingerPrint : null);
                    $card = new Card();
                    $builderCard = $builder->paymentMethod;
                    $card->number = $builderCard->number;
                    $card->expiry_month = (string) $builderCard->expMonth;
                    $card->expiry_year = !empty($builderCard->expYear) ?
                        substr(
                            str_pad((string) $builderCard->expYear, 4, '0', STR_PAD_LEFT),
                            2,
                            2
                        ) : null;
                    $card->cvv = $builderCard->cvn;
                    $requestData['card'] = $card;
                } else {
                    $endpoint = GpApiRequest::VERIFICATIONS_ENDPOINT;
                    $verb = 'POST';
                    $requestData = $this->generateVerificationRequest($builder, $config);
                }
                break;
            case TransactionType::DCC_RATE_LOOKUP:
                $endpoint = GpApiRequest::DCC_ENDPOINT;
                $verb = 'POST';
                $requestData['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
                $requestData['account_id'] = $config->accessTokenInfo->transactionProcessingAccountID;
                $requestData['channel'] = $config->channel;
                $requestData['amount'] = StringUtils::toNumeric($builder->amount);
                $requestData['currency'] = $builder->currency;
                $requestData['country'] = $config->country;
                $requestData['reference'] = !empty($builder->clientTransactionId) ?
                    $builder->clientTransactionId : GenerationUtils::getGuid();
                $requestData['payment_method'] = $this->createPaymentMethodParam($builder, $config);
                break;
            case TransactionType::CREATE:
                if ($builder->payLinkData instanceof PayLinkData) {
                    /** @var PayLinkData $payLink */
                    $payLink = $builder->payLinkData;
                    $endpoint = GpApiRequest::PAYLINK_ENDPOINT;
                    $verb = 'POST';
                    $requestData['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
                    $requestData['account_id'] = $config->accessTokenInfo->transactionProcessingAccountID;
                    $requestData['type'] = $payLink->type;
                    $requestData['usage_mode'] = $payLink->usageMode;
                    $requestData['usage_limit'] = (string) $payLink->usageLimit;
                    $requestData['reference'] = $builder->clientTransactionId;
                    $requestData['name'] = $payLink->name;
                    $requestData['description'] = $builder->description;
                    $requestData['shippable'] = $payLink->isShippable == true ? 'YES' : 'NO';
                    $requestData['shipping_amount'] = StringUtils::toNumeric($payLink->shippingAmount);
                    $requestData['expiration_date'] = !empty($payLink->expirationDate) ?
                        (new \DateTime($payLink->expirationDate))->format('Y-m-d\TH:i:s\Z') : null;
                    //@TODO - remove status when GP-API will fix the issue (status shouldn't be sent in request)
                    $requestData['status'] = PayLinkStatus::ACTIVE;
                    $requestData['images'] = $payLink->images;
                    $requestData['transactions'] = [
                        'amount' => StringUtils::toNumeric($builder->amount),
                        'channel' => $config->channel,
                        'currency' => $builder->currency,
                        'country' => $config->country,
                        'allowed_payment_methods' => $payLink->allowedPaymentMethods
                    ];
                    $requestData['notifications'] = [
                        'return_url' => $payLink->returnUrl,
                        'status_url' => $payLink->statusUpdateUrl,
                        'cancel_url' => $payLink->cancelUrl
                    ];
                }
                break;
            default:
                return '';
        }

        return new GpApiRequest($endpoint, $verb, $requestData);
    }

    private function generateVerificationRequest(AuthorizationBuilder $builder, GpApiConfig $config)
    {
        $requestBody = [];
        $requestBody['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
        $requestBody['account_id'] = $config->accessTokenInfo->transactionProcessingAccountID;
        $requestBody['channel'] = $config->channel;
        $requestBody['reference'] = !empty($builder->clientTransactionId) ?
            $builder->clientTransactionId : GenerationUtils::getGuid();
        $requestBody['currency'] = $builder->currency;
        $requestBody['country'] = $config->country;
        $requestBody['payment_method'] = $this->createPaymentMethodParam($builder, $config);

        return $requestBody;
    }

    private function createFromAuthorizationBuilder($builder, GpApiConfig $config)
    {
        /** @var AuthorizationBuilder $builder */
        $captureMode = $this->getCaptureMode($builder);

        $requestBody = [];
        $requestBody['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
        $requestBody['account_id'] = $config->accessTokenInfo->transactionProcessingAccountID;
        $requestBody['channel'] = $config->channel;
        $requestBody['country'] = $config->country;
        $requestBody['type'] = ($builder->transactionType == TransactionType::REFUND ?
            PaymentType::REFUND : PaymentType::SALE);
        $requestBody['capture_mode'] = !empty($captureMode) ? $captureMode : CaptureMode::AUTO;
        $requestBody['authorization_mode'] = !empty($builder->allowPartialAuth) ? 'PARTIAL' : null;
        $requestBody['amount'] = StringUtils::toNumeric($builder->amount);
        $requestBody['currency'] = $builder->currency;
        $requestBody['reference'] = !empty($builder->clientTransactionId) ?
            $builder->clientTransactionId : GenerationUtils::getGuid();
        if (
            $builder->paymentMethod instanceof Credit &&
            $builder->paymentMethod->mobileType == EncyptedMobileType::CLICK_TO_PAY
        ) {
            $requestBody['masked'] = $builder->maskedDataResponse === true ? 'YES' : 'NO';
        }
        $requestBody['description'] = $builder->description;
        $requestBody['order'] = ['reference' => $builder->orderId];
        $requestBody['gratuity_amount'] = StringUtils::toNumeric($builder->gratuity);
        $requestBody['surcharge_amount'] = StringUtils::toNumeric($builder->surchargeAmount);
        $requestBody['convenience_amount'] = StringUtils::toNumeric($builder->convenienceAmount);
        $requestBody['cashback_amount'] = StringUtils::toNumeric($builder->cashBackAmount);
        $requestBody['ip_address'] = $builder->customerIpAddress;
        $requestBody['payment_method'] = $this->createPaymentMethodParam($builder, $config);
        $requestBody['risk_assessment'] = !empty($builder->fraudFilter) ? [$this->mapFraudManagement()] : null;
        if (!empty($builder->paymentLinkId)) {
            $requestBody['link'] = [
                'id' => $builder->paymentLinkId
            ];
        }

        if (
            $builder->paymentMethod instanceof ECheck ||
            $builder->paymentMethod instanceof AlternativePaymentMethod ||
            $builder->paymentMethod instanceof BNPL
        ) {
            $requestBody['payer'] = $this->setPayerInformation($builder);
        }
        if ($builder->paymentMethod instanceof AlternativePaymentMethod || $builder->paymentMethod instanceof BNPL) {
            $this->setOrderInformation($builder, $requestBody);
        }
        if ($builder->paymentMethod instanceof AlternativePaymentMethod ||
            $builder->paymentMethod instanceof BNPL ||
            $builder->paymentMethod instanceof BankPayment
        ) {
            $this->setNotificationUrls($requestBody);
        }
        if (!empty($builder->storedCredential)) {
            $initiator = EnumMapping::mapStoredCredentialInitiator(GatewayProvider::GP_API, $builder->storedCredential->initiator);
            $requestBody['initiator'] = !empty($initiator) ? $initiator : null;
            $requestBody['stored_credential'] = [
                'model' => strtoupper($builder->storedCredential->type),
                'reason' => strtoupper($builder->storedCredential->reason),
                'sequence' => strtoupper($builder->storedCredential->sequence)
            ];
        }

        if (!empty($builder->dccRateData)) {
            $requestBody['currency_conversion'] = [
                'id' => $builder->dccRateData->dccId
            ];
        }

        return $requestBody;
    }

    /**
     * Sets the information related to the payer
     *
     * @param AuthorizationBuilder $builder
     * @return mixed
     */
    private function setPayerInformation($builder)
    {
        $payer['reference'] = !empty($builder->customerId) ?
            $builder->customerId : (!empty($builder->customerData) ? $builder->customerData->id : null);
        switch (get_class($builder->paymentMethod)) {
            case AlternativePaymentMethod::class:
                $payer['home_phone'] = [
                    'country_code' => !empty($builder->homePhone) ?
                        StringUtils::validateToNumber($builder->homePhone->countryCode) : null,
                    'subscriber_number' => !empty($builder->homePhone) ?
                        StringUtils::validateToNumber($builder->homePhone->number) : null
                ];
                $payer['work_phone'] = [
                    'country_code' => !empty($builder->workPhone) ?
                        StringUtils::validateToNumber($builder->workPhone->countryCode) : null,
                    'subscriber_number' => !empty($builder->workPhone) ?
                        StringUtils::validateToNumber($builder->workPhone->number) : null,
                ];
                break;
            case ECheck::class:
                $payer['billing_address'] = [
                    'line_1' => $builder->billingAddress->streetAddress1,
                    'line_2' => $builder->billingAddress->streetAddress2,
                    'city' => $builder->billingAddress->city,
                    'postal_code' => $builder->billingAddress->postalCode,
                    'state' => $builder->billingAddress->state,
                    'country' => $builder->billingAddress->countryCode
                ];
                if (!empty($builder->customerData)) {
                    $payer['name'] = $builder->customerData->firstName . ' ' . $builder->customerData->lastName;
                    $payer['date_of_birth'] = $builder->customerData->dateOfBirth;
                }
                list($phoneNumber, $phoneCountryCode) = $this->getPhoneNumber($builder, PhoneNumberType::HOME);
                $payer['landline_phone'] = $phoneCountryCode . $phoneNumber;;
                list($phoneNumber, $phoneCountryCode) = $this->getPhoneNumber($builder, PhoneNumberType::MOBILE);
                $payer['mobile_phone'] = $phoneCountryCode . $phoneNumber;
                break;
            case BNPL::class:
                if (empty($builder->customerData)) {
                    break;
                }
                $payer['email'] = $builder->customerData->email;
                $payer['date_of_birth'] = $builder->customerData->dateOfBirth;
                if (!empty($builder->billingAddress)) {
                    $payer['billing_address'] = [
                        'line_1' => $builder->billingAddress->streetAddress1,
                        'line_2' => $builder->billingAddress->streetAddress2,
                        'city' => $builder->billingAddress->city,
                        'postal_code' => $builder->billingAddress->postalCode,
                        'state' => $builder->billingAddress->state,
                        'country' => $builder->billingAddress->countryCode,
                        'first_name' => $builder->customerData->firstName ?? '',
                        'last_name' => $builder->customerData->lastName ?? ''
                    ];
                }
                if (isset($builder->customerData->phone)) {
                    $payer['contact_phone'] = [
                        'country_code' => !empty($builder->customerData->phone->countryCode) ?
                            StringUtils::validateToNumber($builder->customerData->phone->countryCode) : null,
                        'subscriber_number' => !empty($builder->customerData->phone->number) ?
                            StringUtils::validateToNumber($builder->customerData->phone->number) : null,
                    ];
                }
                if (!empty($builder->customerData->documents)) {
                    /** @var CustomerDocument $document */
                    foreach ($builder->customerData->documents as $document) {
                        $documents[] = [
                            'type' => $document->type,
                            'reference' => $document->reference,
                            'issuer' => $document->issuer
                        ];
                    }
                    $payer['documents'] = $documents ?? null;
                }
                break;
            default:
                break;
        }

        return $payer;
    }

    /**
     * You can have the phone number set on customerData or directly to the builder
     *
     * @param AuthorizationBuilder $builder
     * @param string $type
     *
     * @return array
     */
    private function getPhoneNumber($builder, $type)
    {
        $phoneKey = strtolower($type) . 'Phone';
        $phoneCountryCode = $phoneNumber = '';
        if (
            isset($builder->customerData) &&
            isset($builder->customerData->{$phoneKey}) &&
            $builder->customerData->{$phoneKey} instanceof PhoneNumber
        ) {
            $phoneCountryCode = $builder->customerData->{$phoneKey}->countryCode;
            $phoneNumber = $builder->customerData->{$phoneKey}->number;
        }
        if (empty($phoneNumber) && isset($builder->{$phoneKey}) && $builder->{$phoneKey} instanceof PhoneNumber) {
            $phoneCountryCode = $builder->{$phoneKey}->countryCode;
            $phoneNumber = $builder->{$phoneKey}->number;
        }

        return [StringUtils::validateToNumber($phoneNumber), StringUtils::validateToNumber($phoneCountryCode)];
    }

    /**
     * @param AuthorizationBuilder $builder
     * @param GpApiConfig $config
     *
     * @return PaymentMethod
     */
    private function createPaymentMethodParam($builder, $config)
    {
        /** @var CreditCardData|CreditTrackData|DebitTrackData|ECheck|AlternativePaymentMethod|BNPL|BankPayment $paymentMethodContainer */
        $paymentMethodContainer = $builder->paymentMethod;
        $paymentMethod = new PaymentMethod();
        $paymentMethod->entry_mode = $this->getEntryMode($builder, $config->channel);
        $paymentMethod->name = $paymentMethodContainer instanceof AlternativePaymentMethod ?
            $paymentMethodContainer->accountHolderName : (!empty($paymentMethodContainer->cardHolderName) ?
                $paymentMethodContainer->cardHolderName : null);
        $paymentMethod->narrative = !empty($builder->dynamicDescriptor) ? $builder->dynamicDescriptor : null;
        switch (get_class($paymentMethodContainer)) {
            case CreditCardData::class;
                $paymentMethod->fingerprint_mode =
                    (!empty($builder->customerData) & !empty($builder->customerData->deviceFingerPrint) ?
                        $builder->customerData->deviceFingerPrint : null);
                $secureEcom = $paymentMethodContainer->threeDSecure;
                if (!empty($secureEcom)) {
                    $paymentMethod->authentication =
                        [
                            'id' => $secureEcom->serverTransactionId,
                            'three_ds' => [
                                'exempt_status' => $secureEcom->exemptStatus
                            ]
                        ];
                }
                break;
            case ECheck::class:
                $paymentMethod->name = $paymentMethodContainer->checkHolderName;
                $paymentMethod->bank_transfer = [
                    'account_number' => $paymentMethodContainer->accountNumber,
                    'account_type' => EnumMapping::mapAccountType(
                        GatewayProvider::GP_API,
                        $paymentMethodContainer->accountType
                    ),
                    'check_reference' => $paymentMethodContainer->checkReference,
                    'sec_code' => $paymentMethodContainer->secCode,
                    'narrative' => $paymentMethodContainer->merchantNotes,
                    'bank' => [
                        'code' => $paymentMethodContainer->routingNumber,
                        'name' => $paymentMethodContainer->bankName,
                        'address' =>
                        [
                            'line_1' => $paymentMethodContainer->bankAddress->streetAddress1,
                            'line_2' => $paymentMethodContainer->bankAddress->streetAddress2,
                            'line_3' => $paymentMethodContainer->bankAddress->streetAddress3,
                            'city' => $paymentMethodContainer->bankAddress->city,
                            'postal_code' => $paymentMethodContainer->bankAddress->postalCode,
                            'state' => $paymentMethodContainer->bankAddress->state,
                            'country' => $paymentMethodContainer->bankAddress->countryCode
                        ]
                    ]
                ];

                return $paymentMethod;
            case IEncryptable::class:
                if (!empty($paymentMethodContainer->encryptionData)) {
                    /**
                     * @var EncryptionData $encryptionData
                     */
                    $encryptionData = $paymentMethodContainer->encryptionData;
                    $encryption = ['version' => $encryptionData->version];
                    if (!empty($encryptionData->ktb)) {
                        $method = 'KBT';
                        $info = $encryptionData->ktb;
                    } elseif (!empty($encryptionData->ksn)) {
                        $method = 'KSN';
                        $info = $encryptionData->ksn;
                    }
                    if (!empty($info)) {
                        $encryption->method = $method;
                        $encryption->info = $info;
                        $paymentMethod->encryption = $encryption;
                    }
                }
                break;
            case AlternativePaymentMethod::class:
                $paymentMethod->apm = [
                    'provider' => $paymentMethodContainer->alternativePaymentMethodType,
                    'address_override_mode' => !empty($paymentMethodContainer->addressOverrideMode) ?
                        $paymentMethodContainer->addressOverrideMode : null
                ];
                return $paymentMethod;
            case BankPayment::class:
                $paymentMethod->apm = [
                    'provider' => PaymentProvider::OPEN_BANKING,
                    'countries' => $paymentMethodContainer->countries ?? [],
                ];
                $bankPaymentType = !empty($paymentMethodContainer->bankPaymentType) ?
                    $paymentMethodContainer->bankPaymentType : OpenBankingProvider::getBankPaymentType($builder->currency);
                $paymentMethod->bank_transfer = [
                    'account_number' => $bankPaymentType == BankPaymentType::FASTERPAYMENTS ?
                        $paymentMethodContainer->accountNumber : '',
                    'iban' => $bankPaymentType == BankPaymentType::SEPA ? $paymentMethodContainer->iban : '',
                    'bank' => [
                        'code' => $paymentMethodContainer->sortCode,
                        'name' => $paymentMethodContainer->accountName
                    ],
                    'remittance_reference' => [
                        'type' => $builder->remittanceReferenceType,
                        'value' => $builder->remittanceReferenceValue
                    ]
                ];
                return $paymentMethod;
            case BNPL::class:
                if (!empty($builder->customerData->firstName) && !empty($builder->customerData->lastName)) {
                    $name = $builder->customerData->firstName . ' ' . $builder->customerData->lastName;
                }
                $paymentMethod->name = $name ?? null;
                $paymentMethod->bnpl = [
                    'provider' => $paymentMethodContainer->bnplType
                ];
                return $paymentMethod;
            default:
                break;
        }

        if (!in_array(
            $builder->transactionModifier,
            [TransactionModifier::ENCRYPTED_MOBILE, TransactionModifier::DECRYPTED_MOBILE]
        )) {
            if ($paymentMethodContainer instanceof ITokenizable && !empty($paymentMethodContainer->token)) {
                $paymentMethod->id = $paymentMethodContainer->token;
            }

            if (is_null($paymentMethod->id)) {
                $paymentMethod->card = CardUtils::generateCard($builder, GatewayProvider::GP_API);
            }
        } else {
            /* digital wallet */
            switch ($builder->transactionModifier) {
                case TransactionModifier::ENCRYPTED_MOBILE:
                    $paymentToken = null;
                    switch ($paymentMethodContainer->mobileType){
                        case EncyptedMobileType::CLICK_TO_PAY:
                            $paymentToken = ['data' => $paymentMethodContainer->token];
                            break;
                        default:
                            $paymentToken = !empty($paymentMethodContainer->token) ?
                                json_decode(preg_replace('/(\\\)(\w)/', '${1}${1}${2}', $paymentMethodContainer->token)) : null;
                    }
                    $digitalWallet['payment_token'] = $paymentToken;
                    break;
                case TransactionModifier::DECRYPTED_MOBILE:
                    $digitalWallet['token'] = !empty($paymentMethodContainer->token) ?
                        $paymentMethodContainer->token : null;
                    $digitalWallet['token_format'] = DigitalWalletTokenFormat::CARD_NUMBER;
                    $digitalWallet['expiry_month'] = (string) $paymentMethodContainer->expMonth;
                    $digitalWallet['expiry_year'] = !empty($paymentMethodContainer->expYear) ?
                        substr(
                            str_pad($paymentMethodContainer->expYear, 4, '0', STR_PAD_LEFT),
                            2,
                            2
                        ) : null;
                    $digitalWallet['cryptogram'] = $paymentMethodContainer->cryptogram;
                    $digitalWallet['eci'] = !empty($paymentMethodContainer->eci) ?
                        $paymentMethodContainer->eci : $this->getEciCode($paymentMethodContainer);
                    break;
                default:
                    break;
            }
            $digitalWallet['provider'] = EnumMapping::mapDigitalWalletType(
                GatewayProvider::GP_API,
                $paymentMethodContainer->mobileType
            );
            $paymentMethod->digital_wallet = $digitalWallet;
        }
        if (!empty($builder->cardBrandTransactionId)) {
            if (!$paymentMethod->card instanceof Card) {
                $paymentMethod->card = new Card();
            }
            $paymentMethod->card->brand_reference = $builder->cardBrandTransactionId;
        }
        $paymentMethod->storage_mode = $builder->requestMultiUseToken == true ? 'ON_SUCCESS' : null;

        return $paymentMethod;
    }

    /**
     * @param CreditCardData $paymentMethod
     *
     * @return string|null
     */
    private function getEciCode($paymentMethod)
    {
        if (!$paymentMethod instanceof CreditCardData) {
            return null;
        }
        if (!empty($paymentMethod->eci)) {
            return $paymentMethod->eci;
        }
        $eciCode = null;
        switch (CardUtils::getBaseCardType($paymentMethod->getCardType())) {
            case CardType::VISA:
            case CardType::AMEX:
                $eciCode = '05';
                break;
            case CardType::MASTERCARD:
                $eciCode = '02';
                break;
            default:
                break;
        }

        return $eciCode;
    }

    /**
     * @param AuthorizationBuilder $builder
     * @param string $channel
     * @return string
     */
    private function getEntryMode(AuthorizationBuilder $builder, $channel)
    {
        if ($channel == Channel::CardPresent) {
            if ($builder->paymentMethod instanceof ITrackData) {
                if (!empty($builder->tagData)) {
                    if ($builder->paymentMethod->entryMethod == EntryMethod::PROXIMITY) {
                        return PaymentEntryMode::CONTACTLESS_CHIP;
                    }
                    $emvData = EmvUtils::parseTagData($builder->tagData);
                    if ($emvData->isContactlessMsd()) {
                        return  PaymentEntryMode::CONTACTLESS_SWIPE;
                    }

                    return PaymentEntryMode::CHIP;
                }
                if ($builder->paymentMethod->entryMethod == PaymentEntryMode::SWIPE) {
                    return PaymentEntryMode::SWIPE;
                }
            }
            if ($builder->paymentMethod instanceof ICardData && $builder->paymentMethod->cardPresent) {
                return PaymentEntryMode::MANUAL;
            }

            return PaymentEntryMode::SWIPE;
        } elseif ($channel == Channel::CardNotPresent) {
            if ($builder->paymentMethod instanceof ICardData) {
                if ($builder->paymentMethod->readerPresent === true) {
                    return PaymentEntryMode::ECOM;
                }
                if (
                    $builder->paymentMethod->readerPresent === false &&
                    !is_null($builder->paymentMethod->entryMethod)
                ) {
                    switch ($builder->paymentMethod->entryMethod) {
                        case ManualEntryMethod::PHONE:
                            return PaymentEntryMode::PHONE;
                        case ManualEntryMethod::MOTO:
                            return PaymentEntryMode::MOTO;
                        case ManualEntryMethod::MAIL:
                            return PaymentEntryMode::MAIL;
                        default:
                            break;
                    }
                }
                if (
                    $builder->transactionModifier == TransactionModifier::ENCRYPTED_MOBILE &&
                    $builder->paymentMethod instanceof CreditCardData &&
                    $builder->paymentMethod->hasInAppPaymentData()
                ) {
                    return PaymentEntryMode::IN_APP;
                }
            }

            return PaymentEntryMode::ECOM;
        }

        throw new ApiException("Please configure the channel!");
    }

    private function getCaptureMode(AuthorizationBuilder $builder)
    {
        if ($builder->multiCapture) {
            return CaptureMode::MULTIPLE;
        }
        if ($builder->transactionType == TransactionType::AUTH) {
            return CaptureMode::LATER;
        }

        return CaptureMode::AUTO;
    }

    private function setNotificationUrls(&$requestBody)
    {
        $requestBody['notifications'] = [
            'return_url' => $this->builder->paymentMethod->returnUrl ?? null,
            'status_url' => $this->builder->paymentMethod->statusUpdateUrl ?? null,
            'cancel_url' => $this->builder->paymentMethod->cancelUrl ?? null
        ];
    }

    private function setOrderInformation($builder, &$requestBody)
    {
        $order['description'] = !empty($builder->orderDetails) ?
            $builder->orderDetails->description : null;
        if (!empty($builder->shippingAddress)) {
            $order['shipping_address'] = [
                'line_1' => $builder->shippingAddress->streetAddress1,
                'line_2' => $builder->shippingAddress->streetAddress2,
                'line_3' => $builder->shippingAddress->streetAddress3,
                'city' => $builder->shippingAddress->city,
                'postal_code' => $builder->shippingAddress->postalCode,
                'state' => $builder->shippingAddress->state,
                'country' => $builder->shippingAddress->countryCode
            ];
        }
        list($phoneNumber, $phoneCountryCode) = $this->getPhoneNumber($builder, PhoneNumberType::SHIPPING);
        $order['shipping_phone'] = [
            'country_code' => $phoneCountryCode,
            'subscriber_number' => $phoneNumber
        ];

        switch (get_class($builder->paymentMethod)) {
            case AlternativePaymentMethod::class:
                if (!empty($builder->productData)) {
                    $this->setItemDetailsListForApm($builder, $order);
                }
                break;
            case BNPL::class:
                $order['shipping_method'] = $builder->bnplShippingMethod;
                if (!empty($builder->productData)) {
                    $this->setItemDetailsListForBNPL($builder, $order);
                }
                break;
        }

        if (!empty($orderAmount)) {
            $requestBody['amount'] = $orderAmount;
        }
        if (!empty($requestBody['order'])) {
            $order = array_merge($requestBody['order'], $order);
        }
        $requestBody['order'] = $order;

        return $requestBody;
    }

    private function setItemDetailsListForBNPL($builder, &$order)
    {
        /** @var Product $product */
        foreach ($builder->productData as $product) {
            $qta = !empty($product->quantity) ? (int) $product->quantity : 0;
            $unitAmount = !empty($product->unitPrice) ? StringUtils::toNumeric($product->unitPrice) : 0;
            $taxAmount = !empty($product->taxAmount) ? StringUtils::toNumeric($product->taxAmount) : 0;
            $netUnitAmount = !empty($product->netUnitPrice) ? StringUtils::toNumeric($product->netUnitPrice) : 0;
            $discountAmount = !empty($product->discountAmount) ? StringUtils::toNumeric($product->discountAmount) : 0;
            $items[] = [
                'reference' => !empty($product->productId) ? $product->productId : null,
                'label' => !empty($product->productName) ? $product->productName : null,
                'description' => !empty($product->description) ? $product->description : null,
                'quantity' => (string) $qta,
                'unit_amount' => (string) $unitAmount,
                'total_amount' => (string) ($qta * $unitAmount),
                'tax_amount' => (string)$taxAmount,
                'discount_amount' => (string)$discountAmount,
                'tax_percentage' => !empty($product->taxPercentage) ? StringUtils::toNumeric($product->taxPercentage) : "0",
                'net_unit_amount' => (string) $netUnitAmount,
                'url' => !empty($product->url) ? $product->url : null,
                'image_url' => !empty($product->imageUrl) ? $product->imageUrl : null,
            ];
        }
        if (isset($builder->customerData)) {
            $order['shipping_address']['first_name'] = $builder->customerData->firstName;
            $order['shipping_address']['last_name'] = $builder->customerData->lastName;
        }
        $order['items'] = $items ?? null;
    }

    private function setItemDetailsListForApm($builder, &$order)
    {
        $taxTotalAmount = $itemsAmount = 0;
        foreach ($builder->productData as $product) {
            $qta = !empty($product['quantity']) ? $product['quantity'] : 0;
            $taxAmount = !empty($product['tax_amount']) ? StringUtils::toNumeric($product['tax_amount']) : 0;
            $unitAmount = !empty($product['unit_amount']) ? StringUtils::toNumeric($product['unit_amount']) : 0;
            $items[] = [
                'reference' => !empty($product['reference']) ? $product['reference'] : null,
                'label' => !empty($product['label']) ? $product['label'] : null,
                'description' => !empty($product['description']) ? $product['description'] : null,
                'quantity' => $qta,
                'unit_amount' => $unitAmount,
                'unit_currency' => !empty($product['unit_currency']) ? $product['unit_currency'] : null,
                'tax_amount' => $taxAmount,
                'amount' => $qta * $unitAmount
            ];
            if (!empty($product['tax_amount'])) {
                $taxTotalAmount += $taxAmount;
            }
            if (!empty($product['unit_amount'])) {
                $itemsAmount += $unitAmount;
            }
        }

        $order['tax_amount'] = $taxTotalAmount;
        $order['item_amount'] = $itemsAmount;
        $order['shipping_amount'] = !empty($builder->shippingAmount) ?
            StringUtils::toNumeric($builder->shippingAmount) : 0;
        $order['insurance_offered'] = !empty($builder->orderDetails) && !is_null($builder->orderDetails->hasInsurance) ?
            ($builder->orderDetails->hasInsurance === true ? 'YES' : 'NO') : null;
        $order['shipping_discount'] = !empty($builder->shippingDiscount) ?
            StringUtils::toNumeric($builder->shippingDiscount) : 0;
        $order['insurance_amount'] = !empty($builder->orderDetails->insuranceAmount) ?
            StringUtils::toNumeric($builder->orderDetails->insuranceAmount) : 0;
        $order['handling_amount'] = !empty($builder->orderDetails->handlingAmount) ?
            StringUtils::toNumeric($builder->orderDetails->handlingAmount) : 0;
        $orderAmount = $itemsAmount + $taxTotalAmount + $order['handling_amount'] + $order['insurance_amount'] + $order['shipping_amount'];
        $order['amount'] = $orderAmount;
        $order['currency'] = $builder->currency;
        $order['items'] = $items ?? null;
    }

    public function mapFraudManagement()
    {
        if (!empty($this->builder->fraudRules)) {
            foreach ($this->builder->fraudRules as $fraudRule) {
                $rules[] = [
                    'reference' => $fraudRule->key,
                    'mode' => $fraudRule->mode
                ];
            }
        }

        return [
            'mode' => $this->builder->fraudFilter,
            'rules' => $rules ?? null
        ];
    }
}
