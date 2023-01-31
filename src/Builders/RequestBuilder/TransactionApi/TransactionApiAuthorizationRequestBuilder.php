<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\TransactionApi;

use GlobalPayments\Api\Builders\{AuthorizationBuilder, BaseBuilder};
use GlobalPayments\Api\Entities\{IRequestBuilder, PhoneNumber};
use GlobalPayments\Api\Entities\TransactionApi\TransactionApiRequest;
use GlobalPayments\Api\Entities\Enums\{
    PaymentMethodType,
    PaymentMethodUsageMode,
    PhoneNumberType,
    TransactionType,
    Region
};
use GlobalPayments\Api\ServiceConfigs\Gateways\TransactionApiConfig;
use GlobalPayments\Api\Utils\{CountryUtils, StringUtils, AmountUtils};

class TransactionApiAuthorizationRequestBuilder implements IRequestBuilder
{
    /***
     * @param AuthorizationBuilder $builder
     *
     * @return bool
     */
    public static function canProcess($builder)
    {
        return $builder instanceof AuthorizationBuilder;
    }

    /**
     * @param BaseBuilder $builder
     * @param TransactionApiConfig $config
     * @return TransactionApiRequest|string
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $requestData    = null;
        $additionalSlug = "";
        /**
         * @var AuthorizationBuilder $builder
         */

