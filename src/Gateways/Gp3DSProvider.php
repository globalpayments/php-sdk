<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\Enums\ExemptionReason;
use GlobalPayments\Api\Entities\Enums\ExemptStatus;
use GlobalPayments\Api\Entities\MessageExtension;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\Utils\CardUtils;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\StringUtils;

class Gp3DSProvider extends RestGateway implements ISecure3dProvider
{
    /** @var string */
    private $accountId;
    /** @var string */
    private $challengeNotificationUrl;
    /** @var string */
    private $merchantContactUrl;
    /** @var string */
    private $merchantId;
    /** @var string */
    private $methodNotificationUrl;
    /** @var string */
    private $sharedSecret;

    /** @var Secure3dVersion */
    public $version = Secure3dVersion::TWO;

    /** @return Secure3dVersion */
    public function getVersion()
    {
        return $this->version;
    }

    /** @return void */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }
    /** @return void */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }
    /** @return void */
    public function setSharedSecret($sharedSecret)
    {
        $this->sharedSecret = $sharedSecret;
    }
    /** @return void */
    public function setChallengeNotificationUrl($challengeNotificationUrl)
    {
        $this->challengeNotificationUrl = $challengeNotificationUrl;
    }
    /** @return void */
    public function setMerchantContactUrl($merchantContactUrl)
    {
        $this->merchantContactUrl = $merchantContactUrl;
    }
    /** @return void */
    public function setMethodNotificationUrl($methodNotificationUrl)
    {
        $this->methodNotificationUrl = $methodNotificationUrl;
    }

    protected function maybeSetKey(array $arr, $key, $value = null)
    {
        if (!is_null($value)) {
            $arr[$key] = $value;
        }
        return $arr;
    }

    /**
     * @param Secure3dBuilder $builder
     * @return Transaction
     * @throws ApiException
     * @throws GatewayException
     */
    public function processSecure3d(Secure3dBuilder $builder)
    {
        $transType = $builder->getTransactionType();
        $timestamp = date("Y-m-d\TH:i:s.u");
        $paymentMethod = $builder->paymentMethod;
        $secure3d = $paymentMethod;
        $request = [];

        if ($transType === TransactionType::VERIFY_ENROLLED) {
            $request = $this->maybeSetKey($request, 'request_timestamp', $timestamp);
            $request = $this->maybeSetKey($request, 'merchant_id', $this->merchantId);
            $request = $this->maybeSetKey($request, 'account_id', $this->accountId);
            $request = $this->maybeSetKey($request, 'method_notification_url', $this->methodNotificationUrl);

            $hashValue = '';
            if ($paymentMethod instanceof CreditCardData) {
                $cardData = $paymentMethod;
                $request = $this->maybeSetKey($request, 'number', $cardData->number);
                $request = $this->maybeSetKey(
                    $request,
                    'scheme',
                    $this->mapCardScheme(strtoupper(CardUtils::getBaseCardType($cardData->getCardType())))
                );
                $hashValue = $cardData->number;
            } elseif ($paymentMethod instanceof RecurringPaymentMethod) {
                $storedCard = $paymentMethod;
                $request = $this->maybeSetKey($request, 'payer_reference', $storedCard->customerKey);
                $request = $this->maybeSetKey($request, 'payment_method_reference', $storedCard->key);
                $hashValue = $storedCard->customerKey;
            }

            $hash = GenerationUtils::generateHash($this->sharedSecret, implode('.', [$timestamp, $this->merchantId, $hashValue]));
            $verb = 'POST';
            $endpoint = 'protocol-versions';
            $queryValues = null;
            $request = json_encode($request);
        } elseif ($transType === TransactionType::VERIFY_SIGNATURE) {
            $hash = GenerationUtils::generateHash($this->sharedSecret, implode('.', [$timestamp, $this->merchantId, $builder->getServerTransactionId()]));
            $queryValues = [];
            $queryValues['merchant_id'] = $this->merchantId;
            $queryValues['request_timestamp'] = $timestamp;
            $verb = 'GET';
            $endpoint = sprintf('authentications/%s', $builder->getServerTransactionId());
            $request = null;
        } elseif ($transType === TransactionType::INITIATE_AUTHENTICATION) {
            $orderId = $builder->getOrderId();
            if (empty($orderId)) {
                $orderId = GenerationUtils::generateOrderId();
            }

            $secureEcom = $secure3d->threeDSecure;

            $request = $this->maybeSetKey($request, 'request_timestamp', $timestamp);
            $request = $this->maybeSetKey($request, 'authentication_source', $builder->getAuthenticationSource());
            $request = $this->maybeSetKey($request, 'authentication_request_type', $builder->getAuthenticationRequestType());
            $request = $this->maybeSetKey($request, 'message_category', $builder->getMessageCategory());
            $request = $this->maybeSetKey($request, 'message_version', '2.1.0');
            $request = $this->maybeSetKey($request, 'server_trans_id', $secureEcom->serverTransactionId);
            $request = $this->maybeSetKey($request, 'merchant_id', $this->merchantId);
            $request = $this->maybeSetKey($request, 'account_id', $this->accountId);
            $request = $this->maybeSetKey($request, 'challenge_notification_url', $this->challengeNotificationUrl);
            $request = $this->maybeSetKey($request, 'challenge_request_indicator', $builder->getChallengeRequestIndicator());
            $request = $this->maybeSetKey($request, 'method_url_completion', $builder->getMethodUrlCompletion());
            $request = $this->maybeSetKey($request, 'merchant_contact_url', $this->merchantContactUrl);
            $request = $this->maybeSetKey($request, 'merchant_initiated_request_type', $builder->getMerchantInitiatedRequestType());
            $request = $this->maybeSetKey($request, 'whitelist_status', $builder->getWhitelistStatus());
            $request = $this->maybeSetKey($request, 'decoupled_flow_request', $builder->decoupledFlowRequest ?? null);
            $request = $this->maybeSetKey($request, 'decoupled_flow_timeout', $builder->decoupledFlowTimeout ?? null);
            $request = $this->maybeSetKey($request, 'decoupled_notification_url', $builder->decoupledNotificationUrl ?? null);
            $request = $this->maybeSetKey($request, 'enable_exemption_optimization', $builder->enableExemptionOptimization);

            // card details
            $hashValue = '';
            $request['card_detail'] = [];
            if ($paymentMethod instanceof CreditCardData) {
                $cardData = $paymentMethod;
                $hashValue = $cardData->number;

                $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'number', $cardData->number);
                $request['card_detail'] = $this->maybeSetKey(
                    $request['card_detail'],
                    'scheme',
                    strtoupper(CardUtils::getBaseCardType($cardData->getCardType()))
                );
                $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'expiry_month', $cardData->expMonth);
                $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'expiry_year',
                                                substr(str_pad($cardData->expYear, 4, '0', STR_PAD_LEFT), 2, 2));
                $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'full_name', $cardData->cardHolderName);

                if (!empty($cardData->cardHolderName)) {
                    $names = explode(' ', $cardData->cardHolderName, 2);
                    if (count($names) >= 1) {
                        $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'first_name', $names[0]);
                    }
                    if (count($names) >= 2) {
                        $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'last_name', $names[1]);
                    }
                }
            } elseif ($paymentMethod instanceof RecurringPaymentMethod) {
                $storedCard = $paymentMethod;
                $hashValue = $storedCard->customerKey;

                $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'payer_reference', $storedCard->customerKey);
                $request['card_detail'] = $this->maybeSetKey($request['card_detail'], 'payment_method_reference', $storedCard->key);
            }

            // order details
            $request['order'] = [];
            $request['order'] = $this->maybeSetKey($request['order'], 'amount', preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->getAmount())));
            $request['order'] = $this->maybeSetKey($request['order'], 'currency', $builder->getCurrency());
            $request['order'] = $this->maybeSetKey($request['order'], 'id', $orderId);
            $request['order'] = $this->maybeSetKey($request['order'], 'address_match_indicator', ($builder->isAddressMatchIndicator() ? true : false));
            $request['order'] = $this->maybeSetKey($request['order'], 'date_time_created', (new \DateTime($builder->getOrderCreateDate()))->format(\DateTime::RFC3339_EXTENDED));
            $request['order'] = $this->maybeSetKey($request['order'], 'gift_card_count', $builder->getGiftCardCount());
            $request['order'] = $this->maybeSetKey($request['order'], 'gift_card_currency', $builder->getGiftCardCurrency());
            $request['order'] = $this->maybeSetKey($request['order'], 'gift_card_amount', preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->getGiftCardAmount())));
            $request['order'] = $this->maybeSetKey($request['order'], 'delivery_email', $builder->getDeliveryEmail());
            $request['order'] = $this->maybeSetKey($request['order'], 'delivery_timeframe', $builder->getDeliveryTimeframe());
            $request['order'] = $this->maybeSetKey($request['order'], 'shipping_method', $builder->getShippingMethod());
            $request['order'] = $this->maybeSetKey($request['order'], 'shipping_name_matches_cardholder_name', $builder->getShippingNameMatchesCardHolderName());
            $request['order'] = $this->maybeSetKey($request['order'], 'preorder_indicator', $builder->getPreOrderIndicator());
            $request['order'] = $this->maybeSetKey($request['order'], 'reorder_indicator', $builder->getReorderIndicator());
            $request['order'] = $this->maybeSetKey($request['order'], 'transaction_type', $builder->getOrderTransactionType());
            $request['order'] = $this->maybeSetKey($request['order'], 'preorder_availability_date', null !== $builder->getPreOrderAvailabilityDate() ? date('Y-m-d', $builder->getPreOrderAvailabilityDate()) : null);

            // shipping address
            $shippingAddress = $builder->getShippingAddress();
            if (!empty($shippingAddress)) {
                $request['order']['shipping_address'] = [];
                $request['order']['shipping_address'] = $this->maybeSetKey($request['order']['shipping_address'], 'line1', $shippingAddress->streetAddress1);
                $request['order']['shipping_address'] = $this->maybeSetKey($request['order']['shipping_address'], 'line2', $shippingAddress->streetAddress2);
                $request['order']['shipping_address'] = $this->maybeSetKey($request['order']['shipping_address'], 'line3', $shippingAddress->streetAddress3);
                $request['order']['shipping_address'] = $this->maybeSetKey($request['order']['shipping_address'], 'city', $shippingAddress->city);
                $request['order']['shipping_address'] = $this->maybeSetKey($request['order']['shipping_address'], 'postal_code', $shippingAddress->postalCode);
                $request['order']['shipping_address'] = $this->maybeSetKey($request['order']['shipping_address'], 'state', $shippingAddress->state);
                $request['order']['shipping_address'] = $this->maybeSetKey($request['order']['shipping_address'], 'country', $shippingAddress->countryCode);
            }

            // payer
            $request['payer'] = [];
            $request['payer'] = $this->maybeSetKey($request['payer'], 'email', $builder->getCustomerEmail());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'id', $builder->getCustomerAccountId());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'account_age', $builder->getAccountAgeIndicator());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'account_creation_date', null !== $builder->getAccountCreateDate() ? date('Y-m-d', strtotime($builder->getAccountCreateDate())) : null);
            $request['payer'] = $this->maybeSetKey($request['payer'], 'account_change_indicator', $builder->getAccountChangeIndicator());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'account_change_date', null !== $builder->getAccountChangeDate() ? date('Y-m-d', strtotime($builder->getAccountChangeDate())) : null);
            $request['payer'] = $this->maybeSetKey($request['payer'], 'account_password_change_indicator', $builder->getPasswordChangeIndicator());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'account_password_change_date', null !== $builder->getPasswordChangeDate() ? date('Y-m-d', strtotime($builder->getPasswordChangeDate())) : null);
            $request['payer'] = $this->maybeSetKey($request['payer'], 'payment_account_age_indicator', $builder->getAccountAgeIndicator());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'payment_account_creation_date', null !== $builder->getAccountCreateDate() ? date('Y-m-d', strtotime($builder->getAccountCreateDate())) : null);
            $request['payer'] = $this->maybeSetKey($request['payer'], 'purchase_count_last_6months', $builder->getNumberOfPurchasesInLastSixMonths());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'transaction_count_last_24hours', $builder->getNumberOfTransactionsInLast24Hours());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'transaction_count_last_year', $builder->getNumberOfTransactionsInLastYear());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'provision_attempt_count_last_24hours', $builder->getNumberOfAddCardAttemptsInLast24Hours());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'shipping_address_creation_indicator', $builder->getShippingAddressUsageIndicator());
            $request['payer'] = $this->maybeSetKey($request['payer'], 'shipping_address_creation_date', null !== $builder->getShippingAddressCreateDate() ? date('Y-m-d', strtotime($builder->getShippingAddressCreateDate())) : null);

            // suspicious activity
            if ($builder->getPreviousSuspiciousActivity() != null) {
                $request['payer'] = $this->maybeSetKey($request['payer'], 'suspicious_account_activity', $builder->getPreviousSuspiciousActivity() ? 'SUSPICIOUS_ACTIVITY' : 'NO_SUSPICIOUS_ACTIVITY');
            }

            // home phone
            if (!empty($builder->getHomeNumber())) {
                $request['payer']['home_phone'] = [];
                $request['payer']['home_phone'] = $this->maybeSetKey($request['payer']['home_phone'], 'country_code', StringUtils::validateToNumber($builder->getHomeCountryCode()));
                $request['payer']['home_phone'] = $this->maybeSetKey($request['payer']['home_phone'], 'subscriber_number', StringUtils::validateToNumber($builder->getHomeNumber()));
            }

            // work phone
            if (!empty($builder->getWorkNumber())) {
                $request['payer']['work_phone'] = [];
                $request['payer']['work_phone'] = $this->maybeSetKey($request['payer']['work_phone'], 'country_code', StringUtils::validateToNumber($builder->getWorkCountryCode()));
                $request['payer']['work_phone'] = $this->maybeSetKey($request['payer']['work_phone'], 'subscriber_number', StringUtils::validateToNumber($builder->getWorkNumber()));
            }

            // payer login data
            if ($builder->hasPayerLoginData()) {
                $request['payer_login_data'] = [];
                $request['payer_login_data'] = $this->maybeSetKey($request['payer_login_data'], 'authentication_data', $builder->getCustomerAuthenticationData());
                $request['payer_login_data'] = $this->maybeSetKey($request['payer_login_data'], 'authentication_timestamp', $builder->getCustomerAuthenticationTimestamp());
                $request['payer_login_data'] = $this->maybeSetKey($request['payer_login_data'], 'authentication_type', $builder->getCustomerAuthenticationMethod());
            }

            // prior authentication data
            if ($builder->hasPriorAuthenticationData()) {
                $request['payer_prior_three_ds_authentication_data'] = [];
                $request['payer_prior_three_ds_authentication_data'] = $this->maybeSetKey($request['payer_prior_three_ds_authentication_data'], 'authentication_method', $builder->getPriorAuthenticationMethod());
                $request['payer_prior_three_ds_authentication_data'] = $this->maybeSetKey($request['payer_prior_three_ds_authentication_data'], 'acs_transaction_id', $builder->getPriorAuthenticationTransactionId());
                $request['payer_prior_three_ds_authentication_data'] = $this->maybeSetKey($request['payer_prior_three_ds_authentication_data'], 'authentication_timestamp', date('Y-m-d\TH:i:s.u\Z', strtotime($builder->getPriorAuthenticationTimestamp())));
                $request['payer_prior_three_ds_authentication_data'] = $this->maybeSetKey($request['payer_prior_three_ds_authentication_data'], 'authentication_data', $builder->getPriorAuthenticationData());
            }

            // recurring authorization data
            if ($builder->hasRecurringAuthData()) {
                $request['recurring_authorization_data'] = [];
                $request['recurring_authorization_data'] = $this->maybeSetKey($request['recurring_authorization_data'], 'max_number_of_instalments', $builder->getMaxNumberOfInstallments());
                $request['recurring_authorization_data'] = $this->maybeSetKey($request['recurring_authorization_data'], 'frequency', $builder->getRecurringAuthorizationFrequency());
                $request['recurring_authorization_data'] = $this->maybeSetKey($request['recurring_authorization_data'], 'expiry_date', date('Y-m-d', strtotime($builder->getRecurringAuthorizationExpiryDate())));
            }

            // billing details
            $billingAddress = $builder->getBillingAddress();
            if (!empty($billingAddress)) {
                $request['payer']['billing_address'] = [];
                $request['payer']['billing_address'] = $this->maybeSetKey($request['payer']['billing_address'], 'line1', $billingAddress->streetAddress1);
                $request['payer']['billing_address'] = $this->maybeSetKey($request['payer']['billing_address'], 'line2', $billingAddress->streetAddress2);
                $request['payer']['billing_address'] = $this->maybeSetKey($request['payer']['billing_address'], 'line3', $billingAddress->streetAddress3);
                $request['payer']['billing_address'] = $this->maybeSetKey($request['payer']['billing_address'], 'city', $billingAddress->city);
                $request['payer']['billing_address'] = $this->maybeSetKey($request['payer']['billing_address'], 'postal_code', $billingAddress->postalCode);
                $request['payer']['billing_address'] = $this->maybeSetKey($request['payer']['billing_address'], 'state', $billingAddress->state);
                $request['payer']['billing_address'] = $this->maybeSetKey($request['payer']['billing_address'], 'country', $billingAddress->countryCode);
            }

            // mobile phone
            if (!empty($builder->getMobileNumber())) {
                $request['payer']['mobile_phone'] = [];
                $request['payer']['mobile_phone'] = $this->maybeSetKey($request['payer']['mobile_phone'], 'country_code', StringUtils::validateToNumber($builder->getMobileCountryCode()));
                $request['payer']['mobile_phone'] = $this->maybeSetKey($request['payer']['mobile_phone'], 'subscriber_number', StringUtils::validateToNumber($builder->getMobileNumber()));
            }

            // browser_data
            $browserData = $builder->getBrowserData();
            if (!empty($browserData)) {
                $request['browser_data'] = [];
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'accept_header', $browserData->acceptHeader);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'color_depth', $browserData->colorDepth);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'ip', $browserData->ipAddress);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'java_enabled', $browserData->javaEnabled);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'javascript_enabled', $browserData->javaScriptEnabled);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'language', $browserData->language);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'screen_height', $browserData->screenHeight);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'screen_width', $browserData->screenWidth);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'challenge_window_size', $browserData->challengWindowSize);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'timezone', $browserData->timeZone);
                $request['browser_data'] = $this->maybeSetKey($request['browser_data'], 'user_agent', $browserData->userAgent);
            }

            // mobile fields
            if ($builder->hasMobileFields()) {
                $request['sdk_information'] = [];
                $request['sdk_information'] = $this->maybeSetKey($request['sdk_information'], 'application_id', $builder->getApplicationId());
                $request['sdk_information'] = $this->maybeSetKey($request['sdk_information'], 'ephemeral_public_key', $builder->getEphemeralPublicKey());
                $request['sdk_information'] = $this->maybeSetKey($request['sdk_information'], 'maximum_timeout',
                            (!empty($builder->getMaximumTimeout())) ? str_pad($builder->getMaximumTimeout(), 2, '0' , STR_PAD_LEFT) : '');
                $request['sdk_information'] = $this->maybeSetKey($request['sdk_information'], 'reference_number', $builder->getReferenceNumber());
                $request['sdk_information'] = $this->maybeSetKey($request['sdk_information'], 'sdk_trans_id', $builder->getSdkTransactionId());
                $request['sdk_information'] = $this->maybeSetKey($request['sdk_information'], 'encoded_data', $builder->getEncodedData());
            }

            // device render options
            if ($builder->getSdkInterface() != null || $builder->getSdkUiTypes() != null) {
                $request['sdk_information']['device_render_options'] = [];
                $request['sdk_information']['device_render_options'] = $this->maybeSetKey($request['sdk_information']['device_render_options'], 'sdk_interface', $builder->getSdkInterface());
                $request['sdk_information']['device_render_options'] = $this->maybeSetKey($request['sdk_information']['device_render_options'], 'sdk_ui_type', $builder->getSdkUiTypes());
            }

            $hash = GenerationUtils::generateHash($this->sharedSecret, implode('.', [$timestamp, $this->merchantId, $hashValue, $secureEcom->serverTransactionId]));
            $verb = 'POST';
            $endpoint = 'authentications';
            $queryValues = null;
            $request = json_encode($request, JSON_UNESCAPED_SLASHES);
        }

        if (!empty($verb) && !empty($endpoint)) {
            $this->headers['Authorization'] = sprintf('securehash %s', $hash);
            $this->headers["X-GP-Version"] = "2.2.0";
            $rawResponse = $this->doTransaction(
                $verb,
                $endpoint,
                $request,
                $queryValues
            );

            return $this->mapResponse($rawResponse);
        }

        throw new ApiException(sprintf('Unknown transaction type %s.', $transType));
    }

    /** @return Transaction */
    private function mapResponse($rawResponse)
    {
        $doc = json_decode($rawResponse, true);
        $secureEcom = new ThreeDSecure();

        // check enrolled
        $secureEcom->serverTransactionId = isset($doc['server_trans_id']) ? $doc['server_trans_id'] : null;
        if (array_key_exists('enrolled', $doc)) {
            $secureEcom->enrolled = (bool)$doc['enrolled'];
        }
        $secureEcom->issuerAcsUrl = (isset($doc['method_url']) ? $doc['method_url'] : '')
            . (isset($doc['challenge_request_url']) ? $doc['challenge_request_url'] : '');

        // get authentication data
        $secureEcom->acsTransactionId = isset($doc['acs_trans_id']) ? $doc['acs_trans_id'] : null;
        $secureEcom->directoryServerTransactionId = isset($doc['ds_trans_id']) ? $doc['ds_trans_id'] : null;
        $secureEcom->authenticationType = isset($doc['authentication_type']) ? $doc['authentication_type'] : null;
        $secureEcom->authenticationValue = isset($doc['authentication_value']) ? $doc['authentication_value'] : null;
        $secureEcom->eci = isset($doc['eci']) ? $doc['eci'] : null;
        $secureEcom->status = isset($doc['status']) ? $doc['status'] : null;
        $secureEcom->statusReason = isset($doc['status_reason']) ? $doc['status_reason'] : null;
        $secureEcom->authenticationSource = isset($doc['authentication_source']) ? $doc['authentication_source'] : null;
        $secureEcom->messageCategory = isset($doc['message_category']) ? $doc['message_category'] : null;
        $secureEcom->messageVersion = isset($doc['message_version']) ? $doc['message_version'] : null;
        $secureEcom->acsInfoIndicator = isset($doc['acs_info_indicator']) ? $doc['acs_info_indicator'] : null;
        $secureEcom->decoupledResponseIndicator = isset($doc['decoupled_response_indicator']) ?
                                                    $doc['decoupled_response_indicator'] : null;
        $secureEcom->whitelistStatus = isset($doc['whitelist_status']) ? $doc['whitelist_status'] : null;
        //exemption optimization
        if (!empty($doc['eos_reason'])) {
            $secureEcom->exemptReason = $doc['eos_reason'];
            $secureEcom->exemptStatus = ($doc['eos_reason'] == ExemptionReason::APPLY_EXEMPTION ?
                ExemptStatus::TRANSACTION_RISK_ANALYSIS : null);
        }

        // challenge mandated
        if (array_key_exists('challenge_mandated', $doc)) {
            $secureEcom->challengeMandated = (bool)$doc['challenge_mandated'];
        }

        // initiate authentication
        $secureEcom->cardHolderResponseInfo =
            isset($doc['cardHolder_response_info']) ? $doc['cardHolder_response_info'] : null;

        // device_render_options
        if (array_key_exists('device_render_options', $doc)) {
            $renderOptions = $doc['device_render_options'];
            $secureEcom->sdkInterface = isset($renderOptions['sdk_interface']) ? $renderOptions['sdk_interface'] : null;
            $secureEcom->sdkUiType = isset($renderOptions['sdk_ui_type']) ? $renderOptions['sdk_ui_type'] : null;
        }

        // message_extension
        if (array_key_exists('message_extension', $doc)) {
            foreach ($doc['message_extension'] as $messageExtension) {
                $msgItem = new MessageExtension();
                $msgItem->criticalityIndicator =
                    isset($messageExtension['criticality_indicator']) ?
                        $messageExtension['criticality_indicator'] : null;
                $msgItem->messageExtensionData = isset($messageExtension['data']) ?
                    json_encode($messageExtension['data']) : null;
                $msgItem->messageExtensionId = isset($messageExtension['id']) ? $messageExtension['id'] : null;
                $msgItem->messageExtensionName = isset($messageExtension['name']) ? $messageExtension['name'] : null;
                $secureEcom->messageExtension[] = $msgItem;
            }
        }

        // versions
        $secureEcom->directoryServerEndVersion =
            isset($doc['ds_protocol_version_end']) ? $doc['ds_protocol_version_end'] : null;
        $secureEcom->directoryServerStartVersion =
            isset($doc['ds_protocol_version_start']) ? $doc['ds_protocol_version_start'] : null;
        $secureEcom->acsEndVersion = isset($doc['acs_protocol_version_end']) ? $doc['acs_protocol_version_end'] : null;
        $secureEcom->acsStartVersion =
            isset($doc['acs_protocol_version_start']) ? $doc['acs_protocol_version_start'] : null;

        // payer authentication request
        if (array_key_exists('method_data', $doc)) {
            $methodData = $doc['method_data'];
            $secureEcom->payerAuthenticationRequest =
                isset($methodData['encoded_method_data']) ? $methodData['encoded_method_data'] : null;
        } elseif (array_key_exists('encoded_creq', $doc)) {
            $secureEcom->payerAuthenticationRequest = isset($doc['encoded_creq']) ? $doc['encoded_creq'] : null;
        }

        $response = new Transaction();
        $response->threeDSecure = $secureEcom;
        return $response;
    }

    private function mapCardScheme($cardType)
    {
        if ($cardType == "MC") {
            return "MASTERCARD";
        } elseif ($cardType == "DINERSCLUB") {
            return "DINERS";
        } else {
            return $cardType;
        }
    }

    /**
     * @throws GatewayException
     * @return string */
    private function handleResponse(GatewayResponse $response)
    {
        if ($response->statusCode != 200 && $response->statusCode != 204) {
            $parsed = json_decode($response->rawResponse, true);
            if (array_key_exists('error', $parsed)) {
                $error = $parsed['error'];
                throw new GatewayException(sprintf("Status code: %s - %s", $response->statusCode, $error));
            }
            throw new GatewayException(sprintf("Status code: %s - %s", $response->statusCode, $error));
        }
        return $response->rawResponse;
    }
}
