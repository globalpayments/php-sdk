<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Utils\CountryUtils;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\RecurringType;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\DccResponseResult;
use GlobalPayments\Api\Entities\FraudManagementResponse;
use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\Enums\HppVersion;
use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Builders\Secure3dBuilder;

class RealexConnector extends XmlGateway implements IPaymentGateway, IRecurringService, ISecure3dProvider
{
    /**
     * Merchant ID to authenticate with the gateway
     *
     * @var string
     */
    public $merchantId;

    /**
     * Account ID to authenticate with the gateway
     *
     * @var string
     */
    public $accountId;

    /**
     * Shared secret to authenticate with the gateway
     *
     * @var string
     */
    public $sharedSecret;

    /**
     * Channel to describe the transactions
     *
     * @var string
     */
    public $channel;

    /**
     * @var string
     */
    public $rebatePassword;

    /**
     * @var string
     */
    public $refundPassword;

    /**
     * @var boolean
     */
    public $supportsHostedPayments = true;

    /**
     * @var boolean
     */
    public $supportsRetrieval = false;

    /**
     * @var boolean
     */
    public $supportsUpdatePaymentDetails = true;

    /**
     * @var boolean
     */
    public $hostedPaymentConfig;
    
    /**
     * @var array
     */
    private $serializeData = [];

    // /**
    //  * @var Secure3dVersion
    //  */
    // public $version = Secure3dVersion::ONE;

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
        //for google payment amount and currency is required
        if (!empty($builder->transactionModifier) &&
            $builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE &&
            $builder->paymentMethod->mobileType === EncyptedMobileType::GOOGLE_PAY &&
            (empty($builder->amount) || empty($builder->currency))
        ) {
            throw new BuilderException("Amount and Currency cannot be null for google payment");
        }

