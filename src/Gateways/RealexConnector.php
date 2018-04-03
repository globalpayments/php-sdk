<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;

class RealexConnector extends XmlGateway implements IPaymentGateway, IRecurringService
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
     * {@inheritdoc}
     *
     * @param AuthorizationBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function processAuthorization(AuthorizationBuilder $builder)
    {        
        //for google payment amount and currency is required
        if (!empty($builder->transactionModifier) && $builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE &&
            $builder->paymentMethod->mobileType === EncyptedMobileType::GOOGLE_PAY &&
            (empty($builder->amount) || empty($builder->currency))
        ) {
            throw new BuilderException("Amount and Currency cannot be null for google payment");
        }

        $xml = new DOMDocument();
        $timestamp = isset($builder->timestamp) ? $builder->timestamp : GenerationUtils::generateTimestamp();
        $orderId = isset($builder->orderId) ? $builder->orderId : GenerationUtils::generateOrderId();

        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $this->mapAuthRequestType($builder));

        $request->appendChild($xml->createElement("merchantid", $this->merchantId));
        $request->appendChild($xml->createElement("account", $this->accountId));
        if ($this->channel !== null) {
            $request->appendChild($xml->createElement("channel", $this->channel));
        }
        $request->appendChild($xml->createElement("orderid", $orderId));

        if (isset($builder->amount)) {
            $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)));
            $amount->setAttribute("currency", $builder->currency);
            $request->appendChild($amount);
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
                    $cvnElement = $xml->createElement("cvn");
                    $cvnElement->appendChild($xml->createElement("number", $card->cvn));
                    $cvnElement->appendChild($xml->createElement("presind", $card->cvnPresenceIndicator));
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
                $hash = GenerationUtils::generateHash(
                    $this->sharedSecret,
                    implode('.', [
                        $timestamp,
                        $this->merchantId,
                        $orderId,
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

        // This needs to be figured out based on txn type and set to 0, 1 or MULTI
        if ($builder->transactionType === TransactionType::SALE || $builder->transactionType == TransactionType::AUTH) {
            $autoSettle = $builder->transactionType === TransactionType::SALE ? "1" : "0";
            $element = $xml->createElement("autosettle");
            $element->setAttribute("flag", $autoSettle);
            $request->appendChild($element);
        }

        // comment ...TODO: needs to be multiple
        if ($builder->description != null) {
            $comments = $xml->createElement("comments");
            $comment = $xml->createElement("comment", $builder->description);
            $comment->setAttribute("id", "1");
            $comments->appendChild($comment);
            $request->appendChild($comments);
        }

        // TODO: fraudfilter
        if ($builder->recurringType !== null || $builder->recurringSequence !== null) {
            $recurring = $xml->createElement("recurring");
            $recurring->setAttribute("type", strtolower($builder->recurringType));
            $recurring->setAttribute("sequence", strtolower($builder->recurringSequence));
            $request->appendChild($recurring);
        }

        // tssinfo
        if ($builder->customerId !== null
            || $builder->productId !== null
            || $builder->customerId !== null
            || $builder->clientTransactionId !== null
        ) {
            $tssInfo = $xml->createElement("tssinfo");
            $tssInfo->appendChild($xml->createElement("custnum", $builder->customerId));
            $tssInfo->appendChild($xml->createElement("prodid", $builder->productId));
            $tssInfo->appendChild($xml->createElement("varref", $builder->clientTransactionId));
            $tssInfo->appendChild($xml->createElement("custipaddress", $builder->customerIpAddress));
            //$tssInfo->appendChild($xml->createElement("address", ""));
            $request->appendChild($tssInfo);
        }

        // TODO: mpi
        if ($builder->ecommerceInfo !== null) {
            $mpi = $xml->createElement("mpi");
            $mpi->appendChild($xml->createElement("cavv", $builder->ecommerceInfo->cavv));
            $mpi->appendChild($xml->createElement("xid", $builder->ecommerceInfo->xid));
            $mpi->appendChild($xml->createElement("eci", $builder->ecommerceInfo->eci));
            $request->appendChild($mpi);
        }

        $response = $this->doTransaction($xml->saveXML($request));
        return $this->mapResponse($response);
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        // check for hpp config
        if ($this->hostedPaymentConfig === null) {
            throw new ApiException("Hosted configuration missing, Please check you configuration.");
        }

        $encoder = ($this->hostedPaymentConfig->version === HppVersion::VERSION_2) ? null : JsonEncoders::Base64Encoder;
        $request = [];

        $orderId = isset($builder->orderId) ? $builder->orderId : GenerationUtils::generateOrderId();
        $timestamp = isset($builder->timestamp) ? $builder->timestamp : GenerationUtils::generateTimestamp();

        // check for right transaction types
        if ($builder->transactionType !== TransactionType::SALE
            && $builder->transactionType !== TransactionType::AUTH
            && $builder->transactionType !== TransactionType::VERIFY
        ) {
            throw new UnsupportedTransactionException("Only Charge and Authorize are supported through HPP.");
        }        
        
        $request["MERCHANT_ID"] = $this->merchantId;
        $request["ACCOUNT"] = $this->accountId;
        $request["CHANNEL"] = $this->channel;
        $request["ORDER_ID"] = $orderId;
        if ($builder->amount !== null) {
            $request["AMOUNT"] = preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount));
        }
        $request["CURRENCY"] = $builder->currency;
        $request["TIMESTAMP"] = timestamp;
        $request["AUTO_SETTLE_FLAG"] = ($builder->transactionType == TransactionType::Sale) ? "1" : "0";
        $request["COMMENT1"] = $builder->description;
        // $request["COMMENT2"] = ;
        if ($this->hostedPaymentConfig->requestTransactionStabilityScore) {
            $request["RETURN_TSS"] = $this->hostedPaymentConfig->requestTransactionStabilityScore ? "1" : "0";
        }
        if ($this->hostedPaymentConfig->directCurrencyConversionEnabled) {
            $request["DCC_ENABLE"] = $this->hostedPaymentConfig->directCurrencyConversionEnabled ? "1" : "0";
        }
        if ($builder->hostedPaymentData !== null) {
            $request["CUST_NUM"] = $builder->hostedPaymentData->customerNumber;
            if ($this->hostedPaymentConfig->displaySavedCards && $builder->hostedPaymentData->customerKey !== null) {
                $request["HPP_SELECT_STORED_CARD"] = $builder->hostedPaymentData->customerKey;
            }
            if ($builder->hostedPaymentData->offerToSaveCard) {
                $request["OFFER_SAVE_CARD"] = $builder->hostedPaymentData->offerToSaveCard ? "1" : "0";
            }
            if ($builder->hostedPaymentData->customerExists) {
                $request["PAYER_EXIST"] = $builder->hostedPaymentData->customerExists ? "1" : "0";
            }
            $request["PAYER_REF"] = $builder->hostedPaymentData->customerKey;
            $request["PMT_REF"] = $builder->hostedPaymentData->paymentKey;
            $request["PROD_ID"] = $builder->hostedPaymentData->productId;
        }
        if ($builder->shippingAddress !== null) {
            $request["SHIPPING_CODE"] = $builder->shippingAddress->postalCode;
            $request["SHIPPING_CO"] = $builder->shippingAddress->country;
        }
        if ($builder->billingAddress !== null) {
            $request["BILLING_CODE"] = $builder->billingAddress->postalCode;
            $request["BILLING_CO"] = $builder->billingAddress->country;
        }
        $request["CUST_NUM"] = $builder->customerId;
        $request["VAR_REF"] = $builder->clientTransactionId;
        $request["HPP_LANG"] = $this->hostedPaymentConfig->Language;
        $request["MERCHANT_RESPONSE_URL"] = $this->hostedPaymentConfig->responseUrl;
        $request["CARD_PAYMENT_BUTTON"] = $this->hostedPaymentConfig->paymentButtonText;
        if ($this->hostedPaymentConfig->cardStorageEnabled) {
            $request["CARD_STORAGE_ENABLE"] = $this->hostedPaymentConfig->cardStorageEnabled ? "1" : "0";
        }
        if ($builder->transactionType === TransactionType::VERIFY) {
            $request["VALIDATE_CARD_ONLY"] = $builder->transactionType === TransactionType::VERIFY ? "1" : "0";
        }
        $request["HPP_FRAUD_FILTER_MODE"] = $this->hostedPaymentConfig->FraudFilterMode.ToString();
        if ($builder->recurringType !== null || $builder->recurringSequence !== null) {
            $request["RECURRING_TYPE"] = strtolower($builder->recurringType);
            $request["RECURRING_SEQUENCE"] = strtolower($builder->recurringSequence);
        }
        $request["HPP_VERSION"] = $this->hostedPaymentConfig->version;

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

        $request["SHA1HASH"] = GenerationUtils::generateHash($this->sharedSecret, implode('.', $toHash));
        return json_encode($request);
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

        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $this->mapManageRequestType($builder));

        $request->appendChild($xml->createElement("merchantid", $this->merchantId));
        $request->appendChild($xml->createElement("account", $this->accountId));
        $request->appendChild($xml->createElement("channel", $this->channel));
        $request->appendChild($xml->createElement("orderid", $orderId));
        $request->appendChild($xml->createElement("pasref", $builder->transactionId));
        if ($builder->amount !== null) {
            $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)));
            $amount->setAttribute("currency", $builder->currency);
            $request->appendChild($amount);
        } elseif ($builder->transactionType === TransactionType::CAPTURE) {
            throw new BuilderException("Amount cannot be null for capture.");
        }

        // rebate hash
        if ($builder->transactionType === TransactionType::REFUND) {
            $request->appendChild($xml->createElement("authcode", $builder->authorizationCode));
            $request->appendChild(
                $xml->createElement(
                    "refundhash",
                    GenerationUtils::generateHash(isset($this->rebatePassword) ? $this->rebatePassword : '')
                )
            );
        }

        // reason code
        if ($builder->reasonCode !== null) {
            $request->appendChild($xml->createElement("reasoncode", $builder->reasonCode));
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
            '',
        ];

        $request->appendChild(
            $xml->createElement(
                "sha1hash",
                GenerationUtils::generateHash($this->sharedSecret, implode('.', $toHash))
            )
        );

        $response = $this->doTransaction($xml->saveXML($request));
        return $this->mapResponse($response);
    }

    public function processReport(ReportBuilder $builder)
    {
        throw new UnsupportedTransactionException(
            'Reporting functionality is not supported through this gateway.'
        );
    }

    public function processRecurring(RecurringBuilder $builder)
    {
        // $et = new ElementTree();
        // string timestamp = GenerationUtils::generateTimestamp();
        // string orderId = $builder->orderId ?? GenerationUtils::generateOrderId();

        // // Build Request
        // $request = $xml->createElement("request")
        //     .Set("type", MapRecurringRequestType(builder))
        //     .Set("timestamp", timestamp);
        // $request->appendChild($xml->createElement("merchantid", MerchantId);
        // $request->appendChild($xml->createElement("account", AccountId);
        // $request->appendChild($xml->createElement("orderid", orderId);

        // if($builder->transactionType== TransactionType::Create || $builder->transactionType == TransactionType::Edit)
        //	{
        //     if ($builder->entity is Customer) {
        //         $customer = $builder->entity as Customer;
        //         request.Append(BuildCustomer(et, customer));
        //         $request->appendChild($xml->createElement("sha1hash",
        //              GenerationUtils::generateHash(SharedSecret, timestamp, MerchantId,
        //					orderId, null, null, customer.Key));
        //     }
        //     else if ($builder->entity is RecurringPaymentMethod) {
        //         $payment = $builder->entity as RecurringPaymentMethod;
        //         $cardElement = $request->appendChild($xml->createElement("card");
        //         et.SubElement(cardElement, "ref", payment.Key ?? payment.Id);
        //         et.SubElement(cardElement, "payerref", payment.CustomerKey);

        //         if (payment.PaymentMethod != null) {
        //             $card = payment.PaymentMethod as CreditCardData;
        //             string expiry = card.ShortExpiry;
        //             et.SubElement(cardElement, "number", card.Number);
        //             et.SubElement(cardElement, "expdate", expiry);
        //             et.SubElement(cardElement, "chname", card.CardHolderName);
        //             et.SubElement(cardElement, "type", card.CardType);

        //             string sha1hash = string.Empty;
        //             if ($builder->transactionType == TransactionType::Create)
        //                 sha1hash = GenerationUtils::generateHash(SharedSecret, timestamp, MerchantId, orderId, null,
        //								null, payment.CustomerKey, card.CardHolderName, card.Number);
        //             else sha1hash = GenerationUtils::generateHash(SharedSecret, timestamp, MerchantId,
        //								payment.CustomerKey, payment.Key ?? payment.Id, expiry, card.Number);
        //             $request->appendChild($xml->createElement("sha1hash", sha1hash);
        //         }
        //     }
        // }
        // else if ($builder->transactionType == TransactionType::Delete) {
        //     if ($builder->entity is RecurringPaymentMethod) {
        //         $payment = $builder->entity as RecurringPaymentMethod;
        //         $cardElement = $request->appendChild($xml->createElement("card");
        //         et.SubElement(cardElement, "ref", payment.Key ?? payment.Id);
        //         et.SubElement(cardElement, "payerref", payment.CustomerKey);
        //     }
        // }

        // $response = DoTransaction(et.ToString(request));
        // return MapRecurringResponse<TResult>(response, builder);
    }

    /**
     * Deserializes the gateway's XML response
     *
     * @param string $rawResponse The XML response
     *
     * @return Transaction
     */
    protected function mapResponse($rawResponse)
    {
        $result = new Transaction();

        $root = $this->xml2object($rawResponse);

        $this->checkResponse($root);

        $result->responseCode = (string)$root->result;
        $result->responseMessage = (string)$root->message;
        $result->cvnResponseCode = (string)$root->cvnresult;
        $result->avsResponseCode = (string)$root->avspostcoderesponse;
        $result->transactionReference = new TransactionReference();
        $result->transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
        $result->transactionReference->transactionId = (string)$root->pasref;
        $result->transactionReference->authCode = (string)$root->authcode;
        $result->transactionReference->orderId = (string)$root->orderid;

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
                sprintf('Unexpected Gateway Response: %s - %s', $responseCode, $responseMessage)
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
            implode('.', $data),
            $this->sharedSecret
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
                if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                    return 'offline';
                } elseif ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                    return 'auth-mobile';
                }
                return 'auth';
            case TransactionType::CAPTURE:
                return 'settle';
            case TransactionType::VERIFY:
                return 'otb';
            case TransactionType::REFUND:
                return 'credit';
            case TransactionType::REVERSAL:
                // TODO: should be customer type
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
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
                return 'rebate';
            case TransactionType::RELEASE:
                return 'release';
            case TransactionType::VOID:
            case TransactionType::REVERSAL:
                return 'void';
            default:
                return 'unknown';
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
}
