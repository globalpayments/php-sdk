<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Builders\SecureBuilder;
use GlobalPayments\Api\Entities\Enums\AuthenticationSource;
use GlobalPayments\Api\Entities\Enums\DecoupledFlowRequest;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\ProtectSensitiveData;
use GlobalPayments\Api\Utils\StringUtils;

class GpApiSecureRequestBuilder implements IRequestBuilder
{
    /** @var SecureBuilder */
    private mixed $builder = null;
    private array $maskedValues = [];

    public static function canProcess(?BaseBuilder $builder = null): bool
    {
        if ($builder instanceof SecureBuilder) {
            return true;
        }

        return false;
    }

    public function buildRequest(BaseBuilder $builder, mixed $config): GpApiRequest
    {
        if (!$builder instanceof SecureBuilder) {
            throw new BuilderException("Builder must me an instance of SecureBuilder!");
        }

        $this->builder = $builder;
        $requestData = null;
        switch ($builder->transactionType)
        {
            case TransactionType::VERIFY_ENROLLED:
                $verb = 'POST';
                $endpoint = GpApiRequest::AUTHENTICATIONS_ENDPOINT;
                $requestData = $this->verifyEnrolled($builder, $config);
                break;
            case TransactionType::INITIATE_AUTHENTICATION:
                $verb = 'POST';
                $endpoint = GpApiRequest::AUTHENTICATIONS_ENDPOINT . "/{$builder->getServerTransactionId()}/initiate";
                $requestData = $this->initiateAuthenticationData($builder, $config);
                break;
            case  TransactionType::VERIFY_SIGNATURE:
                $verb = 'POST';
                $endpoint = GpApiRequest::AUTHENTICATIONS_ENDPOINT . "/{$builder->getServerTransactionId()}/result";
                if (!empty($builder->getPayerAuthenticationResponse())) {
                    $requestData['three_ds'] = [
                        'challenge_result_value' => $builder->getPayerAuthenticationResponse()
                    ];
                }
                break;
            case TransactionType::RISK_ASSESS:
                $verb = 'POST';
                $endpoint = GpApiRequest::RISK_ASSESSMENTS;
                $requestData['account_name'] =  $config->accessTokenInfo->riskAssessmentAccountName;
                $requestData['account_id'] =  $config->accessTokenInfo->riskAssessmentAccountID;
                $requestData['reference'] = !empty($builder->getReferenceNumber()) ?
                    $builder->getReferenceNumber() : GenerationUtils::getGuid();
                $requestData['source'] = $builder->getAuthenticationSource();
                $requestData['merchant_contact_url'] = $config->merchantContactUrl;
                $requestData['order'] = $this->setOrderParam();
                if (!empty($this->builder->getShippingAddress())) {
                    $requestData['order']['shipping_address']['country'] =
                        CountryUtils::getCountryCodeByCountry($this->builder->getShippingAddress()->countryCode);
                }
                $requestData['payment_method'] = $this->setPaymentMethodParam($builder->paymentMethod);
                $requestData['payer'] = $this->setPayerParam();
                $requestData['payer_prior_three_ds_authentication_data'] = $this->setPayerPrior3DSAuthenticationDataParam();
                $requestData['recurring_authorization_data'] = $this->setRecurringAuthorizationDataParam();
                $requestData['payer_login_data'] = $this->setPayerLoginDataParam();
                $requestData['browser_data'] = $this->setBrowserDataParam($builder->getBrowserData());
                $requestData = $this->cleanRequestData($requestData);
                break;
            default:
                 throw new UnsupportedTransactionException(
                     sprintf("Your current gateway does not %s transaction type.", $builder->transactionType)
                 );
        }
        GpApiRequest::$maskedValues = $this->maskedValues;

        return new GpApiRequest(
            $endpoint,
            $verb,
            $requestData
        );
    }

