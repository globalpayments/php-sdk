<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\{
    AuthorizationBuilder,
    ManagementBuilder,
    RecurringBuilder,
    ReportBuilder,
    Secure3dBuilder
};
use GlobalPayments\Api\Builders\RequestBuilder\RequestBuilderFactory;
use GlobalPayments\Api\Entities\{Address, Customer, IRequestBuilder, Request, Schedule, Transaction};
use GlobalPayments\Api\Entities\Enums\{GatewayProvider, TransactionType, FraudFilterMode, Secure3dVersion};
use GlobalPayments\Api\Entities\Exceptions\{
    BuilderException,
    GatewayException,
    UnsupportedTransactionException,
    ApiException
};
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\Mapping\GpEcomMapping;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\PaymentMethods\{RecurringPaymentMethod, BankPayment};
use GlobalPayments\Api\Utils\{StringUtils, CountryUtils, GenerationUtils};

class GpEcomConnector extends XmlGateway implements IPaymentGateway, IRecurringService, ISecure3dProvider
{
    /** @var bool  */
    public $supportsHostedPayments = true;

    /** @var bool  */
    public $supportsRetrieval = true;

    /** @var bool  */
    public $supportsUpdatePaymentDetails = true;

    /** @var HostedPaymentConfig */
    public $hostedPaymentConfig;

    /** @var array  */
    private $serializeData = [];

    /** @var GpEcomConfig */
    private $config;

    public function __construct(GpEcomConfig $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    public function supportsOpenBanking() : bool
    {
        return true;
    }

    /** @return Secure3dVersion */
    public function getVersion()
    {
        return Secure3dVersion::ONE;
    }

    /**
     * {@inheritdoc}
     *
     * @param AuthorizationBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function processAuthorization(AuthorizationBuilder $builder)
    {
        $response = $this->executeProcess($builder);
        $transactionType = GpEcomMapping::mapAuthRequestType($builder);
        $acceptedResponseCodes = $this->mapAcceptedCodes($transactionType);

        return GpEcomMapping::mapResponse($response, $acceptedResponseCodes);
    }

    public function processReport(ReportBuilder $builder)
    {
        $response = $this->executeProcess($builder);

        return GpEcomMapping::mapReportResponse($response, $builder->reportType);
    }

    public function processRecurring(RecurringBuilder $builder)
    {
        $response = $this->executeProcess($builder);

        if (
            !empty($response->sha1hash) &&
            (string)$response->sha1hash !== $this->checkHashResponse($response, $builder->entity)
        ) {
            throw new ApiException(sprintf('Unexpected shahash response: %s', (string)$response->sha1hash));
        }

        if (
            $builder->entity instanceof Schedule &&
            (
                $builder->transactionType === TransactionType::FETCH ||
                $builder->transactionType === TransactionType::SEARCH
            )
        ) {
            return GpEcomMapping::mapScheduleReport($response, $builder->transactionType);
        }

        return GpEcomMapping::mapRecurringEntityResponse($response, $builder->entity);
    }

    /**
     * {@inheritdoc}
     *
     * @param ManagementBuilder $builder The transaction's builder
     *
     * @return Transaction
     *
     *
     */
    public function manageTransaction(ManagementBuilder $builder)
    {
        $response = $this->executeProcess($builder);
        $transactionType = GpEcomMapping::mapManageRequestType($builder);

        return GpEcomMapping::mapResponse($response, $this->mapAcceptedCodes($transactionType));
    }

    /**
     * @param $builder
     * @return string
     *
     * @throws ApiException
     * @throws GatewayException
     */
    private function executeProcess($builder)
    {
        $processFactory = new RequestBuilderFactory();

        /**
         * @var IRequestBuilder $requestBuilder
         */
        $requestBuilder = $processFactory->getRequestBuilder($builder, $this->config->gatewayProvider);
        if (empty($requestBuilder)) {
            throw new ApiException("Request builder not found!");
        }

        /**
         * @var Request $request
         */
        $request =  $requestBuilder->buildRequest($builder, $this->config);

        if (empty($request)) {
            throw new ApiException("Request was not generated!");
        }

        $response = $this->doTransaction($request->requestBody);

        return $this->xml2object($response);
    }

    /**
     * Converts a XML string to a simple object for use,
     * removing extra nodes that are not necessary for
     * handling the response
     *
     * @param string $xml Response XML from the gateway
     *
     * @return SimpleXMLElement
     */
    protected function xml2object($xml)
    {
        if (is_object($xml) && $xml instanceof \SimpleXMLElement) {
            return $xml;
        }

        $envelope = simplexml_load_string(
            $xml,
            'SimpleXMLElement'
        );

        return $envelope;
    }

    /**
     * @return Transaction
     */
    public function processSecure3d(Secure3dBuilder $builder)
    {
        throw new BuilderException(sprintf('3D Secure %s is no longer supported by %s',Secure3dVersion::ONE, GatewayProvider::GP_ECOM));
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        // check for hpp config
        if ($this->hostedPaymentConfig === null) {
            throw new ApiException("Hosted configuration missing, Please check you configuration.");
        }

        // check for right transaction types
        if ($builder->transactionType !== TransactionType::SALE
            && $builder->transactionType !== TransactionType::AUTH
            && $builder->transactionType !== TransactionType::VERIFY
        ) {
            throw new UnsupportedTransactionException("Only Charge and Authorize are supported through HPP.");
        }

        if ($builder->paymentMethod instanceof BankPayment &&
            $builder->transactionType !== TransactionType::SALE) {
            throw new UnsupportedTransactionException("Only Charge is supported for Bank Payment through HPP.");
        }

        $orderId = isset($builder->orderId) ? $builder->orderId : GenerationUtils::generateOrderId();
        $timestamp = isset($builder->timestamp) ? $builder->timestamp : GenerationUtils::generateTimestamp();
        $amount = preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount));

