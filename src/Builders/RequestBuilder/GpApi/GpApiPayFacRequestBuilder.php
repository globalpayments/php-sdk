<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\RequestBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\UserType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\PayFac\BankAccountData;
use GlobalPayments\Api\Entities\PayFac\UserPersonalData;
use GlobalPayments\Api\Entities\Person;
use GlobalPayments\Api\Entities\Product;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\ProtectSensitiveData;
use GlobalPayments\Api\Utils\StringUtils;

class GpApiPayFacRequestBuilder implements IRequestBuilder
{
    /**
     * @var PayFacBuilder
     */
    private $builder;
    private GpApiConfig $config;

    private array $maskedValues = [];

    /***
     * @param PayFacBuilder $builder
     *
     * @return bool
     */
    public static function canProcess($builder = null)
    {
        if ($builder instanceof PayFacBuilder) {
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
        /** @var PayFacBuilder $builder */
        $this->builder = $builder;
        $this->config = $config;
        $requestData = $queryParams = null;
        $this->validate($builder->transactionType, $builder);
        switch ($builder->transactionType) {
            case TransactionType::CREATE:
                if ($builder->transactionModifier == TransactionModifier::MERCHANT) {
                    $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT;
                    $verb = 'POST';
                    if (empty($builder->userPersonalData)) {
                        throw new ArgumentException('Merchant data is mandatory!');
                    }
                    $requestData = $this->buildCreateMerchantRequest();
                }
                break;
            case TransactionType::FETCH:
                if ($builder->transactionModifier == TransactionModifier::MERCHANT) {
                    $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $builder->userId;
                    $verb = 'GET';
                }
                break;
            case TransactionType::EDIT:
                $verb = 'PATCH';
                if ($builder->transactionModifier == TransactionModifier::MERCHANT) {
                    $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $builder->userReference->userId;
                    $requestData = $this->buildEditMerchantRequest();
                } else {
                    $endpoint = '';
                    if (!empty($builder->userReference->userId)) {
                        $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $builder->userReference->userId;
                    }
                    $endpoint .= GpApiRequest::ACCOUNTS_ENDPOINT . '/' . $builder->accountNumber;
                    $requestData['payer'] = [
                        'payment_method' =>  [
                            'name' => $this->builder->creditCardInformation instanceof CreditCardData ?
                                $this->builder->creditCardInformation->cardHolderName : null,
                            'card' => $this->builder->creditCardInformation instanceof CreditCardData ?
                                $this->mapCreditCardInfo($this->builder->creditCardInformation) : null
                            ],
                        'billing_address' =>
                            !empty($this->builder->addresses) && $this->builder->addresses->offsetExists(AddressType::BILLING) ?
                            $this->mapAddress($this->builder->addresses->get(AddressType::BILLING), 'alpha2') : null
                    ];
                }
                break;
            case TransactionType::ADD_FUNDS:
                $verb = 'POST';
                $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $builder->userReference->userId . '/settlement/funds';
                $requestData = [
                    'account_id' => $builder->accountNumber,
                    'type' => !empty($builder->paymentMethodType) ?
                        PaymentMethodType::getKey($builder->paymentMethodType) : null,
                    'amount' => StringUtils::toNumeric($builder->amount),
                    'currency' => $builder->currency ?? null,
                    'payment_method' => !empty($builder->paymentMethodName) ?
                        PaymentMethodName::getKey($builder->paymentMethodName) : null,
                    'reference' => $builder->clientTransactionId ?? GenerationUtils::getGuid(),
                    ];
                break;
            case TransactionType::UPLOAD_DOCUMENT:
                $verb = 'POST';
                $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $builder->userReference->userId .'/documents';
                $requestData = [
                    'function' => $builder->uploadDocumentData->documentCategory ?? null,
                    'b64_content' => $builder->uploadDocumentData->b64_content ?? null,
                    'format' => $builder->uploadDocumentData->documentFormat ?? null
                ];
                break;
            default:
                break;
        }

        if (empty($endpoint)) {
            throw new ArgumentException('Action not available on this service!');
        }
        GpApiRequest::$maskedValues = $this->maskedValues;

        return new GpApiRequest($endpoint, $verb, $requestData, $queryParams);
    }

    private function setPaymentMethod()
    {
        $paymentMethods = [];
        if (!empty($this->builder->creditCardInformation)) {
            $cardInfo = [
                'functions' => $this->builder->paymentMethodsFunctions[get_class($this->builder->creditCardInformation)] ?? null,
                'card' => $this->builder->creditCardInformation instanceof CreditCardData ?
                    $this->mapCreditCardInfo($this->builder->creditCardInformation) : null
            ];
            array_push($paymentMethods, $cardInfo);
        }
        if (!empty($this->builder->bankAccountData)) {
            $bankData = [
                'functions' => [$this->builder->paymentMethodsFunctions[get_class($this->builder->bankAccountData)] ?? null],
                'name' => !empty($this->builder->bankAccountData) ? $this->builder->bankAccountData->accountHolderName : null,
                'bank_transfer' => $this->builder->bankAccountData instanceof BankAccountData ?
                    $this->mapBankTransferInfo($this->builder->bankAccountData) : null,
                'notifications' => [
                    'status_url' => $this->config->methodNotificationUrl
                ]
            ];
            array_push($paymentMethods, $bankData);
        }

        return $paymentMethods;
    }

    private function mapBankTransferInfo(BankAccountData $bankAccountData)
    {
        return [
            'account_holder_type' => $bankAccountData->accountOwnershipType,
            'account_number' => $bankAccountData->accountNumber,
            'account_type' => EnumMapping::mapAccountType(GatewayProvider::GP_API ,$bankAccountData->accountType),
            'bank' => [
                'name' => $bankAccountData->bankName,
                'code' => $bankAccountData->routingNumber, //@TODO confirmantion from GP-API team
                'international_code' => '', //@TODO
                'address' => !empty($bankAccountData->bankAddress) ?
                    $this->mapAddress($bankAccountData->bankAddress, 'alpha2') : null,
            ]
        ];
    }

    private function mapCreditCardInfo(CreditCardData $creditCardInformation)
    {
        $this->maskedValues = ProtectSensitiveData::hideValues(
            [
                'payer.payment_method.card.expiry_month' => $creditCardInformation->expMonth,
                'payer.payment_method.card.expiry_year' => $creditCardInformation->expYear,
                'payer.payment_method.card.cvv' => $creditCardInformation->cvn
            ]
        );
        $this->maskedValues = ProtectSensitiveData::hideValue('payer.payment_method.card.number', $creditCardInformation->number, 4, 6);

        return [
            'number' => $creditCardInformation->number,
            'expiry_month' => !empty($creditCardInformation->expMonth) ?
                substr($creditCardInformation->expMonth, 0, 2) : null,
            'expiry_year' => !empty($creditCardInformation->expYear) ?
                substr($creditCardInformation->expYear, 2, 2) : null,
            'cvv' => $creditCardInformation->cvn ?? null
        ];
    }

    private function setProductList($productData)
    {
        /** @var Product $product */
        foreach ($productData as $product) {
            if (!$product instanceof Product) {
                continue;
            }
            $deviceInfo = null;
            if (strpos($product->productId, '_CP-') !== false) {
                $deviceInfo = [
                    'quantity' => 1,
                ];
            }
            $products[] = [
                'device' => $deviceInfo ?? null,
                'id' => $product->productId
            ];
        }

        return $products ?? [];
    }

    private function setAddressList()
    {
        /** @var UserPersonalData $merchantData */
        $merchantData = $this->builder->userPersonalData;
        $addressList = [];
        if (!empty($merchantData->userAddress->streetAddress1)) {
            $addressList[AddressType::BUSINESS] = $merchantData->userAddress;
        }
        if (!empty($merchantData->mailingAddress->streetAddress1)) {
            $addressList[AddressType::SHIPPING] = $merchantData->mailingAddress;
        }
        /** @var Address $address */
        foreach ($addressList as $addressType => $address) {
            $addresses[] = $this->mapAddress($address, 'alpha2') + ['functions' => [$addressType]];
        }

        return $addresses ?? [];
    }

    private function mapAddress(Address $address, $countryCodeType = null)
    {
        switch ($countryCodeType)
        {
            case 'alpha2':
                $countryCode = CountryUtils::getCountryCodeByCountry($address->countryCode);
                break;
            default:
                $countryCode = $address->countryCode;
        }
        return [
            'line_1' => $address->streetAddress1,
            'line_2' => $address->streetAddress2,
            'line_3' => $address->streetAddress3,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postalCode,
            'country' => $countryCode,
        ];
    }

    /**
     * Request body for POST /merchants
     *
     * @return array
     */
    private function buildCreateMerchantRequest()
    {
        return array_merge(
            $this->setMerchantInfo(),
            [
                'pricing_profile' => $this->builder->userPersonalData->tier,
                'description' => $this->builder->description,
                'type' => $this->builder->userPersonalData->type,
                'addresses' => $this->setAddressList(),
                'payment_processing_statistics' => $this->setPaymentStatistics(),
                'payment_methods' => $this->setPaymentMethod(),
                'persons' => $this->setPersonList(),
                'products' => !empty($this->builder->productData) ? $this->setProductList($this->builder->productData) : null,
            ]
        );
    }

    private function setMerchantInfo()
    {
        if (empty($this->builder->userPersonalData)) {
            return [];
        }
        /** @var UserPersonalData $merchantData */
        $merchantData = $this->builder->userPersonalData;
        return [
            'name' => $merchantData->userName,
            'legal_name' => $merchantData->legalName,
            'dba' => $merchantData->dba,
            'merchant_category_code' => $merchantData->merchantCategoryCode,
            'website' => $merchantData->website,
            'currency' => $merchantData->currencyCode,
            'tax_id_reference' => $merchantData->taxIdReference,
            'notification_email' => $merchantData->notificationEmail,
            'status' => $this->builder->userReference->userStatus ?? null,
            'notifications' => [
                'status_url' => $merchantData->notificationStatusUrl
            ]
        ];
    }

    private function setPaymentStatistics()
    {
        if (empty($this->builder->paymentStatistics)) {
            return [];
        }
        return [
            'total_monthly_sales_amount' => $this->builder->paymentStatistics->totalMonthlySalesAmount,
            'average_ticket_sales_amount' => $this->builder->paymentStatistics->averageTicketSalesAmount,
            'highest_ticket_sales_amount' => $this->builder->paymentStatistics->highestTicketSalesAmount
        ];
    }

    private function setPersonList()
    {
        $persons = [];
        if (empty($this->builder->personsData)) {
            return $persons;
        }
        /** @var Person $person */
        foreach ($this->builder->personsData as $person) {
            $personInfo = [
                'functions' => [$person->functions],
                'first_name' => $person->firstName,
                'middle_name' => $person->middleName,
                'last_name' => $person->lastName,
                'email' => $person->email,
                'date_of_birth' => !empty($person->dateOfBirth) ?
                    (new \DateTime($person->dateOfBirth))->format('Y-m-d') : null,
                'national_id_reference' => $person->nationalIdReference,
                'equity_percentage' => $person->equityPercentage,
                'job_title' => $person->jobTitle
            ];
            if (!empty($person->address)) {
                $personInfo['address'] = self::mapAddress($person->address);
            }
            if (!empty($person->homePhone)) {
                $personInfo['contact_phone'] = [
                    'country_code' => $person->homePhone->countryCode,
                    'subscriber_number' => $person->homePhone->number,
                ];
            }
            if (!empty($person->workPhone)) {
                $personInfo['work_phone'] = [
                    'country_code' => $person->workPhone->countryCode,
                    'subscriber_number' => $person->workPhone->number,
                ];
            }
            $persons[] = $personInfo;
        }

        return $persons;
    }

    /**
     * Request body for PATCH /merchants/{id}
     *
     * @return array
     */
    private function buildEditMerchantRequest()
    {
        $requestBody = $this->setMerchantInfo();
        return array_merge(
            $requestBody,
            [
                'description' => $this->builder->description,
                'status_change_reason' => $this->builder->statusChangeReason,
                'addresses' => $this->setAddressList(),
                'persons' => $this->setPersonList(),
                'payment_processing_statistics' => $this->setPaymentStatistics(),
                'payment_methods' => $this->setPaymentMethod(),
            ]
        );
    }

    public function buildRequestFromJson($jsonRequest, $config)
    {
        // TODO: Implement buildRequestFromJson() method.
    }

    public function validate($transactionType, PayFacBuilder $builder)
    {
        $errorMsg = "";
        switch ($transactionType)
        {
            case TransactionType::ADD_FUNDS:
                if (empty($this->config->merchantId) && empty($builder->userReference->userId)) {
                    $errorMsg = "property userId or config merchantId cannot be null for this transactionType";
                }
                break;
            default:
                break;
        }

        if (!empty($errorMsg)) {
            throw new GatewayException($errorMsg);
        }
    }
}