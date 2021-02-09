<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\Secure3dBuilder;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\StringUtils;

class Authentication3DSRequest extends GpApiRequest
{
    public $amount;

    public $currency;

    public $notifications;

    public $method_url_completion_status;

    public $source;

    public $merchant_contact_url;

    public $order;

    public $payment_method;

    public $payer;

    public $payer_prior_three_ds_authentication_data;

    public $recurring_authorization_data;

    public $payer_login_data;

    public $browser_data;

    public $message_category;

    public $preference;

    public static function verifyEnrolled(Secure3dBuilder $builder, GpApiConfig $config)
    {
        $threeDS = new Authentication3DSRequest();
        parent::initBaseParams($threeDS, $config);
        $threeDS->reference = !empty($builder->referenceNumber) ? $builder->referenceNumber : GenerationUtils::getGuid();
        $threeDS->amount = StringUtils::toNumeric($builder->amount);
        $threeDS->currency = $builder->currency;

        $threeDS->payment_method = self::setPaymentMethodParam($builder->paymentMethod);
        $threeDS->notifications = [
            'challenge_return_url' => $config->getChallengeNotificationUrl(),
            'three_ds_method_return_url' => $config->getMethodNotificationUrl()
        ];

        return $threeDS;
    }

    public static function initiateAuthenticationData(Secure3dBuilder $builder, GpApiConfig $config)
    {
        $threeDS = new Authentication3DSRequest();
        parent::initBaseParams($threeDS, $config);
        $threeDS->amount = StringUtils::toNumeric($builder->amount);
        $threeDS->currency = $builder->currency;
        $threeDS->preference = $builder->challengeRequestIndicator;
        $threeDS->method_url_completion_status = (string) $builder->methodUrlCompletion;
        $threeDS->source = (string) $builder->authenticationSource;
//        $threeDS->message_category = (string) $builder->messageCategory; ???
        $threeDS->merchant_contact_url = 'https://enp4qhvjseljg.x.pipedream.net/'; // @TODO
        $order = [
            'time_created_reference' => !empty($builder->orderCreateDate) ?
                (new \DateTime($builder->orderCreateDate))->format('Y-m-d\TH:i:s.u\Z') : null,
            'amount' => StringUtils::toNumeric($builder->amount),
            'currency' => $builder->currency,
            'reference' => $builder->referenceNumber,
            'address_match_indicator' => $builder->isAddressMatchIndicator() ? true : false,
            'gift_card_count' => $builder->giftCardCount,
            'gift_card_currency'=> $builder->giftCardCurrency,
            'gift_card_amount' => $builder->giftCardAmount,
            'delivery_email' => $builder->deliveryEmail,
            'delivery_timeframe' => $builder->deliveryTimeframe,
            'shipping_method' => (string) $builder->shippingMethod,
            'shipping_name_matches_cardholder_name' => $builder->getShippingNameMatchesCardHolderName(),
            'preorder_indicator' => (string) $builder->preOrderIndicator,
            'preorder_availability_date' => !empty($builder->preOrderAvailabilityDate) ?
                $builder->preOrderAvailabilityDate->format('Y-m-d') : null,
            'reorder_indicator' => (string) $builder->reorderIndicator,
            'transaction_type' => $builder->orderTransactionType
        ];

        if (!empty($builder->shippingAddress)) {
            $order['shipping_address'] = [
                'line1' => $builder->shippingAddress->streetAddress1,
                'line2' => $builder->shippingAddress->streetAddress2,
                'line3' => $builder->shippingAddress->streetAddress3,
                'city' => $builder->shippingAddress->city,
                'postal_code' => $builder->shippingAddress->postalCode,
                'state' => $builder->shippingAddress->state,
                'country' => $builder->shippingAddress->countryCode
            ];
        }
        $threeDS->order = $order;
        $threeDS->payment_method = self::setPaymentMethodParam($builder->paymentMethod);
        $threeDS->payer = [
            'reference' => $builder->customerAccountId,
            'account_age' => (string) $builder->accountAgeIndicator,
            'account_creation_date' => !empty($builder->accountCreateDate) ?
                $builder->accountCreateDate->format('Y-m-d') : null,
            'account_change_date' => !empty($builder->accountChangeDate) ?
                $builder->accountChangeDate->format('Y-m-d') : null,
            'account_change_indicator' => (string) $builder->accountChangeIndicator,
            'account_password_change_date' => !empty($builder->passwordChangeDate) ?
                $builder->passwordChangeDate->format('Y-m-d') : null,
            'account_password_change_indicator' => (string) $builder->passwordChangeIndicator,
            'home_phone' => [
                'country_code' => $builder->homeCountryCode,
                'subscriber_number' => $builder->homeNumber
            ],
            'work_phone' => [
                'country_code' => $builder->workCountryCode,
                'subscriber_number' => $builder->workNumber
            ],
            'payment_account_creation_date' => !empty($builder->paymentAccountCreateDate) ?
                $builder->paymentAccountCreateDate->format('Y-m-d') : null,
            'payment_account_age_indicator' => (string) $builder->paymentAgeIndicator,
            'suspicious_account_activity' => $builder->previousSuspiciousActivity,
            'purchases_last_6months_count' => $builder->numberOfPurchasesInLastSixMonths,
            'transactions_last_24hours_count' => $builder->numberOfTransactionsInLast24Hours,
            'transaction_last_year_count' => $builder->numberOfTransactionsInLastYear,
            'provision_attempt_last_24hours_count' => $builder->numberOfAddCardAttemptsInLast24Hours,
            'shipping_address_time_created_reference' => !empty($builder->shippingAddressCreateDate) ?
                $builder->shippingAddressCreateDate->format('Y-m-d') : null,
            'shipping_address_creation_indicator' => (string) $builder->shippingAddressUsageIndicator
        ];

        $threeDS->payer_prior_three_ds_authentication_data = [
            'authentication_method' => (string) $builder->priorAuthenticationMethod,
            'acs_transaction_reference' => $builder->priorAuthenticationTransactionId,
            'authentication_timestamp' => !empty($builder->priorAuthenticationTimestamp) ?
                $builder->priorAuthenticationTimestamp->format('Y-m-d\TH:i:s.u\Z') : null,
            'authentication_data' => $builder->priorAuthenticationData
        ];

        $threeDS->recurring_authorization_data = [
            'max_number_of_instalments' => $builder->maxNumberOfInstallments,
            'frequency' => $builder->recurringAuthorizationFrequency,
            'expiry_date' => $builder->recurringAuthorizationExpiryDate
        ];

        $threeDS->payer_login_data = [
            'authentication_data' => $builder->customerAuthenticationData,
            'authentication_timestamp' => !empty($builder->customerAuthenticationTimestamp) ?
                $builder->customerAuthenticationTimestamp->format('Y-m-d\TH:i:s.u\Z') : null,
            'authentication_type' => (string) $builder->customerAuthenticationMethod
        ];
        if (!empty($builder->browserData)) {
            $threeDS->browser_data = [
                'accept_header' => $builder->browserData->acceptHeader,
                'color_depth' => (string) $builder->browserData->colorDepth,
                'ip' => $builder->browserData->ipAddress,
                'java_enabled' => $builder->browserData->javaEnabled,
                'javascript_enabled' => $builder->browserData->javaScriptEnabled,
                'language' => $builder->browserData->language,
                'screen_height' => $builder->browserData->screenHeight,
                'screen_width' => $builder->browserData->screenWidth,
                'challenge_window_size' => (string) $builder->browserData->challengWindowSize,
                'timezone' => (string) $builder->browserData->timeZone,
                'user_agent' => $builder->browserData->userAgent
            ];
        }

        return $threeDS;
    }

    public static function setPaymentMethodParam($cardData)
    {
        $paymentMethod = new PaymentMethod();
        if ($cardData instanceof ITokenizable && !empty($cardData->token)) {
            $paymentMethod->id = $cardData->token;
            $paymentMethod->name = $cardData->cardHolderName;
        }
        if ($cardData instanceof ICardData) {
            $paymentMethod->card = (object) [
                'number' => $cardData->number,
                'expiry_month' => !empty($cardData->expMonth) ? $cardData->expMonth : '',
                'expiry_year' => !empty($cardData->expYear) ?
                    substr(str_pad($cardData->expYear, 4, '0', STR_PAD_LEFT), 2, 2) : ''
            ];;
        }

        return $paymentMethod;
    }
}