        $this->setSerializeData('MERCHANT_ID', $this->config->merchantId);
        $this->setSerializeData('ACCOUNT', $this->config->accountId);
        $this->setSerializeData('HPP_CHANNEL', $this->config->channel);
        $this->setSerializeData('ORDER_ID', $orderId);
        if ($builder->amount !== null) {
            $this->setSerializeData('AMOUNT', $amount);
        }
        $this->setSerializeData('CURRENCY', $builder->currency);
        $this->setSerializeData('TIMESTAMP', $timestamp);

        $this->setSerializeData(
            'AUTO_SETTLE_FLAG',
            ($builder->transactionType == TransactionType::SALE) ? "1" : "0"
        );

        if (
            !empty($builder->hostedPaymentData->bankPayment) &&
            $builder->hostedPaymentData->bankPayment instanceof BankPayment
        ) {
            $this->buildOpenBankingHppRequest($builder);
        }

        $this->setSerializeData('COMMENT1', $builder->description);

        if (isset($this->hostedPaymentConfig->requestTransactionStabilityScore)) {
            $this->serializeData["RETURN_TSS"] =
                $this->hostedPaymentConfig->requestTransactionStabilityScore ? "1" : "0";
        }
        if (isset($this->hostedPaymentConfig->directCurrencyConversionEnabled)) {
            $this->serializeData["DCC_ENABLE"] =
                $this->hostedPaymentConfig->directCurrencyConversionEnabled ? "1" : "0";
        }