    private function verifyEnrolled(Secure3dBuilder $builder, GpApiConfig $config)
    {
        $requestData = [];
        $mappedMessageCategory = EnumMapping::mapMessageCategory(GatewayProvider::GP_API, $builder->messageCategory);

        $requestData['merchant_id'] = $config->accessTokenInfo->merchantId;
        $requestData['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
        $requestData['reference'] = !empty($builder->getReferenceNumber()) ?
            $builder->getReferenceNumber() : GenerationUtils::getGuid();
        $requestData['channel'] = $config->channel;
        $requestData['amount'] = StringUtils::toNumeric($builder->getAmount(), $builder->getCurrency());
        $requestData['currency'] = $builder->getCurrency();
        $requestData['country'] = $config->country;
        $requestData['transaction_type'] = 'SALE';

        $requestData['payment_method'] = $this->setPaymentMethodParam($builder->paymentMethod);
        $requestData['payment_method']->entry_mode = 'ECOM';

        $requestData['three_ds'] = [
            'message_category' => !empty($mappedMessageCategory) ? $mappedMessageCategory : null,
            'preference' => $builder->challengeRequestIndicator,
            'source' => (string) $builder->getAuthenticationSource(),
            'initiator' => 'MERCHANT'
        ];

        $requestData['notifications'] = [
            'three_ds_method_return_url' => $config->methodNotificationUrl
        ];
        if (!empty($builder->storedCredential)) {
            $this->setStoreCredentialParam($builder->storedCredential, $requestData);
        }

        return $this->cleanRequestData($requestData);
    }

    private function initiateAuthenticationData(Secure3dBuilder $builder, GpApiConfig $config)
    {
        $requestData = [];
        $mappedMessageCategory = EnumMapping::mapMessageCategory(GatewayProvider::GP_API, $builder->messageCategory);

        $requestData['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
        $requestData['channel'] = $config->channel;
        $requestData['amount'] = StringUtils::toNumeric($builder->getAmount(), $builder->getCurrency());
        $requestData['currency'] = $builder->getCurrency();
        $requestData['country'] = $config->country;
        $requestData['reference'] = !empty($builder->getReferenceNumber()) ?
            $builder->getReferenceNumber() : GenerationUtils::getGuid();
        $requestData['transaction_type'] = 'SALE';
        $requestData['initiator'] = 'PAYER';
        $requestData['method_url_completion_status'] = (string) $builder->methodUrlCompletion;
        $requestData['merchant_contact_url'] = $config->merchantContactUrl;

        // Stored Credential (at top level)
        if (!empty($builder->storedCredential)) {
            $this->setStoreCredentialParam($builder->storedCredential, $requestData);
        }

        // Three DS Data
        $requestData['three_ds'] = [
            'source' => (string) $builder->getAuthenticationSource(),
            'preference' => $builder->challengeRequestIndicator,
            'message_version' => $builder->threeDSecure->messageVersion,
            'message_category' => !empty($mappedMessageCategory) ? $mappedMessageCategory : null,
            'initiator' => 'PAYER'
        ];

        // Three DS Message Extension (if available)
        if (!empty($builder->threeDSecure->messageExtension)) {
            $requestData['three_ds']['message_extension'] = $builder->threeDSecure->messageExtension;
        }

        // Order Data
        $requestData['order'] = $this->setOrderParam();

        // Payment Method
        $requestData['payment_method'] = $this->setPaymentMethodParam($builder->paymentMethod);
        $requestData['payment_method']->entry_mode = 'ECOM';

        if (
            !empty($builder->paymentMethod) &&
            !empty($builder->paymentMethod->cardHolderName)
        ) {
            $nameParts = preg_split('/\s+/', trim((string)$builder->paymentMethod->cardHolderName));
            $requestData['payment_method']->first_name = $nameParts[0] ?? null;
            $requestData['payment_method']->last_name = isset($nameParts[1])
                ? implode(' ', array_slice($nameParts, 1))
                : null;
        }

        // Payer Data
        $requestData['payer'] = $this->setPayerParam();
        if (!empty($builder->billingAddress)) {
            $requestData['payer']['billing_address'] = [
                'line1' => $builder->billingAddress->streetAddress1,
                'line2' => $builder->billingAddress->streetAddress2,
                'line3' => $builder->billingAddress->streetAddress3,
                'city' => $builder->billingAddress->city,
                'postal_code' => $builder->billingAddress->postalCode,
                'state' => $builder->billingAddress->state,
                'country' => CountryUtils::getNumericCodeByCountry($builder->billingAddress->countryCode)
            ];
        }

        // Prior 3DS Authentication Data
        $requestData['payer_prior_three_ds_authentication_data'] = $this->setPayerPrior3DSAuthenticationDataParam();

        // Recurring Authorization Data
        $requestData['recurring_authorization_data'] = $this->setRecurringAuthorizationDataParam();

        // Payer Login Data
        $requestData['payer_login_data'] = $this->setPayerLoginDataParam();

        // Decoupled Flow (if applicable)
        if (isset($builder->decoupledFlowRequest)) {
            $requestData['decoupled_flow_request'] = $builder->decoupledFlowRequest === true ? DecoupledFlowRequest::DECOUPLED_PREFERRED :
                DecoupledFlowRequest::DO_NOT_USE_DECOUPLED;
        }
        $requestData['decoupled_flow_timeout'] = $builder->decoupledFlowTimeout ?? null;

        // Whitelist Status (if available)
        if (!empty($builder->whitelistStatus)) {
            $requestData['whitelist_status'] = $builder->whitelistStatus;
        }

        // Top-level Message Extension (if available)
        if (!empty($builder->messageExtension)) {
            $requestData['message_extension'] = $builder->messageExtension;
        }

        // Browser Data or Mobile Data
        if (!empty($builder->getBrowserData()) && $builder->getAuthenticationSource() != AuthenticationSource::MOBILE_SDK) {
            $requestData['browser_data'] = $this->setBrowserDataParam($builder->getBrowserData());
        }
        if (!empty($builder->mobileData)) {
            $requestData['mobile_data'] = [
                'encoded_data' => $builder->mobileData->encodedData,
                'application_reference' => $builder->mobileData->applicationReference,
                'sdk_interface' => $builder->mobileData->sdkInterface,
                'sdk_ui_type' => EnumMapping::mapSdkUiType(GatewayProvider::GP_API, $builder->mobileData->sdkUiTypes),
                'ephemeral_public_key' => json_decode($builder->mobileData->ephemeralPublicKey),
                'maximum_timeout' => $builder->mobileData->maximumTimeout,
                'reference_number' => $builder->mobileData->referenceNumber,
                'sdk_trans_reference' => $builder->mobileData->sdkTransReference
            ];
        }

        // Notifications
        $requestData['notifications'] = [
            'challenge_return_url' => $config->challengeNotificationUrl,
            'three_ds_method_url' => null,
            'decoupled_notification_url' => $builder->decoupledNotificationUrl ?? null
        ];

        // Clean out null and empty values before returning
        return $this->cleanRequestData($requestData);
    }

    private function setPaymentMethodParam($cardData)
    {
        $paymentMethod = new PaymentMethod();
        if ($cardData instanceof ITokenizable && !empty($cardData->token)) {
            $paymentMethod->id = $cardData->token;

        }
        if ($cardData instanceof ICardData && empty($cardData->token)) {
            $expMonth = $cardData->expMonth ?? '';
            $expYear = !empty($cardData->expYear) ?
                substr(str_pad($cardData->expYear, 4, '0', STR_PAD_LEFT), 2, 2) : '';
            $paymentMethod->card = (object) [
                'brand' => !empty($cardData->getCardType()) ? strtoupper($cardData->getCardType()) : '',
                'number' => $cardData->number ?? '',
                'expiry_month' => $expMonth,
                'expiry_year' => $expYear
            ];
            $paymentMethod->name = $cardData->cardHolderName ?? '';
            $this->maskedValues = ProtectSensitiveData::hideValues(
                [
                    'payment_method.card.expiry_month' => $expMonth,
                    'payment_method.card.expiry_year' => $expYear,
                ]
            );
            $this->maskedValues = ProtectSensitiveData::hideValue(
                    'payment_method.card.number', $cardData->number ?? '', 4, 6
            );
        }


        return $paymentMethod;
    }

    /**
     * Set the order parameter in the request
     */
    private function setOrderParam()
    {
        $preOrderAvailabilityDate = $this->builder->getPreOrderAvailabilityDate();
        $orderCreateDate = $this->builder->getOrderCreateDate();
        if ($orderCreateDate instanceof \DateTimeInterface) {
            $formattedOrderCreateDate = $orderCreateDate->format('Y-m-d\TH:i:s.u\Z');
        } elseif (!empty($orderCreateDate)) {
            $formattedOrderCreateDate = (new \DateTime((string) $orderCreateDate))->format('Y-m-d\TH:i:s.u\Z');
        } else {
            $formattedOrderCreateDate = null;
        }

        $order = [
            'amount' => StringUtils::toNumeric($this->builder->getAmount(), $this->builder->getCurrency()),
            'currency' => $this->builder->getCurrency(),
            'reference' => $this->builder->getOrderId(),
            'address_match_indicator' => $this->builder->isAddressMatchIndicator(),
            'time_created_reference' => $formattedOrderCreateDate,
            'gift_card_count' => $this->builder->getGiftCardCount(),
            'gift_card_currency' => $this->builder->getGiftCardCurrency(),
            'gift_card_amount' => $this->builder->getGiftCardAmount(),
            'delivery_email' => $this->builder->getDeliveryEmail(),
            'delivery_timeframe' => $this->builder->getDeliveryTimeframe(),
            'shipping_method' => (string) $this->builder->getShippingMethod(),
            'shipping_name_matches_cardholder_name' => $this->builder->getShippingNameMatchesCardHolderName(),
            'preorder_indicator' => (string) $this->builder->getPreOrderIndicator(),
            'preorder_availability_date' => !empty($preOrderAvailabilityDate) ?
                ($preOrderAvailabilityDate instanceof \DateTimeInterface
                    ? $preOrderAvailabilityDate->format('Y-m-d')
                    : (new \DateTime($preOrderAvailabilityDate))->format('Y-m-d')) : null,
            'reorder_indicator' => (string) $this->builder->getReorderIndicator(),
            'transaction_type' => (string) $this->builder->getOrderTransactionType()
        ];

        $shippingAddress = $this->builder->getShippingAddress();
        if (!empty($shippingAddress)) {
            $order['shipping_address'] = [
                'line1' => $shippingAddress->streetAddress1,
                'line2' => $shippingAddress->streetAddress2,
                'line3' => $shippingAddress->streetAddress3,
                'city' => $shippingAddress->city,
                'postal_code' => $shippingAddress->postalCode,
                'state' => $shippingAddress->state,
                'country' => CountryUtils::getNumericCodeByCountry($shippingAddress->countryCode)
            ];
        }

        return $order;
    }


    /**
     * Set the stored credential details in the request
     *
     * @param StoredCredential $storedCredential
     * @param array $threeDS
     */
    private function setStoreCredentialParam($storedCredential, &$threeDS)
    {
        $initiator = EnumMapping::mapStoredCredentialInitiator(GatewayProvider::GP_API, $storedCredential->initiator);
        $threeDS['initiator'] = !empty($initiator) ? $initiator : null;
        $threeDS['stored_credential'] = [
            'model' => strtoupper($storedCredential->type),
            'reason' => strtoupper($storedCredential->reason),
            'sequence' => strtoupper($storedCredential->sequence)
        ];
    }

    private function setPayerParam()
    {
        $dateFormat = ($this->builder->transactionType === TransactionType::RISK_ASSESS ? 'Y-m-d\TH:i:s': 'Y-m-d');

        return[
            'reference' => $this->builder->getCustomerAccountId(),
            'account_age' => (string) $this->builder->getAccountAgeIndicator(),
            'account_creation_date' => !empty($this->builder->getAccountCreateDate()) ?
                (new \DateTime($this->builder->getAccountCreateDate()))->format('Y-m-d') : null,
            'account_change_date' => !empty($this->builder->getAccountChangeDate()) ?
                (new \DateTime($this->builder->getAccountChangeDate()))->format('Y-m-d') : null,
            'account_change_indicator' => (string) $this->builder->getAccountChangeIndicator(),
            'account_password_change_date' => !empty($this->builder->getPasswordChangeDate()) ?
                (new \DateTime($this->builder->getPasswordChangeDate()))->format('Y-m-d') : null,
            'account_password_change_indicator' => (string) $this->builder->getPasswordChangeIndicator(),
            'home_phone' => [
                'country_code' => $this->builder->getHomeCountryCode(),
                'subscriber_number' => $this->builder->getHomeNumber()
            ],
            'work_phone' => [
                'country_code' => $this->builder->getWorkCountryCode(),
                'subscriber_number' => $this->builder->getWorkNumber()
            ],
            'mobile_phone' => [
                'country_code' => $this->builder->getMobileCountryCode(),
                'subscriber_number' => $this->builder->getMobileNumber()
            ],
            'payment_account_creation_date' => !empty($this->builder->getPaymentAccountCreateDate()) ?
                (new \DateTime($this->builder->getPaymentAccountCreateDate()))->format('Y-m-d') : null,
            'payment_account_age_indicator' => (string) $this->builder->getPaymentAgeIndicator(),
            'suspicious_account_activity' => $this->builder->suspiciousAccountActivity ?? null,
            'purchases_last_6months_count' => !empty($this->builder->getNumberOfPurchasesInLastSixMonths()) ?
                str_pad(
                    $this->builder->getNumberOfPurchasesInLastSixMonths(),
                    2,
                    '0',
                    STR_PAD_LEFT
                ) : null,
            'transactions_last_24hours_count' => !empty($this->builder->getNumberOfTransactionsInLast24Hours()) ?
                str_pad(
                    $this->builder->getNumberOfTransactionsInLast24Hours(),
                    2,
                    '0',
                    STR_PAD_LEFT
                ) : null,
            'transaction_last_year_count' => !empty($this->builder->getNumberOfTransactionsInLastYear()) ?
                str_pad(
                    $this->builder->getNumberOfTransactionsInLastYear(),
                    2,
                    '0',
                    STR_PAD_LEFT
                ) : null,
            'provision_attempt_last_24hours_count' => !empty($this->builder->getNumberOfAddCardAttemptsInLast24Hours()) ?
                str_pad(
                    $this->builder->getNumberOfAddCardAttemptsInLast24Hours(),
                    2,
                    '0',
                    STR_PAD_LEFT
                ) : null,
            'shipping_address_time_created_reference' => !empty($this->builder->getShippingAddressCreateDate()) ?
                (new \DateTime($this->builder->getShippingAddressCreateDate()))->format($dateFormat) : null,
            'shipping_address_creation_indicator' => (string) $this->builder->getShippingAddressUsageIndicator(),
            'email' => $this->builder->getCustomerEmail()
        ];
    }

    private function setBrowserDataParam($browserData)
    {
        if (empty($browserData)) {
            return;
        }

        return [
            'accept_header' => $browserData->acceptHeader,
            'color_depth' => (string) $browserData->colorDepth,
            'ip' => $browserData->ipAddress,
            'java_enabled' => StringUtils::boolToString($browserData->javaEnabled),
            'javascript_enabled' => StringUtils::boolToString($browserData->javaScriptEnabled),
            'language' => $browserData->language,
            'screen_height' => $browserData->screenHeight,
            'screen_width' => $browserData->screenWidth,
            'challenge_window_size' => (string) $browserData->challengWindowSize,
            'timezone' => (string) $browserData->timeZone,
            'user_agent' => $browserData->userAgent
        ];
    }

    private function setPayerPrior3DSAuthenticationDataParam()
    {
        return [
            'authentication_method' => (string) $this->builder->getPriorAuthenticationMethod(),
            'acs_transaction_reference' => $this->builder->getPriorAuthenticationTransactionId(),
            'authentication_timestamp' => !empty($this->builder->getPriorAuthenticationTimestamp()) ?
                (new \DateTime($this->builder->getPriorAuthenticationTimestamp()))->format('Y-m-d\TH:i:s.u\Z') : null,
            'authentication_data' => $this->builder->getPriorAuthenticationData()
        ];
    }

    private function setRecurringAuthorizationDataParam()
    {
        return [
            'max_number_of_instalments' => !empty($this->builder->getMaxNumberOfInstallments()) ?
                str_pad(
                    $this->builder->getMaxNumberOfInstallments(),
                    2,
                    '0',
                    STR_PAD_LEFT
                ) : null,
            'frequency' => $this->builder->getRecurringAuthorizationFrequency(),
            'expiry_date' => $this->builder->getRecurringAuthorizationExpiryDate()
        ];
    }

    private function setPayerLoginDataParam()
    {
        return [
            'authentication_data' => $this->builder->getCustomerAuthenticationData(),
            'authentication_timestamp' => !empty($this->builder->getCustomerAuthenticationTimestamp()) ?
                (new \DateTime($this->builder->getCustomerAuthenticationTimestamp()))->format('Y-m-d\TH:i:s.u\Z') : null,
            'authentication_type' => (string) $this->builder->getCustomerAuthenticationMethod()
        ];
    }

    /**
     * Remove nulls, empty strings, and empty nested arrays from request payloads.
     *
     * @param array $data
     * @return array
     */
    private function cleanRequestData(array $data): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $cleanedArray = $this->cleanRequestData($value);

                if (!empty($cleanedArray)) {
                    $cleaned[$key] = $cleanedArray;
                }

                continue;
            }

            $cleaned[$key] = $value;
        }

        return $cleaned;
    }

    public function buildRequestFromJson(mixed $jsonRequest, mixed $config): mixed
    {
        throw new \GlobalPayments\Api\Entities\Exceptions\NotImplementedException();
    }
}