        switch ($builder->transactionType) {
            case TransactionType::SALE:
                $verb = 'POST';
                if (isset($builder->paymentMethod) && $builder->paymentMethod->paymentMethodType == PaymentMethodType::ACH) {
                    $endpoint = TransactionApiRequest::CHECKSALES;
                } else {
                    $endpoint = TransactionApiRequest::CREDITSALE;
                }

                $requestData =  $this->createFromAuthorizationBuilder($builder, $config);
                break;
            case TransactionType::AUTH:
                $endpoint = TransactionApiRequest::CREDITAUTH;
                $verb = 'POST';
                $requestData =  $this->createFromAuthorizationBuilder($builder, $config);
                break;
            case TransactionType::VERIFY:
                $endpoint = TransactionApiRequest::CREDITAUTH;
                $verb = 'POST';
                $requestData =  $this->createFromAuthorizationBuilder($builder, $config);
                break;
            case TransactionType::REFUND:
                if (isset($builder->paymentMethod) && $builder->paymentMethod->paymentMethodType == PaymentMethodType::ACH) {
                    $endpoint = TransactionApiRequest::CHECKREFUND;
                }
                if (isset($builder->paymentMethod) && $builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    $endpoint = TransactionApiRequest::CREDITREFUND;
                }
                $verb = 'POST';
                $requestData =  $this->createFromAuthorizationBuilder($builder, $config, $additionalSlug);
                break;
            default:
                return '';
        }
        return new TransactionApiRequest($endpoint, $verb, $requestData);
    }

    /**
     * @param BaseBuilder $builder
     * @param TransactionApiConfig $config
     * @param String $additionalSlug
     *
     * @return array
     */
    private function createFromAuthorizationBuilder(BaseBuilder $builder, TransactionApiConfig $config = null, String $additionalSlug = "")
    {
        $requestBody = [];
        if (empty($additionalSlug)) {
            $requestBody['reference_id'] = 'REF' . str_shuffle('abcdefg123212');
        }

        if (isset($builder->paymentMethod)) {
            if (isset($builder->paymentMethod) && $builder->paymentMethod->paymentMethodType == PaymentMethodType::ACH) {
                if ($config->country == Region::CA) {
                    if (!empty($builder->paymentMethod->token)) {
                        $requestBody["check"] = [
                            "account_type" => $builder->paymentMethod->accountType,
                            "token" => $builder->paymentMethod->token,
                        ];
                    } else {
                        $requestBody["check"] = [
                            "account_type" => $builder->paymentMethod->accountType,
                            "account_number" => $builder->paymentMethod->accountNumber,
                            "branch_transit_number" => $builder->paymentMethod->branchTransitNumber,
                            "financial_institution_number" => $builder->paymentMethod->financialInstitutionNumber,
                            "check_number" => $builder->paymentMethod->checkNumber,
                        ];
                    }
                } else if ($config->country == Region::US) {
                    if (!empty($builder->paymentMethod->token)) {
                        $requestBody["check"] = [
                            "account_type" => $builder->paymentMethod->accountType,
                            "token" => $builder->paymentMethod->token,
                        ];
                    } else {
                        $requestBody["check"] = [
                            "account_type" => $builder->paymentMethod->accountType,
                            "account_number" => $builder->paymentMethod->accountNumber,
                            "routing_number" => $builder->paymentMethod->routingNumber,
                            "check_number" => $builder->paymentMethod->checkNumber,
                        ];
                    }
                }
            } else if (empty($additionalSlug)) {
                if (!empty($builder->paymentMethod->token)) {
                    if ($builder->paymentMethodUsageMode === PaymentMethodUsageMode::SINGLE) {
                        $requestBody["card"] = [
                            "temporary_token" => $builder->paymentMethod->token
                        ];
                    } else {
                        $requestBody["card"] = [
                            "token" => $builder->paymentMethod->token,
                            "card_security_code" => !empty($builder->paymentMethod->cvn) ? $builder->paymentMethod->cvn : "",
                            "cardholder_name" => !empty($builder->paymentMethod->cardHolderName) ? $builder->paymentMethod->cardHolderName : "",
                        ];
                    }
                } else {
                    $requestBody["card"] = [
                        "card_number" => !empty($builder->paymentMethod->number) ? $builder->paymentMethod->number : "",
                        "card_security_code" => !empty($builder->paymentMethod->cvn) ? $builder->paymentMethod->cvn : "",
                        "cardholder_name" => !empty($builder->paymentMethod->cardHolderName) ? $builder->paymentMethod->cardHolderName : "",
                        "expiry_month" => !empty($builder->paymentMethod->expMonth) ? $builder->paymentMethod->expMonth : "",
                        "expiry_year" => !empty($builder->paymentMethod->expYear) ? $builder->paymentMethod->expYear : "",
                    ];
                }
            }
        }

        if (isset($builder->billingAddress)) {
            $billingAddress = [
                'line1' => $builder->billingAddress->streetAddress1,
                'line2' => !empty($builder->billingAddress->streetAddress2) ? $builder->billingAddress->streetAddress2 : "test",
                'city' => isset($builder->billingAddress->city) ? $builder->billingAddress->city : "",
                'postal_code' => isset($builder->billingAddress->postalCode) ? $builder->billingAddress->postalCode : "",
                'state' => isset($builder->billingAddress->state) ? $builder->billingAddress->state : "",
                'country' => isset($builder->billingAddress->countryCode) ? $builder->billingAddress->countryCode : ""
            ];
        }

        if (isset($builder->shippingAddress)) {
            $shippingAddress = [
                'line1' => isset($builder->shippingAddress->streetAddress1) ? $builder->shippingAddress->streetAddress1 : "",
                'line2' => !empty($builder->shippingAddress->streetAddress2) ? $builder->shippingAddress->streetAddress2 : "",
                'city' => isset($builder->shippingAddress->city) ? $builder->shippingAddress->city : "",
                'postal_code' => isset($builder->shippingAddress->postalCode) ? $builder->shippingAddress->postalCode : "",
                'state' => isset($builder->shippingAddress->state) ? $builder->shippingAddress->state : "",
                'country' => isset($builder->shippingAddress->countryCode) ? $builder->shippingAddress->countryCode : ""
            ];
        }

        list($phoneNumber, $phoneCountryCode) = $this->getPhoneNumber($builder, PhoneNumberType::MOBILE);

        $requestBody["customer"] = [
            "id" => isset($builder->customerData->id) ? $builder->customerData->id : "",
            "title" => isset($builder->customerData->title) ? $builder->customerData->title : "",
            "first_name" => isset($builder->customerData->firstName) ? $builder->customerData->firstName : "",
            "middle_name" => isset($builder->customerData->middleName) ? $builder->customerData->middleName : "",
            "last_name" => isset($builder->customerData->lastName) ? $builder->customerData->lastName : "",
            // "business_name" => $builder->customerData->businessName,
            "email" => isset($builder->customerData->email) ? $builder->customerData->email : "",
            "phone" => $phoneCountryCode . $phoneNumber,
            "billing_address" => isset($billingAddress) ? $billingAddress : ""
        ];

        $requestBody["payment"] = [
            "amount" => isset($builder->amount) ? (string)AmountUtils::transitFormat($builder->amount) : "0.00",
            "invoice_number" => isset($builder->invoiceNumber) ? $builder->invoiceNumber : ""
        ];

        if (empty($additionalSlug)) {
            $requestBody["payment"]["currency_code"] = isset($builder->currency) ?
                CountryUtils::getNumericCodeByCountry($builder->currency) : "";
        }

        if (isset($builder->shippingDate)) {
            $requestBody["shipping"] = [
                "date" => isset($builder->shippingDate) ? $builder->shippingDate : "",
                "address" => isset($shippingAddress) ? $shippingAddress : ""
            ];
        }

        $requestBody["transaction"] = $this->buildTransactionData($builder, $config, $additionalSlug);

        if (
            $builder->transactionType == TransactionType::AUTH
            && !empty($builder->clerkId)
        ) {
            $requestBody["receipt"] = [
                "clerk_id" => $builder->clerkId
            ];
        }

        return $requestBody;
    }

    /**
     * @param BaseBuilder $builder
     * @param TransactionApiConfig $config
     * @param String $additionalSlug
     *
     * @return array
     */
    private function buildTransactionData(BaseBuilder $builder, TransactionApiConfig $config, String $additionalSlug)
    {
        if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::ACH) {
            return $this->buildAchTransactionData($builder, $config, $additionalSlug);
        }

        if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
            return $this->buildCreditCardTransactionData($builder, $config, $additionalSlug);
        }

        return [];
    }

    /**
     * @param BaseBuilder $builder
     * @param TransactionApiConfig $config
     * @param String $additionalSlug
     *
     * @return array
     */
    private function buildAchTransactionData(BaseBuilder $builder, TransactionApiConfig $config, String $additionalSlug)
    {
        if (isset($builder->transactionData)) {

            $transactionData = $builder->transactionData;
            if (empty($additionalSlug)) {
                $transactionObjectCountry = [
                    "country_code" => !empty($config->country) ? CountryUtils::getNumericCodeByCountry($config->country) : ""
                ];
            }

            $transactionObject = [
                "ecommerce_indicator" => isset($transactionData->ecommerceIndicator) ? $transactionData->ecommerceIndicator : "",
                "language" => isset($transactionData->language) ? $transactionData->language : "",
                "soft_descriptor" => isset($transactionData->softDescriptor) ? $transactionData->softDescriptor : ""
            ];

            if (!empty($transactionObjectCountry)) {
                $transactionObject = array_merge($transactionObjectCountry, $transactionObject);
            }

            if (isset($builder->paymentMethod) && $builder->paymentMethod->paymentMethodType == PaymentMethodType::ACH) {
                if ($config->country == Region::CA) {
                    $transactionObject["payment_purpose_code"] = isset($builder->paymentPurposeCode)
                        ? $builder->paymentPurposeCode : "";
                } else if ($config->country == Region::US) {
                    $transactionObject["entry_class"] = isset($builder->entryClass)
                        ? $builder->entryClass : "";
                } else {
                    $transactionObject["processing_indicators"] = [
                        "address_verification_service" => isset($transactionData->addressVerificationService)
                            ? $transactionData->addressVerificationService : false,
                        "create_token" => isset($builder->requestMultiUseToken)
                            ? $builder->requestMultiUseToken : false,
                        "generate_receipt" => isset($builder->transactionData)
                            ? $builder->transactionData->generateReceipt : null,
                        "partial_approval" => isset($builder->allowPartialAuth)
                            ? $builder->allowPartialAuth : false

                    ];
                }

                if (isset($builder->requestMultiUseToken)) {
                    $transactionObject["processing_indicators"] = [
                        "create_token" => isset($builder->requestMultiUseToken)
                            ? $builder->requestMultiUseToken : false,
                    ];
                }
            }
        }

        return $transactionObject;
    }

    /**
     * @param BaseBuilder $builder
     * @param TransactionApiConfig $config
     * @param String $additionalSlug
     *
     * @return array
     */
    private function buildCreditCardTransactionData(BaseBuilder $builder, TransactionApiConfig $config, String $additionalSlug)
    {
        $transactionData = [];
        if (isset($builder->transactionData)) {
            $transactionData = $builder->transactionData;
        }

        if (empty($additionalSlug)) {
            $transactionObjectCountry = [
                "country_code" => !empty($config->country) ? CountryUtils::getNumericCodeByCountry($config->country) : ""
            ];
        }

        $transactionObject = [
            "ecommerce_indicator" => null,
            "language" => isset($transactionData->language) ? $transactionData->language : "",
            "soft_descriptor" => isset($transactionData->softDescriptor) ? $transactionData->softDescriptor : ""
        ];

        if (!empty($transactionObjectCountry)) {
            $transactionObject = array_merge($transactionObjectCountry, $transactionObject);
        }

        if (
            $builder->transactionType == TransactionType::AUTH
            || $builder->transactionType == TransactionType::VERIFY
        ) {
            $transactionObject["processing_indicators"] = [
                "address_verification_service" => isset($transactionData->addressVerificationService)
                    ? $transactionData->addressVerificationService : false,
                "create_token" => isset($builder->requestMultiUseToken)
                    ? $builder->requestMultiUseToken : false,
                "partial_approval" => isset($builder->allowPartialAuth)
                    ? $builder->allowPartialAuth : false
            ];
        } else if ($builder->transactionType == TransactionType::SALE) {
            $transactionObject["processing_indicators"] = [
                "partial_approval" => isset($builder->allowPartialAuth)
                    ? $builder->allowPartialAuth : false,
                "create_token" => isset($builder->requestMultiUseToken)
                    ? $builder->requestMultiUseToken : false
            ];
        }

        if (isset($builder->allowDuplicates) && $builder->allowDuplicates == TRUE) {
            $transactionObject["processing_indicators"]["allow_duplicate"] = true;
        }

        if (
            isset($builder->transactionData)
            && isset($builder->transactionData->generateReceipt)
            && $builder->transactionData->generateReceipt == TRUE
        ) {
            $transactionObject["processing_indicators"]["generate_receipt"] = true;
        }

        return $transactionObject;
    }

    /**
     * You can have the phone number set on customerData or directly to the builder
     *
     * @param AuthorizationBuilder $builder
     * @param string $type
     *
     * @return array
     */
    private function getPhoneNumber(AuthorizationBuilder $builder, String $type)
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
}