        if (!empty($builder->hostedPaymentData)) {
            $hostedPaymentData = $builder->hostedPaymentData;
            $this->setSerializeData('CUST_NUM', $builder->hostedPaymentData->customerNumber);

            if (!empty($this->hostedPaymentConfig->displaySavedCards) &&
                !empty($builder->hostedPaymentData->customerKey)) {
                $this->setSerializeData('HPP_SELECT_STORED_CARD', $builder->hostedPaymentData->customerKey);
            }

            if (isset($builder->hostedPaymentData->offerToSaveCard)) {
                $this->setSerializeData(
                    'OFFER_SAVE_CARD',
                    $builder->hostedPaymentData->offerToSaveCard ? "1" : "0"
                );
            }
            if (isset($builder->hostedPaymentData->customerExists)) {
                $this->setSerializeData(
                    'PAYER_EXIST',
                    $builder->hostedPaymentData->customerExists ? "1" : "0"
                );
            }
            if (isset($builder->hostedPaymentData->customerKey)) {
                $this->setSerializeData('PAYER_REF', $builder->hostedPaymentData->customerKey);
            }
            if (isset($builder->hostedPaymentData->paymentKey)) {
                $this->setSerializeData('PMT_REF', $builder->hostedPaymentData->paymentKey);
            }
            if (isset($builder->hostedPaymentData->productId)) {
                $this->setSerializeData('PROD_ID', $builder->hostedPaymentData->productId);
            }
            // APMs Fields
            if (!empty($hostedPaymentData->customerCountry)) {
                $this->setSerializeData('HPP_CUSTOMER_COUNTRY', $hostedPaymentData->customerCountry);
            }
            if (!empty($hostedPaymentData->customerFirstName)) {
                $this->setSerializeData('HPP_CUSTOMER_FIRSTNAME', $hostedPaymentData->customerFirstName);
            }
            if (!empty($hostedPaymentData->customerLastName)) {
                $this->setSerializeData('HPP_CUSTOMER_LASTNAME', $hostedPaymentData->customerLastName);
            }
            if (!empty($hostedPaymentData->customerFirstName) && !empty($hostedPaymentData->customerLastName)) {
                $this->setSerializeData(
                    'HPP_NAME',
                    $hostedPaymentData->customerFirstName . ' ' . $hostedPaymentData->customerLastName
                );
            }
            if (!empty($hostedPaymentData->merchantResponseUrl)) {
                $this->setSerializeData('MERCHANT_RESPONSE_URL', $hostedPaymentData->merchantResponseUrl);
            }
            if (!empty($hostedPaymentData->transactionStatusUrl)) {
                $this->setSerializeData('HPP_TX_STATUS_URL', $hostedPaymentData->transactionStatusUrl);
            }
            if (!empty($hostedPaymentData->presetPaymentMethods)) {
                $this->setSerializeData('PM_METHODS', implode( '|', $hostedPaymentData->presetPaymentMethods));
            }
            // end APMs Fields
        } elseif (isset($builder->customerId)) {
            $this->setSerializeData('CUST_NUM', $builder->customerId);
        }
        if (!empty($builder->shippingAddress)) {

            $countryCode = CountryUtils::getCountryCodeByCountry($builder->shippingAddress->country);
            $shippingCode = $this->generateCode($builder->shippingAddress);

            // Fraud values
            $this->setSerializeData('SHIPPING_CODE', $shippingCode);
            $this->setSerializeData('SHIPPING_CO', $countryCode);

            // 3DS 2.0 values
            $this->setSerializeData('HPP_SHIPPING_STREET1', $builder->shippingAddress->streetAddress1);
            $this->setSerializeData('HPP_SHIPPING_STREET2', $builder->shippingAddress->streetAddress2);
            $this->setSerializeData('HPP_SHIPPING_STREET3', $builder->shippingAddress->streetAddress3);
            $this->setSerializeData('HPP_SHIPPING_CITY', $builder->shippingAddress->city);
            $this->setSerializeData('HPP_SHIPPING_STATE', $builder->shippingAddress->state);
            $this->setSerializeData('HPP_SHIPPING_POSTALCODE', $builder->shippingAddress->postalCode);
            $this->setSerializeData('HPP_SHIPPING_COUNTRY', CountryUtils::getNumericCodeByCountry($builder->shippingAddress->country));
        }
        if (!empty($builder->billingAddress)) {
            $countryCode = CountryUtils::getCountryCodeByCountry($builder->billingAddress->country);
            $billingCode = $this->generateCode($builder->billingAddress);
            // Fraud values
            $this->setSerializeData('BILLING_CODE', $billingCode);
            $this->setSerializeData('BILLING_CO', $countryCode);

            // 3DS 2.0 values
            $this->setSerializeData('HPP_BILLING_STREET1', $builder->billingAddress->streetAddress1);
            $this->setSerializeData('HPP_BILLING_STREET2', $builder->billingAddress->streetAddress2);
            $this->setSerializeData('HPP_BILLING_STREET3', $builder->billingAddress->streetAddress3);
            $this->setSerializeData('HPP_BILLING_CITY', $builder->billingAddress->city);
            $this->setSerializeData('HPP_BILLING_STATE', $builder->billingAddress->state);
            $this->setSerializeData('HPP_BILLING_POSTALCODE', $builder->billingAddress->postalCode);
            $this->setSerializeData(
                'HPP_BILLING_COUNTRY',
                CountryUtils::getNumericCodeByCountry($builder->billingAddress->country)
            );
        }