        $xml = new DOMDocument();
        $timestamp = isset($builder->timestamp) ? $builder->timestamp : GenerationUtils::generateTimestamp();
        $orderId = isset($builder->orderId) ? $builder->orderId : GenerationUtils::generateOrderId();
        $transactionType = $this->mapAuthRequestType($builder);

        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $transactionType);

        $request->appendChild($xml->createElement("merchantid", $this->merchantId));
        
        if ($this->accountId !== null) {
            $request->appendChild($xml->createElement("account", $this->accountId));
        }
        if ($this->channel !== null) {
            $request->appendChild($xml->createElement("channel", $this->channel));
        }
        
        $request->appendChild($xml->createElement("orderid", $orderId));

        if (isset($builder->amount)) {
            $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)));
            $amount->setAttribute("currency", $builder->currency);
            $request->appendChild($amount);
        }
        
        // This needs to be figured out based on txn type and set to 0, 1 or MULTI
        if ($builder->transactionType === TransactionType::SALE || $builder->transactionType == TransactionType::AUTH) {
            $autoSettle = $builder->transactionType === TransactionType::SALE ? "1" : "0";
            $element = $xml->createElement("autosettle");
            $element->setAttribute("flag", $autoSettle);
            $request->appendChild($element);
        }

        // For Fraud Decision Manager
        if (!empty($builder->customerData)) {
            $customerValue = $builder->customerData;
            $customer = $xml->createElement("customer");
            $customer->appendChild($xml->createElement("customerid", $customerValue->id));
            $customer->appendChild($xml->createElement("firstname", $customerValue->firstName));
            $customer->appendChild($xml->createElement("lastname", $customerValue->lastName));
            $customer->appendChild($xml->createElement("dateofbirth", $customerValue->dateOfBirth));
            $customer->appendChild($xml->createElement("customerpassword", $customerValue->customerPassword));
            $customer->appendChild($xml->createElement("email", $customerValue->email));
            $customer->appendChild($xml->createElement("domainname", $customerValue->domainName));
            $customer->appendChild($xml->createElement("devicefingerprint", $customerValue->deviceFingerPrint));
            $customer->appendChild($xml->createElement("phonenumber", $customerValue->homePhone));
            $request->appendChild($customer);
        }

        if (!empty($builder->productData)) {
            $prod = [];
            $productValues = $builder->productData;
            $products = $xml->createElement("products");

            foreach ($productValues as $prod) {
                $product = $xml->createElement("product");
                $product->appendChild($xml->createElement('product_id', $prod['product_id']));
                $product->appendChild($xml->createElement('productname', $prod['productname']));
                $product->appendChild($xml->createElement('quantity', $prod['quantity']));
                $product->appendChild($xml->createElement('unitprice', $prod['unitprice']));
                $product->appendChild($xml->createElement('gift', $prod['gift']));
                $product->appendChild($xml->createElement('type', $prod['type']));
                $product->appendChild($xml->createElement('risk', $prod['risk']));
                $product->appendChild($products);
                $request->appendChild($product);
            }
        }

        if ($builder->decisionManager !== null) {
            $dmValues = $builder->decisionManager;
            $fraud = $xml->createElement("fraud");
            $dm = $fraud->appendChild($xml->createElement('dm'));
            $dm->appendChild($xml->createElement('billtohostname', $dmValues->billToHostName));
            $dm->appendChild($xml->createElement(
                'billtohttpbrowsercookiesaccepted',
                ($dmValues->billToHttpBrowserCookiesAccepted) != true ? 'false' : 'true'
            ));
            $dm->appendChild($xml->createElement('billtohttpbrowseremail', $dmValues->billToHttpBrowserEmail));
            $dm->appendChild($xml->createElement('billtohttpbrowsertype', $dmValues->billToHttpBrowserType));
            $dm->appendChild($xml->createElement('billtoipnetworkaddress', $dmValues->billToIpNetworkAddress));
            $dm->appendChild($xml->createElement(
                'businessrulesscorethreshold',
                $dmValues->businessRulessCoreThresHold
            ));
            $dm->appendChild($xml->createElement('billtopersonalid', $dmValues->billToPersonalId));
            $dm->appendChild($xml->createElement('invoiceheadertendertype', $dmValues->invoiceHeaderTenderType));
            $dm->appendChild($xml->createElement(
                'invoiceheaderisgift',
                ($dmValues->invoiceHeaderIsGift) != true ? 'false' : 'true'
            ));
            $dm->appendChild($xml->createElement('decisionmanagerprofile', $dmValues->decisionManagerProfile));
            $dm->appendChild($xml->createElement(
                'invoiceheaderreturnsaccepted',
                ($dmValues->invoiceHeaderReturnsAccepted) != true ? 'false' : 'true'
            ));
            $dm->appendChild($xml->createElement('itemhosthedge', $dmValues->itemHostHedge));
            $dm->appendChild($xml->createElement('itemnonsensicalhedge', $dmValues->itemNonsensicalHedge));
            $dm->appendChild($xml->createElement('itemobscenitieshedge', $dmValues->itemObscenitiesHedge));
            $dm->appendChild($xml->createElement('itemphonehedge', $dmValues->itemPhoneHedge));
            $dm->appendChild($xml->createElement('itemtimehedge', $dmValues->itemTimeHedge));
            $dm->appendChild($xml->createElement('itemvelocityhedge', $dmValues->itemVelocityHedge));
            $request->appendChild($dm);
        }

        if (!empty($builder->customData)) {
            $cust = [];
            $customValues = $builder->customData;
            $custom = $xml->createElement("custom");

            foreach ($customValues as $cust) {
                $custom->appendChild($xml->createElement('field01', $cust['field01']));
                $custom->appendChild($xml->createElement('field02', $cust['field02']));
                $custom->appendChild($xml->createElement('field03', $cust['field03']));
                $custom->appendChild($xml->createElement('field04', $cust['field04']));
                $request->appendChild($custom);
            }
        }

        // For DCC rate lookup
        if ($builder->transactionType === TransactionType::DCC_RATE_LOOKUP) {
            $dccinfo = $xml->createElement("dccinfo");
            $dccinfo->appendChild($xml->createElement("ccp", $builder->dccProcessor));
            $dccinfo->appendChild($xml->createElement("type", $builder->dccType));
            $dccinfo->appendChild($xml->createElement("ratetype", $builder->dccRateType));
            $request->appendChild($dccinfo);
        }

        // For DCC charge/auth
        if (!empty($builder->dccRateData)) {
            $dccinfo = $xml->createElement("dccinfo");

            $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', $builder->dccRateData->amount));
            $amount->setAttribute("currency", $builder->dccRateData->currency);

            $dccinfo->appendChild($amount);
            $dccinfo->appendChild($xml->createElement("ccp", $builder->dccRateData->dccProcessor));
            $dccinfo->appendChild($xml->createElement("type", $builder->dccRateData->dccType));
            $dccinfo->appendChild($xml->createElement("rate", $builder->dccRateData->dccRate));
            $dccinfo->appendChild($xml->createElement("ratetype", $builder->dccRateData->dccRateType));
            $request->appendChild($dccinfo);
        }

        // Hydrate the payment data fields
        if ($builder->paymentMethod instanceof CreditCardData) {
            $card = $builder->paymentMethod;
            
            if ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                $request->appendChild($xml->createElement("token", $card->token));
                $request->appendChild($xml->createElement("mobile", $card->mobileType));
            } else {
                $cardElement = $xml->createElement("card");
                $cardElement->appendChild($xml->createElement("number", $card->number));
                $cardElement->appendChild($xml->createElement("expdate", $card->getShortExpiry()));
                $cardElement->appendChild($xml->createElement("chname", $card->cardHolderName));
                $cardElement->appendChild($xml->createElement("type", strtoupper($card->getCardType())));

                if ($card->cvn !== null) {
                    //if cvn number is not empty indicator should be PRESENT
                    $cvnPresenceIndicator = (!empty($card->cvn)) ?
                                                CvnPresenceIndicator::PRESENT:
                                                $card->cvnPresenceIndicator;
                    
                    $cvnElement = $xml->createElement("cvn");
                    $cvnElement->appendChild($xml->createElement("number", $card->cvn));
                    $cvnElement->appendChild($xml->createElement("presind", $cvnPresenceIndicator));
                    $cardElement->appendChild($cvnElement);
                }
                $request->appendChild($cardElement);
            }
            // issueno
            $hash = '';
            if ($builder->transactionType === TransactionType::VERIFY) {
                $hash = GenerationUtils::generateHash(
                    $this->sharedSecret,
                    implode('.', [
                            $timestamp,
                            $this->merchantId,
                            $orderId,
                            $card->number
                                ])
                );
            } else {
                $requestValues = $this->getShal1RequestValues($timestamp, $orderId, $builder, $card);
                
                $hash = GenerationUtils::generateHash(
                    $this->sharedSecret,
                    implode('.', $requestValues)
                );
            }
           
            $request->appendChild($xml->createElement("sha1hash", $hash));
        }
        if ($builder->paymentMethod instanceof RecurringPaymentMethod) {
            $recurring = $builder->paymentMethod;
            $request->appendChild($xml->createElement("payerref", $recurring->customerKey));
            $request->appendChild($xml->createElement(
                "paymentmethod",
                isset($recurring->key) ? $recurring->key : $recurring->id
            ));

            if ($builder->cvn !== null && $builder->cvn !== '') {
                $paymentData = $xml->createElement("paymentdata");
                $cvn = $xml->createElement("cvn");
                $cvn->appendChild($xml->createElement("number", $builder->cvn));
                $paymentData->appendChild($cvn);
                $request->appendChild($paymentData);
            }
            
            $hash = '';
            if ($builder->transactionType === TransactionType::VERIFY) {
                if (!empty($builder->transactionModifier) &&
                    $builder->transactionModifier === TransactionModifier::SECURE3D) {
                    $hash = GenerationUtils::generateHash(
                        $this->sharedSecret,
                        implode('.', [
                                    $timestamp,
                                    $this->merchantId,
                                    $orderId,
                                    preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
                                    $builder->currency,
                                    $recurring->customerKey,
                                ])
                    );
                } else {
                    $hash = GenerationUtils::generateHash(
                        $this->sharedSecret,
                        implode('.', [
                                    $timestamp,
                                    $this->merchantId,
                                    $orderId,
                                    $recurring->customerKey,
                                ])
                    );
                }
            } else {
                $hash = GenerationUtils::generateHash(
                    $this->sharedSecret,
                    implode('.', [
                        $timestamp,
                        $this->merchantId,
                        $orderId,
                        preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
                        $builder->currency,
                        $recurring->customerKey,
                    ])
                );
            }
            $request->appendChild($xml->createElement("sha1hash", $hash));
        } else {
            // TODO: Token Processing
            //$request->appendChild($xml->createElement("sha1hash", GenerateHash(order, token));
        }

        // refund hash
        if ($builder->transactionType === TransactionType::REFUND) {
            $request->appendChild($xml->createElement(
                "refundhash",
                GenerationUtils::generateHash($this->refundPassword) ?: ''
            ));
        }

        
        
        if ($builder->paymentMethod instanceof AlternativePaymentMethod) {
            $this->buildAlternativePaymentMethod($builder, $request, $xml);
        }

        // comment ...TODO: needs to be multiple
        if ($builder->description != null) {
            $comments = $xml->createElement("comments");
            $comment = $xml->createElement("comment", $builder->description);
            $comment->setAttribute("id", "1");
            $comments->appendChild($comment);
            
            $request->appendChild($comments);
        }
        
        if ($builder->paymentMethod instanceof AlternativePaymentMethod) {
            $hash = GenerationUtils::generateHash(
                $this->sharedSecret,
                implode('.', [
                        $timestamp,
                        $this->merchantId,
                        $orderId,
                        preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
                        $builder->currency,
                        $builder->paymentMethod->alternativePaymentMethodType,
                    ])
            );
            $request->appendChild($xml->createElement("sha1hash", $hash));
        }
        
        if ($builder->recurringType !== null || $builder->recurringSequence !== null) {
            $recurring = $xml->createElement("recurring");
            $recurring->setAttribute("type", strtolower($builder->recurringType));
            $recurring->setAttribute("sequence", strtolower($builder->recurringSequence));
            $request->appendChild($recurring);
        }

        // fraud filter
        $this->buildFraudFilter($builder, $xml, $request);
        
        // tssinfo

        // stored credential
        if ($builder->storedCredential != null) {
            $storedCredential = $xml->createElement("storedcredential");
            $storedCredential->appendChild($xml->createElement("type", $builder->storedCredential->type));
            $storedCredential->appendChild($xml->createElement("initiator", $builder->storedCredential->initiator));
            $storedCredential->appendChild($xml->createElement("sequence", $builder->storedCredential->sequence));
            $storedCredential->appendChild($xml->createElement("srd", $builder->storedCredential->schemeId));
            $request->appendChild($storedCredential);
        }

        // mpi
        $secureEcom = $builder->paymentMethod->threeDSecure;
        if (!empty($secureEcom)) {
            $mpi = $xml->createElement("mpi");
            $mpi->appendChild($xml->createElement("eci", $secureEcom->eci));
            $mpi->appendChild($xml->createElement("cavv", $secureEcom->cavv));
            $mpi->appendChild($xml->createElement("xid", $secureEcom->xid));

            if (
                $secureEcom->directoryServerTransactionId != null ||
                $secureEcom->authenticationValue != null ||
                $secureEcom->messageVersion != null
            ) {
                $mpi->appendChild($xml->createElement("ds_trans_id", $secureEcom->directoryServerTransactionId));
                $mpi->appendChild($xml->createElement("authentication_value", $secureEcom->authenticationValue));
                $mpi->appendChild($xml->createElement("message_version", $secureEcom->messageVersion));
            }
            if ($secureEcom->exemptStatus != null) {
                $mpi->appendChild($xml->createElement("exempt_status", $secureEcom->exemptStatus));
            }
            $request->appendChild($mpi);
        }
        
        $acceptedResponseCodes = $this->mapAcceptedCodes($transactionType);
        $response = $this->doTransaction($xml->saveXML($request));
        return $this->mapResponse($response, $acceptedResponseCodes);
    }

    /**
     * @return Transaction
     */
    public function processSecure3d(Secure3dBuilder $builder)
    {
        $transType = $builder->getTransactionType();
        
        if ($transType === TransactionType::VERIFY_ENROLLED) {
            $authBuilder = (new AuthorizationBuilder($transType, $builder->getPaymentMethod()))
                ->withAmount($builder->getAmount())
                ->withCurrency($builder->getCurrency())
                ->withOrderId($builder->getOrderId());

            return $this->processAuthorization($authBuilder);
        } elseif ($transType === TransactionType::VERIFY_SIGNATURE) {
            // Get our three d secure object
            $secureEcom = $builder->getThreeDSecure();

            // Create our transaction reference
            $reference = new TransactionReference();
            $reference->orderId = $secureEcom->getOrderId();
            
            $managementBuilder = (new ManagementBuilder($transType))
                ->withAmount($secureEcom->getAmount())
                ->withCurrency($secureEcom->getCurrency())
                ->withPayerAuthenticationResponse($builder->getPayerAuthenticationResponse())
                ->withPaymentMethod($reference);
            return $this->manageTransaction($managementBuilder);
        }
        throw new UnsupportedTransactionException(sprintf("Unknown transaction type %s", $transType));
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

        $orderId = isset($builder->orderId) ? $builder->orderId : GenerationUtils::generateOrderId();
        $timestamp = isset($builder->timestamp) ? $builder->timestamp : GenerationUtils::generateTimestamp();

        $this->setSerializeData('MERCHANT_ID', $this->merchantId);
        $this->setSerializeData('ACCOUNT', $this->accountId);
        $this->setSerializeData('HPP_CHANNEL', $this->channel);
        $this->setSerializeData('ORDER_ID', $orderId);
        if ($builder->amount !== null) {
            $this->setSerializeData('AMOUNT', preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)));
        }
        $this->setSerializeData('CURRENCY', $builder->currency);
        $this->setSerializeData('TIMESTAMP', $timestamp);
        $this->setSerializeData(
            'AUTO_SETTLE_FLAG',
            ($builder->transactionType == TransactionType::SALE) ? "1" : "0"
        );
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
        
        $this->setSerializeData('VAR_REF', $builder->clientTransactionId);
        $this->setSerializeData('HPP_LANG', $this->hostedPaymentConfig->language);
        $this->setSerializeData('MERCHANT_RESPONSE_URL', $this->hostedPaymentConfig->responseUrl);
        $this->setSerializeData('CARD_PAYMENT_BUTTON', $this->hostedPaymentConfig->paymentButtonText);
        if (!empty($builder->hostedPaymentData)) {
            $this->setSerializeData('HPP_CUSTOMER_EMAIL', $builder->hostedPaymentData->customerEmail);
            $this->setSerializeData('HPP_CUSTOMER_PHONENUMBER_MOBILE', $builder->hostedPaymentData->customerPhoneMobile);
            $this->setSerializeData('HPP_CHALLENGE_REQUEST_INDICATOR', $builder->hostedPaymentData->challengeRequest);
            if (isset($builder->hostedPaymentData->addressesMatch)) {
                $this->setSerializeData('HPP_ADDRESS_MATCH_INDICATOR', $builder->hostedPaymentData->addressesMatch ? 'TRUE' : 'FALSE');
            }
            if (!empty($builder->hostedPaymentData->supplementaryData)) {
                $this->serializeSupplementaryData($builder->hostedPaymentData->supplementaryData);
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
        if (!empty($this->hostedPaymentConfig->FraudFilterMode)) {
            $this->setSerializeData('HPP_FRAUD_FILTER_MODE', $this->hostedPaymentConfig->FraudFilterMode);
        }
        
        if ($builder->recurringType !== null || $builder->recurringSequence !== null) {
            $this->setSerializeData('RECURRING_TYPE', strtolower($builder->recurringType));
            $this->setSerializeData('RECURRING_SEQUENCE', strtolower($builder->recurringSequence));
        }
        if (isset($this->hostedPaymentConfig->version)) {
            $this->setSerializeData('HPP_VERSION', $this->hostedPaymentConfig->version);
        }

        if (!empty($builder->supplementaryData)) {
            $this->serializeSupplementaryData($builder->supplementaryData);
        }

        $toHash = [
            $timestamp,
            $this->merchantId,
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

        if ($this->hostedPaymentConfig->fraudFilterMode !== FraudFilterMode::NONE) {
            $toHash[] = $this->hostedPaymentConfig->fraudFilterMode;
        }
        
        $this->serializeData["SHA1HASH"] = GenerationUtils::generateHash($this->sharedSecret, implode('.', $toHash));
        return GenerationUtils::convertArrayToJson($this->serializeData, $this->hostedPaymentConfig->version);
    }

    /**
     * {@inheritdoc}
     *
     * @param ManagementBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function manageTransaction(ManagementBuilder $builder)
    {
        $xml = new DOMDocument();
        $timestamp = GenerationUtils::generateTimestamp();
        $orderId = $builder->orderId ?: GenerationUtils::generateOrderId();
        $transactionType = $this->mapManageRequestType($builder);
        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $transactionType);

        $request->appendChild($xml->createElement("merchantid", $this->merchantId));
        
        if ($this->accountId !== null) {
            $request->appendChild($xml->createElement("account", $this->accountId));
        }
        if (is_null($builder->alternativePaymentType)) {
            $request->appendChild($xml->createElement("channel", $this->channel));
        }
      
        if ($builder->amount !== null) {
            $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)));
            $amount->setAttribute("currency", $builder->currency);
            $request->appendChild($amount);
        } elseif ($builder->transactionType === TransactionType::CAPTURE) {
            throw new BuilderException("Amount cannot be null for capture.");
        }
        
        $request->appendChild($xml->createElement("orderid", $orderId));
        $request->appendChild($xml->createElement("pasref", $builder->transactionId));

        // rebate hash
        if ($builder->transactionType === TransactionType::REFUND &&
                is_null($builder->alternativePaymentType)) {
            $request->appendChild($xml->createElement("authcode", $builder->paymentMethod->authCode));
        }

        // reason code
        if ($builder->reasonCode !== null) {
            $request->appendChild($xml->createElement("reasoncode", $builder->reasonCode));
        }
        
        if ($builder->alternativePaymentType !== null) {
            $request->appendChild($xml->createElement("paymentmethod", $builder->alternativePaymentType));
        }

        if ($builder->transactionType === TransactionType::VERIFY_SIGNATURE) {
            $request->appendChild($xml->createElement("pares", $builder->payerAuthenticationResponse));
        }

        // comments needs to be multiple
        if ($builder->description !== null) {
            $comments = $xml->createElement("comments");
            $comment = $xml->createElement("comment", $builder->description);
            $comment->setAttribute("id", "1");
            $comments->appendChild($comment);
            $request->appendChild($comments);
        }

        $toHash = [
                $timestamp,
                $this->merchantId,
                $orderId,
                ($builder->amount !== null ? preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)) : ''),
                ($builder->currency !== null ? $builder->currency : ''),
                ($builder->alternativePaymentType !== null ? $builder->alternativePaymentType : '')
            ];

        $request->appendChild(
            $xml->createElement(
                "sha1hash",
                GenerationUtils::generateHash($this->sharedSecret, implode('.', $toHash))
            )
        );
        
        // rebate hash
        if ($builder->transactionType === TransactionType::REFUND) {
            $request->appendChild(
                $xml->createElement(
                    "refundhash",
                    GenerationUtils::generateHash(isset($this->rebatePassword) ? $this->rebatePassword : '')
                )
            );
        }
        
        $response = $this->doTransaction($xml->saveXML($request));
        return $this->mapResponse($response, $this->mapAcceptedCodes($transactionType));
    }

    public function processReport(ReportBuilder $builder)
    {
        throw new UnsupportedTransactionException(
            'Reporting functionality is not supported through this gateway.'
        );
    }

    public function processRecurring(RecurringBuilder $builder)
    {
        $xml = new DOMDocument();
        $timestamp = GenerationUtils::generateTimestamp();
        $orderId = $builder->orderId ? $builder->orderId : GenerationUtils::generateOrderId();

        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $this->mapRecurringRequestType($builder));

        $request->appendChild($xml->createElement("merchantid", $this->merchantId));
        
        if ($this->accountId !== null) {
            $request->appendChild($xml->createElement("account", $this->accountId));
        }
        $request->appendChild($xml->createElement("channel", $this->channel));
        $request->appendChild($xml->createElement("orderid", $orderId));

        if ($builder->transactionType == TransactionType::CREATE ||
            $builder->transactionType == TransactionType::EDIT) {
            if ($builder->entity instanceof Customer) {
                $hash = GenerationUtils::generateHash(
                    $this->sharedSecret,
                    implode('.', [
                                $timestamp,
                                $this->merchantId,
                                $orderId,
                                '',
                                '',
                                $builder->entity->key
                                ])
                );
                
                $request->appendChild($this->buildCustomer($xml, $builder));
            } elseif ($builder->entity instanceof RecurringPaymentMethod) {
                $payment = $builder->entity;
                $paymentKey = (!empty($payment->key)) ? $payment->key : $payment->id;
                
                if ($builder->transactionType == TransactionType::CREATE) {
                    $hash = GenerationUtils::generateHash(
                        $this->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $this->merchantId,
                            $orderId,
                            '',
                            '',
                            $payment->customerKey,
                            $payment->paymentMethod->cardHolderName,
                            $payment->paymentMethod->number
                            ])
                    );
                } else {
                    $hash = GenerationUtils::generateHash(
                        $this->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $this->merchantId,
                            $payment->customerKey,
                            $paymentKey,
                            $payment->paymentMethod->getShortExpiry(),
                            $payment->paymentMethod->number
                            ])
                    );
                }
                $request->appendChild($this->buildCardElement($xml, $payment, $paymentKey));
                $request->appendChild($xml->createElement("defaultcard", 1));
            }
            
            //set hash value
            $request->appendChild($xml->createElement("sha1hash", $hash));
        } elseif ($builder->transactionType == TransactionType::DELETE) {
            if ($builder->entity instanceof RecurringPaymentMethod) {
                $payment = $builder->entity;
                $paymentKey = (!empty($payment->key)) ? $payment->key : $payment->id;
                $cardElement = $xml->createElement("card");
                $cardElement->appendChild($xml->createElement("ref", $paymentKey));
                $cardElement->appendChild($xml->createElement("payerref", $payment->customerKey));
                $request->appendChild($cardElement);
                
                $hash = GenerationUtils::generateHash(
                    $this->sharedSecret,
                    implode('.', [
                            $timestamp,
                            $this->merchantId,
                            $payment->customerKey,
                            $paymentKey
                            ])
                );
                $request->appendChild($xml->createElement("sha1hash", $hash));
            }
        }
        
        $response = $this->doTransaction($xml->saveXML($request));
        return $this->mapResponse($response);
    }

    private function buildCustomer($xml, $builder)
    {
        $customer = $builder->entity;
        $type = 'Retail';
        if ($builder->transactionType === TransactionType::EDIT) {
            $type = 'Subscriber';
        }
        $payer = $xml->createElement("payer");
        $payer->setAttribute("ref", (!empty($customer->key)) ? $customer->key :
                GenerationUtils::generateRecurringKey());
        $payer->setAttribute("type", $type);

        $payer->appendChild($xml->createElement("title", $customer->title));
        $payer->appendChild($xml->createElement("firstname", $customer->firstName));
        $payer->appendChild($xml->createElement("surname", $customer->lastName));
        $payer->appendChild($xml->createElement("company", $customer->company));


        if ($customer->address != null) {
            $address = $xml->createElement("address");
            $address->appendChild($xml->createElement("line1", $customer->address->streetAddress1));
            $address->appendChild($xml->createElement("line2", $customer->address->streetAddress2));
            $address->appendChild($xml->createElement("line3", $customer->address->streetAddress3));
            $address->appendChild($xml->createElement("city", $customer->address->city));
            $address->appendChild($xml->createElement("county", $customer->address->getProvince()));
            $address->appendChild($xml->createElement("postcode", $customer->address->postalCode));

            $country = $xml->createElement("country", $customer->address->country);
            if (!empty($customer->address->countryCode)) {
                $country->setAttribute("code", $customer->address->countryCode);
            }
            $address->appendChild($country);

            $payer->appendChild($address);
        }

        $phonenumbers = $xml->createElement("phonenumbers");
        $phonenumbers->appendChild($xml->createElement("home", $customer->homePhone));
        $phonenumbers->appendChild($xml->createElement("work", $customer->workPhone));
        $phonenumbers->appendChild($xml->createElement("fax", $customer->fax));
        $phonenumbers->appendChild($xml->createElement("mobile", $customer->mobilePhone));

        $payer->appendChild($phonenumbers);
        $payer->appendChild($xml->createElement("email", $customer->email));

        return $payer;
    }
    
    private function buildCardElement($xml, $payment, $paymentKey = '')
    {
        $card = $payment->paymentMethod;
        $cardElement = $xml->createElement("card");
        $cardElement->appendChild($xml->createElement("ref", $paymentKey));
        $cardElement->appendChild($xml->createElement("payerref", $payment->customerKey));
        $cardElement->appendChild($xml->createElement("number", $card->number));
        $cardElement->appendChild($xml->createElement("expdate", $card->getShortExpiry()));
        $cardElement->appendChild($xml->createElement("chname", $card->cardHolderName));
        $cardElement->appendChild($xml->createElement("type", strtoupper($card->getCardType())));

        return $cardElement;
    }

    /**
     * Deserializes the gateway's XML response
     *
     * @param string $rawResponse The XML response
     *
     * @return Transaction
     */
    protected function mapResponse($rawResponse, array $acceptedCodes = null)
    {
        $result = new Transaction();

        $root = $this->xml2object($rawResponse);

        $this->checkResponse($root, $acceptedCodes);

        $result->responseCode = (string)$root->result;
        $result->responseMessage = (string)$root->message;
        $result->cvnResponseCode = (string)$root->cvnresult;
        $result->avsResponseCode = (string)$root->avspostcoderesponse;
        $result->avsAddressResponse = (string)$root->avsaddressresponse;
        $result->transactionReference = new TransactionReference();
        $result->transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
        $result->transactionReference->transactionId = (string)$root->pasref;
        $result->transactionReference->authCode = (string)$root->authcode;
        $result->transactionReference->orderId = (string)$root->orderid;
        $result->timestamp = (!empty($root->attributes()->timestamp)) ?
                                    (string) $root->attributes()->timestamp :
                                    '';

        // 3d secure enrolled
        if (!empty($root->enrolled)) {
            $result->threeDSecure = new ThreeDSecure();
            $result->threeDSecure->enrolled = (string)$root->enrolled;
            $result->threeDSecure->xid = (string)$root->xid;
            $result->threeDSecure->issuerAcsUrl = (string)$root->url;
            $result->threeDSecure->payerAuthenticationRequest = (string)$root->pareq;
        }

        // 3d secure signature
        if (!empty($root->threedsecure)) {
            $secureEcom = new ThreeDSecure();
            $secureEcom->status = (string)$root->threedsecure->status;
            $secureEcom->eci = (string)$root->threedsecure->eci;
            $secureEcom->cavv = (string)$root->threedsecure->cavv;
            $secureEcom->xid = (string)$root->threedsecure->xid;
            $secureEcom->algorithm = (int)$root->threedsecure->algorithm;
            $result->threeDSecure = $secureEcom;
        }
        
        // stored credential
        $result->schemeId = (string)$root->srd;

        // dccinfo
        if (!empty($root->dccinfo)) {
            $result->dccResponseResult = new DccResponseResult();

            $result->dccResponseResult->cardHolderCurrency = (string)$root->dccinfo->cardholdercurrency;
            $result->dccResponseResult->cardHolderAmount = (string)$root->dccinfo->cardholderamount;
            $result->dccResponseResult->cardHolderRate = (string)$root->dccinfo->cardholderrate;
            $result->dccResponseResult->merchantCurrency = (string)$root->dccinfo->merchantcurrency;
            $result->dccResponseResult->merchantAmount = (string)$root->dccinfo->merchantamount;
            $result->dccResponseResult->marginRatePercentage = (string)$root->dccinfo->marginratepercentage;
            $result->dccResponseResult->exchangeRateSourceName = (string)$root->dccinfo->exchangeratesourcename;
            $result->dccResponseResult->commissionPercentage = (string)$root->dccinfo->commissionpercentage;
            $result->dccResponseResult->exchangeRateSourceTimestamp = (string)
                                            $root->dccinfo->exchangeratesourcetimestamp;
        }

        // fraud filter
        if (!empty($root->fraudresponse)) {
            $fraudResponse = $root->fraudresponse;
            $result->fraudFilterResponse = new FraudManagementResponse();
            
            foreach ($fraudResponse->attributes() as $attrName => $attrValue) {
                $result->fraudFilterResponse->fraudResponseMode = (!empty($attrValue)) ? (string) $attrValue : '';
            }

            $result->fraudFilterResponse->fraudResponseResult = (!empty($fraudResponse->result)) ?
                                            (string) $fraudResponse->result : '';
            
            if (!empty($fraudResponse->rules)) {
                foreach ($fraudResponse->rules->rule as $rule) {
                    $ruleDetails = [
                        'id' => (string) $rule->attributes()->id,
                        'name' => (string) $rule->attributes()->name,
                        'action' => (string) $rule->action
                    ];
                    $result->fraudFilterResponse->fraudResponseRules[] = $ruleDetails;
                }
            }
        }
        
        // alternativePaymentResponse
        if (!empty($root->paymentmethoddetails)) {
            $result->alternativePaymentResponse = new AlternativePaymentResponse();

            $result->alternativePaymentResponse->paymentMethod = (string)
                    $root->paymentmethoddetails->paymentmethod;
            $result->alternativePaymentResponse->bankAccount = (string)
                    $root->paymentmethoddetails->bankaccount;
            $result->alternativePaymentResponse->accountHolderName = (string)
                    $root->paymentmethoddetails->accountholdername;
            $result->alternativePaymentResponse->country = (string)
                    $root->paymentmethoddetails->country;
            $result->alternativePaymentResponse->redirectUrl = (string)
                    $root->paymentmethoddetails->redirecturl;
            $result->alternativePaymentResponse->paymentPurpose = (string)
                    $root->paymentmethoddetails->paymentpurpose;
        }
       
        return $result;
    }

    protected function checkResponse($root, array $acceptedCodes = null)
    {
        if ($acceptedCodes === null) {
            $acceptedCodes = [ "00" ];
        }

        $responseCode = (string)$root->result;
        $responseMessage = (string)$root->message;

        if (!in_array($responseCode, $acceptedCodes)) {
            throw new GatewayException(
                sprintf('Unexpected Gateway Response: %s - %s', $responseCode, $responseMessage),
                $responseCode,
                $responseMessage
            );
        }
    }

    /**
     * Generates a request hash from the request data
     *
     * @param string $timestamp Request timestamp
     * @param string $orderId Request order ID
     * @param string $amount Request amount
     * @param string $currency Request currency
     * @param string $paymentData Request payment data
     * @param bool $verify Is request a verify transaction
     *
     * @return string
     */
    protected function generateHash(
        $timestamp,
        $orderId,
        $amount,
        $currency,
        $paymentData = null,
        $verify = false
    ) {
        $data = [
            $timestamp,
            $this->merchantId,
            $orderId,
        ];

        if (false === $verify) {
            $data[] = $amount;
            $data[] = $currency;
        }

        $data[] = $paymentData;

        return GenerationUtils::generateHash(
            $this->sharedSecret,
            implode('.', $data)
        );
    }

    /**
     * Maps a transaction builder to a Realex request type
     *
     * @param AuthorizationBuilder $builder Transaction builder
     *
     * @return string
     */
    protected function mapAuthRequestType(AuthorizationBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::SALE:
            case TransactionType::AUTH:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                        return 'offline';
                    } elseif ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                        return 'auth-mobile';
                    }
                } elseif ($builder->paymentMethod->paymentMethodType == PaymentMethodType::RECURRING) {
                    return (!empty($builder->recurringSequence) &&
                            $builder->recurringSequence == RecurringSequence::FIRST) ?
                            'auth' :
                            'receipt-in';
                } elseif ($builder->paymentMethod->paymentMethodType == PaymentMethodType::APM) {
                    return "payment-set";
                }
                return 'auth';
            case TransactionType::CAPTURE:
                return 'settle';
            case TransactionType::VERIFY:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::RECURRING) {
                    if (!empty($builder->transactionModifier) &&
                            $builder->transactionModifier === TransactionModifier::SECURE3D) {
                        return 'realvault-3ds-verifyenrolled';
                    }
                    return 'receipt-in-otb';
                }
                return 'otb';
            case TransactionType::REFUND:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    return 'credit';
                }
                return 'payment-out';
            case TransactionType::DCC_RATE_LOOKUP:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    return "dccrate";
                }
                return "realvault-dccrate";
                
            case TransactionType::REVERSAL:
                // TODO: should be customer type
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
            case TransactionType::VERIFY_ENROLLED:
                if ($builder->paymentMethod instanceof RecurringPaymentMethod) {
                    return 'realvault-3ds-verifyenrolled';
                }
                return '3ds-verifyenrolled';
            default:
                return 'unknown';
        }
    }

    /**
     * Maps a transaction builder to a Realex request type
     *
     * @param ManagementBuilder $builder Transaction builder
     *
     * @return string
     */
    protected function mapManageRequestType(ManagementBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::CAPTURE:
                return 'settle';
            case TransactionType::HOLD:
                return 'hold';
            case TransactionType::REFUND:
                if ($builder->alternativePaymentType !== null) {
                    return 'payment-credit';
                }
                return 'rebate';
            case TransactionType::RELEASE:
                return 'release';
            case TransactionType::VOID:
            case TransactionType::REVERSAL:
                return 'void';
            case TransactionType::VERIFY_SIGNATURE:
                return '3ds-verifysig';
            default:
                return 'unknown';
        }
    }

    /**
     * Maps a transaction builder to a Realex request type
     *
     * @param RecurringBuilder $builder Transaction builder
     *
     * @return string
     */
    private function mapRecurringRequestType(RecurringBuilder $builder)
    {
        $entity = $builder->entity;

        switch ($builder->transactionType) {
            case TransactionType::CREATE:
                if ($entity instanceof Customer) {
                    return "payer-new";
                } elseif ($entity instanceof RecurringPaymentMethod) {
                    return "card-new";
                }
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
            case TransactionType::EDIT:
                if ($entity instanceof Customer) {
                    return "payer-edit";
                } elseif ($entity instanceof RecurringPaymentMethod) {
                    return "card-update-card";
                }
                throw new UnsupportedTransactionException();
            case TransactionType::DELETE:
                if ($entity instanceof RecurringPaymentMethod) {
                    return "card-cancel-card";
                }
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
            default:
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
        }
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
        $envelope = simplexml_load_string(
            $xml,
            'SimpleXMLElement'
        );

        return $envelope;
    }
    
    /**
     * Return the request values for Shal hash generation based on transaction type
     * EncyptedMobileType::GOOGLE_PAY requires amount and currency with token
     * EncyptedMobileType::APPLE_PAY doesn't requires amount and currency. token contains those values
     *
     * @param string $timestamp current timestamp
     * @param int $orderId current order id
     * @param object $builder auth builder object
     * @param object $card
     *
     * @return array
     */
    private function getShal1RequestValues($timestamp, $orderId, $builder, $card)
    {
        $requestValues = [
            $timestamp,
            $this->merchantId,
            $orderId,
            preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
            $builder->currency,
            $card->number
        ];

        if (($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE)) {
            switch ($card->mobileType) {
                case EncyptedMobileType::GOOGLE_PAY:
                    $requestValues = [
                        $timestamp,
                        $this->merchantId,
                        $orderId,
                        preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
                        $builder->currency,
                        $card->token
                    ];
                    break;

                case EncyptedMobileType::APPLE_PAY:
                    $requestValues = [
                        $timestamp,
                        $this->merchantId,
                        $orderId,
                        '',
                        '',
                        $card->token
                    ];
                    break;
            }
        }
        return $requestValues;
    }
    
    public function buildFraudFilter($builder, $xml, $request)
    {
        // tssinfo fraudfilter
        // fraudfilter
        if (!empty($builder->fraudFilter)) {
            $fraudFilter = $xml->createElement("fraudfilter");
            $fraudFilter->setAttribute("mode", $builder->fraudFilter);
            $request->appendChild($fraudFilter);
        }
        if ($builder->customerId !== null || $builder->productId !== null ||
                $builder->clientTransactionId !== null || $builder->verifyAddress !== false
        ) {
            $tssInfo = $xml->createElement("tssinfo");
            
            if (!empty($builder->customerId)) {
                $tssInfo->appendChild($xml->createElement("custnum", $builder->customerId));
            }

            if (!empty($builder->productId)) {
                $tssInfo->appendChild($xml->createElement("prodid", $builder->productId));
            }

            if (!empty($builder->clientTransactionId)) {
                $tssInfo->appendChild($xml->createElement("varref", $builder->clientTransactionId));
            }

            if (!empty($builder->customerIpAddress)) {
                $tssInfo->appendChild($xml->createElement("custipaddress", $builder->customerIpAddress));
            }

            if (!empty($builder->billingAddress)) {
                $billingAddress = $xml->createElement("address");
                $billingAddress->setAttribute("type", 'billing');
                $billingAddress->appendChild($xml->createElement("code", $builder->billingAddress->postalCode));
                $billingAddress->appendChild($xml->createElement("country", $builder->billingAddress->country));
                $tssInfo->appendChild($billingAddress);
            }

            if (!empty($builder->shippingAddress)) {
                $shippingAddress = $xml->createElement("address");
                $shippingAddress->setAttribute("type", 'shipping');
                $shippingAddress->appendChild($xml->createElement("code", $builder->shippingAddress->postalCode));
                $shippingAddress->appendChild($xml->createElement("country", $builder->shippingAddress->country));
                $tssInfo->appendChild($shippingAddress);
            }
            if (!empty($tssInfo->childNodes->length)) {
                $request->appendChild($tssInfo);
            }
        }
        return;
    }
    
    public function supportsHostedPayments()
    {
        return $this->supportsHostedPayments;
    }
    

    public function buildAlternativePaymentMethod($builder, $request, $xml)
    {
        $request->appendChild($xml->createElement(
            "paymentmethod",
            $builder->paymentMethod->alternativePaymentMethodType
        ));
        
        $paymentMethodDetails = $xml->createElement("paymentmethoddetails");
        $paymentMethodDetails->appendChild(
            $xml->createElement("returnurl", $builder->paymentMethod->returnUrl)
        );
        $paymentMethodDetails->appendChild(
            $xml->createElement("statusupdateurl", $builder->paymentMethod->statusUpdateUrl)
        );
        
        if (!empty($builder->paymentMethod->descriptor)) {
            $paymentMethodDetails->appendChild(
                $xml->createElement("descriptor", $builder->paymentMethod->descriptor)
            );
        }

        $paymentMethodDetails->appendChild($xml->createElement("country", $builder->paymentMethod->country));
        $paymentMethodDetails->appendChild($xml->createElement(
            "accountholdername",
            $builder->paymentMethod->accountHolderName
        ));

        $request->appendChild($paymentMethodDetails);
        
        return;
    }
    
    private function mapAcceptedCodes($paymentMethodType)
    {
        switch ($paymentMethodType) {
            case "3ds-verifysig":
            case "3ds-verifyenrolled":
                return ["00", "110"];
            case PaymentMethodType::APM:
                return ["01"];
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
}
