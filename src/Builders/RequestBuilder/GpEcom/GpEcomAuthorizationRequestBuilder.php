<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpEcom;

use DOMDocument;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Request;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\Mapping\GpEcomMapping;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Utils\CardUtils;
use GlobalPayments\Api\Utils\GenerationUtils;

class GpEcomAuthorizationRequestBuilder extends GpEcomRequestBuilder
{
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
     * @param GpEcomConfig $config
     * @return Request
     */
    public function buildRequest(BaseBuilder $builder, GpEcomConfig $config)
    {
        /** @var AuthorizationBuilder $builder */
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
        $transactionType = GpEcomMapping::mapAuthRequestType($builder);

        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $transactionType);

        $request->appendChild($xml->createElement("merchantid", $config->merchantId ?? ''));

        if ($config->accountId !== null) {
            $request->appendChild($xml->createElement("account", $config->accountId ?? ''));
        }
        if ($config->channel !== null && !($builder->paymentMethod instanceof AlternativePaymentMethod)) {
            $request->appendChild($xml->createElement("channel", $config->channel));
        }

        if (isset($builder->amount)) {
            $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)));
            $amount->setAttribute("currency", $builder->currency ?? '');
            $request->appendChild($amount);
        }

        // This needs to be figured out based on txn type and set to 0, 1 or MULTI
        if ($builder->transactionType === TransactionType::SALE || $builder->transactionType == TransactionType::AUTH) {
            $autoSettle = $builder->transactionType === TransactionType::SALE ? "1" : "0";
            $element = $xml->createElement("autosettle");
            $element->setAttribute("flag", $autoSettle);
            $request->appendChild($element);
        }

        $request->appendChild($xml->createElement("orderid", $orderId));

        // For Fraud Decision Manager
        if (!empty($builder->customerData)) {
            $customerValue = $builder->customerData;
            $customer = $xml->createElement("customer");
            $customer->appendChild($xml->createElement("customerid", $customerValue->id ?? ''));
            $customer->appendChild($xml->createElement("firstname", $customerValue->firstName ?? ''));
            $customer->appendChild($xml->createElement("lastname", $customerValue->lastName ?? ''));
            $customer->appendChild($xml->createElement("dateofbirth", $customerValue->dateOfBirth ?? ''));
            $customer->appendChild($xml->createElement("customerpassword", $customerValue->customerPassword ?? ''));
            $customer->appendChild($xml->createElement("email", $customerValue->email ?? ''));
            $customer->appendChild($xml->createElement("domainname", $customerValue->domainName ?? ''));
            $customer->appendChild($xml->createElement("devicefingerprint", $customerValue->deviceFingerPrint ?? ''));
            $customer->appendChild($xml->createElement("phonenumber", $customerValue->homePhone ?? ''));
            $request->appendChild($customer);
        }

        if (!empty($builder->productData)) {
            $prod = [];
            $productValues = $builder->productData;
            $products = $xml->createElement("products");

            foreach ($productValues as $prod) {
                $product = $xml->createElement("product");
                $product->appendChild($xml->createElement('product_id', $prod['product_id'] ?? ''));
                $product->appendChild($xml->createElement('productname', $prod['productname'] ?? ''));
                $product->appendChild($xml->createElement('quantity', $prod['quantity'] ?? ''));
                $product->appendChild($xml->createElement('unitprice', $prod['unitprice'] ?? ''));
                $product->appendChild($xml->createElement('gift', $prod['gift'] ?? ''));
                $product->appendChild($xml->createElement('type', $prod['type'] ?? ''));
                $product->appendChild($xml->createElement('risk', $prod['risk'] ?? ''));
                $product->appendChild($products);
                $request->appendChild($product);
            }
        }

        if ($builder->decisionManager !== null) {
            $dmValues = $builder->decisionManager;
            $fraud = $xml->createElement("fraud");
            $dm = $fraud->appendChild($xml->createElement('dm'));
            $dm->appendChild($xml->createElement('billtohostname', $dmValues->billToHostName ?? ''));
            $dm->appendChild($xml->createElement(
                'billtohttpbrowsercookiesaccepted',
                ($dmValues->billToHttpBrowserCookiesAccepted) != true ? 'false' : 'true'
            ));
            $dm->appendChild($xml->createElement('billtohttpbrowseremail', $dmValues->billToHttpBrowserEmail ?? ''));
            $dm->appendChild($xml->createElement('billtohttpbrowsertype', $dmValues->billToHttpBrowserType ?? ''));
            $dm->appendChild($xml->createElement('billtoipnetworkaddress', $dmValues->billToIpNetworkAddress ?? ''));
            $dm->appendChild($xml->createElement(
                'businessrulesscorethreshold',
                $dmValues->businessRulessCoreThresHold ?? ''
            ));
            $dm->appendChild($xml->createElement('billtopersonalid', $dmValues->billToPersonalId ?? ''));
            $dm->appendChild($xml->createElement('invoiceheadertendertype', $dmValues->invoiceHeaderTenderType ?? ''));
            $dm->appendChild($xml->createElement(
                'invoiceheaderisgift',
                ($dmValues->invoiceHeaderIsGift) != true ? 'false' : 'true'
            ));
            $dm->appendChild($xml->createElement('decisionmanagerprofile', $dmValues->decisionManagerProfile));
            $dm->appendChild($xml->createElement(
                'invoiceheaderreturnsaccepted',
                ($dmValues->invoiceHeaderReturnsAccepted) != true ? 'false' : 'true'
            ));
            $dm->appendChild($xml->createElement('itemhosthedge', $dmValues->itemHostHedge ?? ''));
            $dm->appendChild($xml->createElement('itemnonsensicalhedge', $dmValues->itemNonsensicalHedge ?? ''));
            $dm->appendChild($xml->createElement('itemobscenitieshedge', $dmValues->itemObscenitiesHedge ?? ''));
            $dm->appendChild($xml->createElement('itemphonehedge', $dmValues->itemPhoneHedge ?? ''));
            $dm->appendChild($xml->createElement('itemtimehedge', $dmValues->itemTimeHedge ?? ''));
            $dm->appendChild($xml->createElement('itemvelocityhedge', $dmValues->itemVelocityHedge ?? ''));
            $request->appendChild($dm);
        }

        if (!empty($builder->customData)) {
            $customValues = $builder->customData;
            $custom = $xml->createElement("custom");

            foreach ($customValues as $cust) {
                $custom->appendChild($xml->createElement('field01', $cust['field01'] ?? ''));
                $custom->appendChild($xml->createElement('field02', $cust['field02'] ?? ''));
                $custom->appendChild($xml->createElement('field03', $cust['field03'] ?? ''));
                $custom->appendChild($xml->createElement('field04', $cust['field04'] ?? ''));
                $request->appendChild($custom);
            }
        }

        // For DCC charge/auth
        if (!empty($builder->dccRateData)) {
            $dccinfo = $xml->createElement("dccinfo");
            $dccinfo->appendChild($xml->createElement(
                "ccp",
                !empty($builder->dccRateData->dccProcessor) ? $builder->dccRateData->dccProcessor : DccProcessor::FEXCO)
            );
            $dccinfo->appendChild($xml->createElement(
                "type",
                !empty($builder->dccRateData->dccType) ? $builder->dccRateData->dccType : "1")
            );
            $dccinfo->appendChild($xml->createElement("ratetype", $builder->dccRateData->dccRateType ?? ''));
            if ($builder->transactionType !== TransactionType::DCC_RATE_LOOKUP) {
                $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', $builder->dccRateData->cardHolderAmount));
                $amount->setAttribute("currency", $builder->dccRateData->cardHolderCurrency ?? '');
                $dccinfo->appendChild($amount);
                $dccinfo->appendChild($xml->createElement("rate", $builder->dccRateData->cardHolderRate ?? ''));
            }
            $request->appendChild($dccinfo);
        }

        if (
            (
                $builder->transactionType === TransactionType::AUTH ||
                $builder->transactionType === TransactionType::CAPTURE ||
                $builder->transactionType === TransactionType::REFUND
            ) &&
            !empty($builder->dynamicDescriptor)
        ) {
            $narrative = $xml->createElement("narrative");
            $narrative->appendChild($xml->createElement("chargedescription", strtoupper($builder->dynamicDescriptor)));
            $request->appendChild($narrative);
        }

        // Hydrate the payment data fields
        if ($builder->paymentMethod instanceof CreditCardData) {
            $card = $builder->paymentMethod;

            if ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                $request->appendChild($xml->createElement("token", $card->token ?? ''));
                $request->appendChild($xml->createElement("mobile", $card->mobileType ?? ''));
            } else {
                $cardElement = $xml->createElement("card");
                $cardElement->appendChild($xml->createElement("number", $card->number ?? ''));
                $cardElement->appendChild($xml->createElement("expdate", $card->getShortExpiry() ?? ''));
                $cardElement->appendChild($xml->createElement("chname", $card->cardHolderName ?? ''));

                $cardElement->appendChild($xml->createElement(
                    "type",
                    strtoupper(EnumMapping::mapCardType(GatewayProvider::GP_ECOM, CardUtils::getBaseCardType($card->getCardType())))
                ));

                if ($card->cvn !== null || isset($card->cvnPresenceIndicator)) {
                    //if cvn number is not empty indicator should be PRESENT
                    $cvnPresenceIndicator = (!empty($card->cvn)) ?
                        CvnPresenceIndicator::PRESENT:
                        $card->cvnPresenceIndicator;

                    $cvnElement = $xml->createElement("cvn");
                    $cvnElement->appendChild($xml->createElement("number", $card->cvn ?? ''));
                    $cvnElement->appendChild($xml->createElement("presind", $cvnPresenceIndicator ?? ''));
                    $cardElement->appendChild($cvnElement);
                }
                $request->appendChild($cardElement);
            }
            // issueno
            $hash = '';
            if ($builder->transactionType === TransactionType::VERIFY) {
                $hash = GenerationUtils::generateHash(
                    $config->sharedSecret,
                    implode('.', [
                        $timestamp,
                        $config->merchantId,
                        $orderId,
                        $card->number
                    ])
                );
            } else {
                $requestValues = $this->getShal1RequestValues($timestamp, $orderId, $builder, $card, $config);

                $hash = GenerationUtils::generateHash(
                    $config->sharedSecret,
                    implode('.', $requestValues)
                );
            }

            $request->appendChild($xml->createElement("sha1hash", $hash));
        }

        if ($builder->paymentMethod instanceof RecurringPaymentMethod) {
            $recurring = $builder->paymentMethod;
            $request->appendChild($xml->createElement("payerref", $recurring->customerKey ?? ''));
            $request->appendChild($xml->createElement(
                "paymentmethod",
                isset($recurring->key) ? $recurring->key : (string) $recurring->id
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
                        $config->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $config->merchantId,
                            $orderId,
                            preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
                            $builder->currency,
                            $recurring->customerKey,
                        ])
                    );
                } else {
                    $hash = GenerationUtils::generateHash(
                        $config->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $config->merchantId,
                            $orderId,
                            $recurring->customerKey,
                        ])
                    );
                }
            } else {
                $hash = GenerationUtils::generateHash(
                    $config->sharedSecret,
                    implode('.', [
                        $timestamp,
                        $config->merchantId,
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
                GenerationUtils::generateHash($config->refundPassword) ?: ''
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
                $config->sharedSecret,
                implode('.', [
                    $timestamp,
                    $config->merchantId,
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
            $storedCredential->appendChild($xml->createElement("type", $builder->storedCredential->type ?? ''));
            $storedCredential->appendChild($xml->createElement("initiator", $builder->storedCredential->initiator ?? ''));
            $storedCredential->appendChild($xml->createElement("sequence", $builder->storedCredential->sequence ?? ''));
            $storedCredential->appendChild($xml->createElement("srd", $builder->storedCredential->schemeId ?? ''));
            $request->appendChild($storedCredential);
        }

        //Supplementary Data
        if (!empty($builder->supplementaryData)) {
            $this->buildSupplementaryData($builder, $xml,$request);
        }

        // mpi
        if (!empty($builder->paymentMethod->threeDSecure)) {
            $secureEcom = $builder->paymentMethod->threeDSecure;
            $mpi = $xml->createElement("mpi");
            $mpi->appendChild($xml->createElement("eci", $secureEcom->eci ?? ''));
            $mpi->appendChild($xml->createElement("cavv", $secureEcom->cavv ?? ''));
            $mpi->appendChild($xml->createElement("xid", $secureEcom->xid ?? ''));

            if (
                $secureEcom->directoryServerTransactionId != null ||
                $secureEcom->authenticationValue != null ||
                $secureEcom->messageVersion != null
            ) {
                $mpi->appendChild($xml->createElement("ds_trans_id", $secureEcom->directoryServerTransactionId ?? ''));
                $mpi->appendChild($xml->createElement("authentication_value", $secureEcom->authenticationValue ?? ''));
                $mpi->appendChild($xml->createElement("message_version", $secureEcom->messageVersion ?? ''));
            }
            if ($secureEcom->exemptStatus != null) {
                $mpi->appendChild($xml->createElement("exempt_status", $secureEcom->exemptStatus));
            }
            $request->appendChild($mpi);
        }

        return new Request('', 'POST', $xml->saveXML($request));
    }

    public function buildAlternativePaymentMethod($builder, $request, $xml)
    {
        $request->appendChild($xml->createElement(
            "paymentmethod",
            $builder->paymentMethod->alternativePaymentMethodType ?? ''
        ));

        $paymentMethodDetails = $xml->createElement("paymentmethoddetails");
        list($returnUrl, $statusUpdateUrl, $cancelUrl) =
            $this->mapAPMUrls($builder->paymentMethod->alternativePaymentMethodType);
        $paymentMethodDetails->appendChild(
            $xml->createElement($returnUrl, $builder->paymentMethod->returnUrl ?? '')
        );
        $paymentMethodDetails->appendChild(
            $xml->createElement($statusUpdateUrl, $builder->paymentMethod->statusUpdateUrl ?? '')
        );
        if (!empty($builder->paymentMethod->cancelUrl)) {
            $paymentMethodDetails->appendChild(
                $xml->createElement($cancelUrl, $builder->paymentMethod->cancelUrl)
            );
        }

        if (!empty($builder->paymentMethod->descriptor)) {
            $paymentMethodDetails->appendChild(
                $xml->createElement("descriptor", $builder->paymentMethod->descriptor)
            );
        }

        $paymentMethodDetails->appendChild($xml->createElement("country", $builder->paymentMethod->country ?? ''));
        $paymentMethodDetails->appendChild($xml->createElement(
            "accountholdername",
            $builder->paymentMethod->accountHolderName ?? ''
        ));

        $request->appendChild($paymentMethodDetails);
    }

    public function buildFraudFilter($builder, $xml, $request)
    {
        // tssinfo fraudfilter
        // fraudfilter
        if (!empty($builder->fraudFilter)) {
            $fraudFilter = $xml->createElement("fraudfilter");
            $fraudFilter->setAttribute("mode", $builder->fraudFilter);
            if (!empty($builder->fraudRules)) {
                $rules = $xml->createElement("rules");
                foreach ($builder->fraudRules as $fraudRule) {
                    $rule = $xml->createElement("rule");
                    $rule->setAttribute("id", $fraudRule->key);
                    $rule->setAttribute("mode", $fraudRule->mode);
                    $rules->appendChild($rule);
                }
                $fraudFilter->appendChild($rules);
            }
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
                $billingAddress->appendChild($xml->createElement("code", $builder->billingAddress->postalCode ?? ''));
                $billingAddress->appendChild($xml->createElement("country", $builder->billingAddress->countryCode ?? ''));
                $tssInfo->appendChild($billingAddress);
            }

            if (!empty($builder->shippingAddress)) {
                $shippingAddress = $xml->createElement("address");
                $shippingAddress->setAttribute("type", 'shipping');
                $shippingAddress->appendChild($xml->createElement("code", $builder->shippingAddress->postalCode ?? ''));
                $shippingAddress->appendChild($xml->createElement("country", $builder->shippingAddress->countryCode ?? ''));
                $tssInfo->appendChild($shippingAddress);
            }
            if (!empty($tssInfo->childNodes->length)) {
                $request->appendChild($tssInfo);
            }
        }
    }

    private function mapAPMUrls($paymentMethodType)
    {
        switch ($paymentMethodType) {
            case AlternativePaymentType::PAYPAL:
                return ['ReturnURL', 'StatusUpdateURL', 'CancelURL'];
            default:
                return ['returnurl', 'statusupdateurl', 'cancelurl'];
        }
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
     * @param GpEcomConfig $config
     *
     * @return array
     */
    private function getShal1RequestValues($timestamp, $orderId, $builder, $card, $config)
    {
        $requestValues = [
            $timestamp,
            $config->merchantId,
            $orderId,
            preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
            $builder->currency,
            $card->number
        ];

        if (($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE)) {
            switch ($card->mobileType) {
                case EncyptedMobileType::GOOGLE_PAY:
                case EncyptedMobileType::APPLE_PAY:
                    $requestValues = [
                        $timestamp,
                        $config->merchantId,
                        $orderId,
                        preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)),
                        $builder->currency,
                        $card->token
                    ];
                    break;
                default:
                    break;
            }
        }
        return $requestValues;
    }
}