        if (!empty($builder->homePhone)) {
            $this->setSerializeData('HPP_CUSTOMER_PHONENUMBER_HOME',
                StringUtils::validateToNumber($builder->homePhone->countryCode) . '|' .
                StringUtils::validateToNumber($builder->homePhone->number));
        }

        if (!empty($builder->workPhone)) {
            $this->setSerializeData('HPP_CUSTOMER_PHONENUMBER_WORK',
                StringUtils::validateToNumber($builder->workPhone->countryCode) . '|' .
                StringUtils::validateToNumber($builder->workPhone->number));
        }

        $this->setSerializeData('VAR_REF', $builder->clientTransactionId);
        $this->setSerializeData('HPP_LANG', $this->hostedPaymentConfig->language);
        $this->setSerializeData('MERCHANT_RESPONSE_URL', $this->hostedPaymentConfig->responseUrl);
        $this->setSerializeData('CARD_PAYMENT_BUTTON', $this->hostedPaymentConfig->paymentButtonText);
        if (!empty($builder->hostedPaymentData)) {
            $hostedPaymentData = $builder->hostedPaymentData;
            $this->setSerializeData('HPP_CUSTOMER_EMAIL', $hostedPaymentData->customerEmail);
            $this->setSerializeData('HPP_CUSTOMER_PHONENUMBER_MOBILE', $hostedPaymentData->customerPhoneMobile);
            $this->setSerializeData('HPP_PHONE', $hostedPaymentData->customerPhoneMobile);
            $this->setSerializeData('HPP_CHALLENGE_REQUEST_INDICATOR', $hostedPaymentData->challengeRequest);
            $this->setSerializeData('HPP_ENABLE_EXEMPTION_OPTIMIZATION', $hostedPaymentData->enableExemptionOptimization);
            if (isset($hostedPaymentData->addressesMatch)) {
                $this->setSerializeData('HPP_ADDRESS_MATCH_INDICATOR', $hostedPaymentData->addressesMatch ? 'TRUE' : 'FALSE');
            }
            if (!empty($hostedPaymentData->supplementaryData)) {
                $this->serializeSupplementaryData($hostedPaymentData->supplementaryData);
            }

            if (isset($hostedPaymentData->addressCapture)) {
                $this->setSerializeData('HPP_CAPTURE_ADDRESS', $hostedPaymentData->addressCapture == true);
            }
            if (isset($hostedPaymentData->notReturnAddress)) {
                $this->setSerializeData('HPP_DO_NOT_RETURN_ADDRESS', $hostedPaymentData->notReturnAddress == true);
            }
        }
        if (isset($this->hostedPaymentConfig->cardStorageEnabled)) {
            $this->setSerializeData('CARD_STORAGE_ENABLE', $this->hostedPaymentConfig->cardStorageEnabled ? '1' : '0');
        }
        if ($builder->transactionType === TransactionType::VERIFY) {
            $this->setSerializeData(
                'VALIDATE_CARD_ONLY',
                $builder->transactionType === TransactionType::VERIFY ? '1' : '0'
            );
        }
        if ($this->hostedPaymentConfig->fraudFilterMode != FraudFilterMode::NONE) {
            $this->setSerializeData('HPP_FRAUDFILTER_MODE', $this->hostedPaymentConfig->fraudFilterMode);
            if ($this->hostedPaymentConfig->fraudFilterMode !== FraudFilterMode::NONE && !empty($this->hostedPaymentConfig->fraudFilterRules)) {
                foreach ($this->hostedPaymentConfig->fraudFilterRules->rules as $fraudRule) {
                    $this->setSerializeData(
                        'HPP_FRAUDFILTER_RULE_' . $fraudRule->key,
                        $fraudRule->mode
                    );
                }
            }
        }

