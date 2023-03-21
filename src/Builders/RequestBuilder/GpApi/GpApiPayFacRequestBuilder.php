<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Builders\RequestBuilder\RequestBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\UserType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\PayFac\BankAccountData;
use GlobalPayments\Api\Entities\PayFac\UserPersonalData;
use GlobalPayments\Api\Entities\Person;
use GlobalPayments\Api\Entities\Product;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\CountryUtils;

class GpApiPayFacRequestBuilder implements IRequestBuilder
{
    /**
     * @var PayFacBuilder
     */
    private $builder;

    /***
     * @param PayFacBuilder $builder
     *
     * @return bool
     */
    public static function canProcess($builder)
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
        $requestData = $queryParams = null;
        switch ($builder->transactionType) {
            case TransactionType::CREATE:
                if (TransactionModifier::MERCHANT) {
                    $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT;
                    $verb = 'POST';
                    if (empty($builder->userPersonalData)) {
                        throw new ArgumentException('Merchant data is mandatory!');
                    }
                    $requestData = $this->buildCreateMerchantRequest();
                }
                break;
            case TransactionType::EDIT:
                if (TransactionModifier::MERCHANT) {
                    $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $builder->userReference->userId;
                    $verb = 'PATCH';
                    $requestData = $this->buildEditMerchantRequest();
                }
                break;
            case TransactionType::FETCH:
                if (TransactionModifier::MERCHANT) {
                    $endpoint = GpApiRequest::MERCHANT_MANAGEMENT_ENDPOINT . '/' . $builder->userId;
                    $verb = 'GET';
                }
                break;
            default:
                return '';
        }

        if (empty($endpoint)) {
            throw new ArgumentException('Action not available on this service!');
        }
        return new GpApiRequest($endpoint, $verb, $requestData, $queryParams);
    }

    private function setPaymentMethod()
    {
        if (!empty($this->builder->creditCardInformation)) {
            $cardInfo = [
                'functions' => $this->builder->paymentMethodsFunctions[get_class($this->builder->creditCardInformation)] ?? null,
                'card' => $this->builder->creditCardInformation instanceof CreditCardData ?
                    $this->mapCreditCardInfo($this->builder->creditCardInformation) : null
            ];
        }
        if (!empty($this->builder->bankAccountData)) {
            $bankData = [
                'functions' => $this->builder->paymentMethodsFunctions[get_class($this->builder->bankAccountData)] ?? null,
                'name' => !empty($this->builder->bankAccountData) ? $this->builder->bankAccountData->accountHolderName : null,
                'bank_transfer' => $this->builder->bankAccountData instanceof BankAccountData ?
                    $this->mapBankTransferInfo($this->builder->bankAccountData) : null
            ];
        }
        return [
            $cardInfo ?? null,
            $bankData ?? null
        ];
    }

    private function mapBankTransferInfo(BankAccountData $bankAccountData)
    {
        return [
            'account_holder_type' => $bankAccountData->accountOwnershipType,
            'account_number' => $bankAccountData->accountNumber,
            'account_type' => $bankAccountData->accountType,
            'bank' => [
                'name' => $bankAccountData->bankName,
                'code' => $bankAccountData->routingNumber, //@TODO confirmantion from GP-API team
                'international_code' => '', //@TODO
                'address' => [
                    'line_1' => $bankAccountData->bankAddress->streetAddress1 ?? null,
                    'line_2' => $bankAccountData->bankAddress->streetAddress2 ?? null,
                    'line_3' => $bankAccountData->bankAddress->streetAddress3 ?? null,
                    'city' => $bankAccountData->bankAddress->city ?? null,
                    'postal_code' => $bankAccountData->bankAddress->postalCode ?? null,
                    'state' => $bankAccountData->bankAddress->state ?? null,
                    'country' => !empty($bankAccountData->bankAddress) ?
                        CountryUtils::getCountryCodeByCountry($bankAccountData->bankAddress->countryCode) : '',
                ]
            ]
        ];
    }

    private function mapCreditCardInfo(CreditCardData $creditCardInformation)
    {
        return [
            'name' => $creditCardInformation->cardHolderName,
            'number' => $creditCardInformation->number,
            'expiry_month' => substr($creditCardInformation->expMonth, 2, 2),
            'expiry_year' => substr($creditCardInformation->expYear, 0, 2)
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
            $addresses[] = [
                'line_1' => $address->streetAddress1,
                'line_2' => $address->streetAddress2,
                'city' => $address->city,
                'postal_code' => $address->postalCode,
                'state' => $address->state,
                'country' => CountryUtils::getCountryCodeByCountry($address->countryCode),
                'functions' => [$addressType]
            ];
        }

        return $addresses ?? [];
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
                $personInfo['address'] = [
                    'line_1' => $person->address->streetAddress1,
                    'line_2' => $person->address->streetAddress2,
                    'line_3' => $person->address->streetAddress3,
                    'city' => $person->address->city,
                    'state' => $person->address->state,
                    'postal_code' => $person->address->postalCode,
                    'country' => $person->address->countryCode,
                ];
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
}