        if ($builder->recurringType !== null || $builder->recurringSequence !== null) {
            $this->setSerializeData('RECURRING_TYPE', strtolower($builder->recurringType));
            $this->setSerializeData('RECURRING_SEQUENCE', strtolower($builder->recurringSequence));
        }
        if (isset($this->hostedPaymentConfig->version)) {
            $this->setSerializeData('HPP_VERSION', $this->hostedPaymentConfig->version);
        }
        if (isset($this->hostedPaymentConfig->postDimensions)) {
            $this->setSerializeData('HPP_POST_DIMENSIONS', $this->hostedPaymentConfig->postDimensions);
        }
        if (isset($this->hostedPaymentConfig->postResponse)) {
            $this->setSerializeData('HPP_POST_RESPONSE', $this->hostedPaymentConfig->postResponse);
        }
        if (!empty($builder->supplementaryData)) {
            $this->serializeSupplementaryData($builder->supplementaryData);
        }

        $toHash = [
            $timestamp,
            $this->config->merchantId,
            $orderId,
            ($builder->amount !== null) ? preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)) : null,
            $builder->currency,
        ];

        if ($this->hostedPaymentConfig->cardStorageEnabled
            || ($builder->hostedPaymentData != null
                && $builder->hostedPaymentData->offerToSaveCard)
            || $this->hostedPaymentConfig->displaySavedCards
        ) {
            $toHash[] = ($builder->hostedPaymentData->customerKey !== null) ?
                $builder->hostedPaymentData->customerKey :
                null;
            $toHash[] = ($builder->hostedPaymentData->paymentKey !== null) ?
                $builder->hostedPaymentData->paymentKey :
                null;
        }

        if (
            !empty($this->hostedPaymentConfig->fraudFilterMode) &&
            $this->hostedPaymentConfig->fraudFilterMode !== FraudFilterMode::NONE
        ) {
            $toHash[] = $this->hostedPaymentConfig->fraudFilterMode;
        }

        if (
            !empty($builder->hostedPaymentData->bankPayment) &&
            $builder->hostedPaymentData->bankPayment instanceof BankPayment
        ) {
            $bankPayment = $builder->hostedPaymentData->bankPayment;
            $toHash = array_merge($toHash, [
                !empty($bankPayment->sortCode) ? $bankPayment->sortCode : '',
                !empty($bankPayment->accountNumber) ? $bankPayment->accountNumber : '',
                !empty($bankPayment->iban) ? $bankPayment->iban : ''
            ]);
        }

        if (!empty($builder->dynamicDescriptor)) {
            $this->serializeData["CHARGE_DESCRIPTION"] = $builder->dynamicDescriptor;
        }

        list($tagHashName, $tagHashValue) = $this->mapShaHash(implode('.', $toHash));
        $this->setSerializeData($tagHashName, $tagHashValue);

        return GenerationUtils::convertArrayToJson($this->serializeData, $this->hostedPaymentConfig->version);
    }

    private function checkHashResponse($response, $entity)
    {
        switch (get_class($entity)) {
            case Schedule::class:
                $hash = GenerationUtils::generateHash(
                    $this->config->sharedSecret,
                    implode('.', [
                        $response['timestamp'],
                        $response->merchantid,
                        $response->result,
                    ])
                );
                break;
            case Customer::class:
            case RecurringPaymentMethod::class:
                $hash = GenerationUtils::generateHash(
                    $this->config->sharedSecret,
                    implode('.', [
                        $response['timestamp'],
                        $response->merchantid,
                        $response->orderid,
                        $response->result,
                        $response->message,
                        $response->pasref,
                        $response->authcode,
                    ])
                );
                break;
            default:
                $hash = '';
                break;
        }

        return $hash;
    }

    public function supportsHostedPayments()
    {
        return $this->supportsHostedPayments;
    }

    private function mapAcceptedCodes($paymentMethodType)
    {
        switch ($paymentMethodType) {
            case "3ds-verifysig":
            case "3ds-verifyenrolled":
                return ["00", "110"];
            case "payment-set":
                return ["01", "00"];
            default:
                return ["00"];
        }
    }

    private function setSerializeData($key, $value = null)
    {
        if ($value !== null) {
            $this->serializeData[$key] = $value;
        }
    }

    /**
     * @param array<string, array<string>> $supplementaryData
     */
    private function serializeSupplementaryData($supplementaryData)
    {
        foreach ($supplementaryData as $key => $value) {
            $this->setSerializeData(strtoupper($key), $value);
        }
    }

    private function generateCode(Address $address)
    {
        $countryCode = CountryUtils::getCountryCodeByCountry($address->country);
        switch ($countryCode)
        {
            case 'GB':
                return filter_var($address->postalCode, FILTER_SANITIZE_NUMBER_INT) . '|' . filter_var($address->streetAddress1, FILTER_SANITIZE_NUMBER_INT);
            case 'US':
            case 'CA':
                return $address->postalCode . '|' . $address->streetAddress1;
            default:
                return null;
        }
    }

    /**
     * @param AuthorizationBuilder $builder
     */
    private function buildOpenBankingHppRequest($builder)
    {
        $paymentMethod = $builder->hostedPaymentData->bankPayment;
        $this->setSerializeData(
            'HPP_OB_PAYMENT_SCHEME',
            !empty($paymentMethod->bankPaymentType) ?
                $paymentMethod->bankPaymentType : OpenBankingProvider::getBankPaymentType($builder->currency)
        );
        $this->setSerializeData('HPP_OB_REMITTANCE_REF_TYPE', $builder->remittanceReferenceType);
        $this->setSerializeData('HPP_OB_REMITTANCE_REF_VALUE', $builder->remittanceReferenceValue);
        $this->setSerializeData('HPP_OB_DST_ACCOUNT_IBAN', $paymentMethod->iban);
        $this->setSerializeData('HPP_OB_DST_ACCOUNT_NAME', $paymentMethod->accountName);
        $this->setSerializeData('HPP_OB_DST_ACCOUNT_NUMBER', $paymentMethod->accountNumber);
        $this->setSerializeData('HPP_OB_DST_ACCOUNT_SORT_CODE', $paymentMethod->sortCode);
        if (!empty($builder->hostedPaymentData)) {
            $hostedPaymentData = $builder->hostedPaymentData;
            $this->setSerializeData('HPP_OB_CUSTOMER_COUNTRIES', $hostedPaymentData->customerCountry);
        }
    }

    private function mapShaHash($toHash)
    {
        if (empty($this->config->shaHashType)) {
            throw new ApiException(sprintf("%s not supported. Please check your code and the Developers Documentation.", $this->config->shaHashType));
        }

        return [
            $this->config->shaHashType . 'HASH',
            GenerationUtils::generateNewHash($this->config->sharedSecret, $toHash, $this->config->shaHashType)
        ];
    }
}
