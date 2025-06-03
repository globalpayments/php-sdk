<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use DOMElement;
use Exception;
use GlobalPayments\Api\Builders\{
    AuthorizationBuilder,
    BaseBuilder,
    ManagementBuilder,
    ReportBuilder,
    TransactionReportBuilder
};
use GlobalPayments\Api\Entities\{
    BatchSummary,
    Transaction,
    Address,
    LodgingData,
    ThreeDSecure
};
use GlobalPayments\Api\Entities\Enums\{
    AccountType,
    AliasAction,
    CheckType,
    EntryMethod,
    PaymentMethodType,
    PaymentDataSourceType,
    ReportType,
    Secure3dVersion,
    StoredCredentialInitiator,
    TaxType,
    TransactionModifier,
    TransactionType
};
use GlobalPayments\Api\Entities\Exceptions\{
    BuilderException,
    GatewayException,
    NotImplementedException,
    UnsupportedTransactionException
};
use GlobalPayments\Api\Entities\Reporting\{
    AltPaymentData,
    AltPaymentProcessorInfo,
    CheckData,
    TransactionSummary
};
use GlobalPayments\Api\PaymentMethods\{CreditCardData, ECheck, GiftCard};
use GlobalPayments\Api\PaymentMethods\Interfaces\{
    IBalanceable,
    ICardData,
    IEncryptable,
    IPaymentMethod,
    IPinProtected,
    ITokenizable,
    ITrackData
};
use GlobalPayments\Api\PaymentMethods\{RecurringPaymentMethod, TransactionReference};
use GlobalPayments\Api\Entities\PayFac\PayFacResponseData;

class PorticoConnector extends XmlGateway implements IPaymentGateway
{
    /**
     * Portico's XML namespace
     *
     * @var string
     */
    const XML_NAMESPACE = 'http://Hps.Exchange.PosGateway';

    /**
     * Site ID to authenticate with the gateway
     *
     * @var string
     */
    public $siteId;

    /**
     * License ID to authenticate with the gateway
     *
     * @var string
     */
    public $licenseId;

    /**
     * Device ID to authenticate with the gateway
     *
     * @var string
     */
    public $deviceId;

    /**
     * Username to authenticate with the gateway
     *
     * @var string
     */
    public $username;

    /**
     * Password to authenticate with the gateway
     *
     * @var string
     */
    public $password;

    /**
     * Secret API Key to authenticate with the gateway.
     *
     * This can be used in place of the following properties:
     *
     * - username
     * - password
     * - siteId
     * - licenseId
     * - deviceId
     *
     * @var string
     */
    public $secretApiKey;

    /**
     * Developer ID for the application, as given during certification
     *
     * @var string
     */
    public $developerId;

    /**
     * Version number for the application, as given during certification
     *
     * @var string
     */
    public $versionNumber;

    public $supportsHostedPayments = false;

    /**
     * A client-generated transaction id; limit 50 characters
     *
     * @var string
     */
    public $clientTransactionId;

    /** @var string */
    public $uniqueDeviceId;

    /**
     * Indicates the version of this SDK used to send a gateway request.
     * 
     * @var string
     */
    public $sdkNameVersion;

    public function supportsOpenBanking() : bool
    {
        return false;
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
        $xml = new DOMDocument('1.0', 'utf-8');

        $transaction = $xml->createElement($this->mapRequestType($builder));
        $block1 = $xml->createElement('Block1');

        if (
            $builder->paymentMethod->paymentMethodType !== PaymentMethodType::GIFT
            && $builder->paymentMethod->paymentMethodType !== PaymentMethodType::ACH
            && (
                $builder->transactionType === TransactionType::AUTH
                || $builder->transactionType === TransactionType::SALE
                || $builder->transactionType === TransactionType::REFUND
            )
        ) {
            if (
                $builder->paymentMethod->paymentMethodType !== PaymentMethodType::RECURRING
                || $builder->paymentMethod->paymentType !== 'ACH'
            ) {
                $block1->appendChild(
                    $xml->createElement(
                        'AllowDup',
                        ($builder->allowDuplicates ? 'Y' : 'N')
                    )
                );
            }

            if (
                $builder->transactionModifier === TransactionModifier::NONE
                && $builder->paymentMethod->paymentMethodType !== PaymentMethodType::EBT
                && $builder->paymentMethod->paymentMethodType !== PaymentMethodType::RECURRING
                && $builder->transactionType !== TransactionType::REFUND
            ) {
                $block1->appendChild(
                    $xml->createElement(
                        'AllowPartialAuth',
                        ($builder->allowPartialAuth ? 'Y' : 'N')
                    )
                );
            }

            if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT 
                && $builder->transactionModifier === TransactionModifier::NONE
                && $builder->amountEstimated !== null
            ) {
                $block1->appendChild(
                    $xml->createElement(
                        'AmountIndicator',
                        ($builder->amountEstimated ? 'E' : 'F')
                    )
                );
            }
        }

        if ($builder->amount !== null) {
            $block1->appendChild($xml->createElement('Amt', $builder->amount));
        }

        if ($builder->gratuity !== null) {
            $block1->appendChild(
                $xml->createElement('GratuityAmtInfo', $builder->gratuity)
            );
        }

        if ($builder->convenienceAmount !== null) {
            $block1->appendChild($xml->createElement('ConvenienceAmtInfo', $builder->convenienceAmount));
        }

        if ($builder->shippingAmount !== null) {
            $block1->appendChild($xml->createElement('ShippingAmtInfo', $builder->shippingAmount));
        }

        if ($builder->surchargeAmount !== null) {
            $block1->appendChild($xml->createElement('SurchargeAmtInfo', $builder->surchargeAmount));
        }

        if ($builder->cashBackAmount !== null) {
            $block1->appendChild(
                $xml->createElement(
                    $builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT
                        ? 'CashbackAmtInfo'
                        : 'CashBackAmount',
                    $builder->cashBackAmount
                )
            );
        }

        if ($builder->offlineAuthCode !== null) {
            $block1->appendChild(
                $xml->createElement('OfflineAuthCode', $builder->offlineAuthCode)
            );
        }

        if ($builder->transactionType === TransactionType::ALIAS) {
            $block1->appendChild($xml->createElement('Action', AliasAction::validate($builder->aliasAction)));
            $block1->appendChild($xml->createElement('Alias', $builder->alias));
        }

        $isCheck = ($builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH)
            || ($builder->paymentMethod instanceof RecurringPaymentMethod
                && $builder->paymentMethod->paymentType === 'ACH');

        $propertyName = $isCheck ? 'checkHolderName' : 'cardHolderName';
        if (
            $isCheck
            || $builder->billingAddress !== null
            || isset($builder->paymentMethod->{$propertyName})
            || ($builder->customerData !== null && $builder->customerData->email !== null)
        ) {
            if ($builder->transactionType !== TransactionType::REVERSAL) {
                $holder = $this->hydrateHolder($xml, $builder, $isCheck);
            }
            if (!empty($holder)) {
                $block1->appendChild($holder);
            }          
        }
        list($hasToken, $tokenValue) = $this->hasToken($builder->paymentMethod);

        $cardData = $xml->createElement(
            $builder->transactionType === TransactionType::REPLACE ? 'OldCardData' : 'CardData'
        );
        if ($builder->paymentMethod instanceof ICardData) {
            if ($builder->transactionInitiator !== null) {
                //card on file request
                $intiator = ($builder->transactionInitiator === StoredCredentialInitiator::CARDHOLDER) ? 'C' : 'M';
                $cardOnFileData = $xml->createElement('CardOnFileData');
                $cardOnFileData->appendChild($xml->createElement('CardOnFile', $intiator));

                if (!empty($builder->cardBrandTransactionId)) {
                    $cardOnFileData->appendChild($xml->createElement('CardBrandTxnId', $builder->cardBrandTransactionId));
                }

                if (!empty($builder->categoryIndicator)) {
                    $cardOnFileData->appendChild($xml->createElement('CategoryInd', $builder->categoryIndicator));
                }

                $block1->appendChild($cardOnFileData);
            }

            $cardData->appendChild(
                $this->hydrateManualEntry(
                    $xml,
                    $builder,
                    $hasToken,
                    $tokenValue
                )
            );
        } elseif ($builder->paymentMethod instanceof ITrackData) {
            $trackData = $this->hydrateTrackData(
                $xml,
                $builder,
                $hasToken,
                $tokenValue
            );

            if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                $block1->appendChild($trackData);
            } else {
                $cardData->appendChild($trackData);
            }
        } elseif ($builder->paymentMethod instanceof GiftCard) {
            if ($builder->currency !== null) {
                $block1->appendChild($xml->createElement('Currency', strtoupper($builder->currency)));
            }

            if ($builder->transactionType === TransactionType::REPLACE) {
                $newCard = $xml->createElement('NewCardData');
                $newCard->appendChild(
                    $xml->createElement(
                        $builder->replacementCard->valueType,
                        $builder->replacementCard->value
                    )
                );

                if ($builder->replacementCard->pin !== null) {
                    $newCard->appendChild(
                        $xml->createElement(
                            'PIN',
                            $builder->replacementCard->pin
                        )
                    );
                }
                $block1->appendChild($newCard);
            }

            if ($builder->paymentMethod->value !== null) {
                $cardData->appendChild(
                    $xml->createElement(
                        $builder->paymentMethod->valueType,
                        $builder->paymentMethod->value
                    )
                );
            }

            if ($builder->paymentMethod->pin !== null) {
                $cardData->appendChild(
                    $xml->createElement(
                        'PIN',
                        $builder->paymentMethod->pin
                    )
                );
            }
        } elseif ($builder->paymentMethod instanceof ECheck) {
            $block1->appendChild($xml->createElement('CheckAction', 'SALE'));

            if (empty($builder->paymentMethod->token)) {
                $accountInfo = $xml->createElement('AccountInfo');
                $accountInfo->appendChild($xml->createElement('RoutingNumber', $builder->paymentMethod->routingNumber ?? ''));
                $accountInfo->appendChild($xml->createElement('AccountNumber', $builder->paymentMethod->accountNumber ?? ''));
                $accountInfo->appendChild($xml->createElement('CheckNumber', $builder->paymentMethod->checkNumber ?? ''));
                $accountInfo->appendChild($xml->createElement('MICRData', $builder->paymentMethod->micrNumber ?? ''));
                $accountInfo->appendChild(
                    $xml->createElement(
                        'AccountType',
                        $this->hydrateAccountType($builder->paymentMethod->accountType)
                    )
                );
                $block1->appendChild($accountInfo);
            } else {
                $accountInfo = $xml->createElement('AccountInfo');
                $accountInfo->appendChild($xml->createElement('CheckNumber', $builder->paymentMethod->checkNumber));
                $accountInfo->appendChild($xml->createElement('MICRData', $builder->paymentMethod->micrNumber));
                $accountInfo->appendChild(
                    $xml->createElement(
                        'AccountType',
                        $this->hydrateAccountType($builder->paymentMethod->accountType)
                    )
                );
                $block1->appendChild($accountInfo);
                $block1->appendChild($xml->createElement('TokenValue', $builder->paymentMethod->token));
            }

            $block1->appendChild(
                $xml->createElement(
                    'DataEntryMode',
                    strtoupper($this->hydrateEntryMethod($builder->paymentMethod->entryMode))
                )
            );
            $block1->appendChild(
                $xml->createElement(
                    'CheckType',
                    $this->hydrateCheckType($builder->paymentMethod->checkType)
                )
            );
            $block1->appendChild($xml->createElement('SECCode', $builder->paymentMethod->secCode));

            $verify = $xml->createElement('VerifyInfo');
            $verify->appendChild(
                $xml->createElement(
                    'CheckVerify',
                    ($builder->paymentMethod->checkVerify ? 'Y' : 'N')
                )
            );
            $verify->appendChild(
                $xml->createElement(
                    'ACHVerify',
                    ($builder->paymentMethod->achVerify ? 'Y' : 'N')
                )
            );
            $block1->appendChild($verify);
        }

        if ($builder->paymentMethod instanceof TransactionReference) {
            $block1->appendChild($xml->createElement('GatewayTxnId', $builder->paymentMethod->transactionId));
            $block1->appendChild($xml->createElement('ClientTxnId', $builder->paymentMethod->clientTransactionId));
        }

        if ($builder->paymentMethod instanceof RecurringPaymentMethod) {
            $method = $builder->paymentMethod;
            if ($builder->transactionInitiator !== null) {
                //card on file request
                $intiator = ($builder->transactionInitiator === StoredCredentialInitiator::CARDHOLDER) ? 'C' : 'M';
                $cardOnFileData = $xml->createElement('CardOnFileData');
                $cardOnFileData->appendChild($xml->createElement('CardOnFile', $intiator));

                if (!empty($builder->cardBrandTransactionId)) {
                    $cardOnFileData->appendChild($xml->createElement('CardBrandTxnId', $builder->cardBrandTransactionId));
                }
                $block1->appendChild($cardOnFileData);
            }


            if ($method->paymentType === 'ACH') {
                $block1->appendChild($xml->createElement('CheckAction', 'SALE'));
            }

            $block1->appendChild($xml->createElement('PaymentMethodKey', $method->key));

            if ($method->paymentMethod !== null && $method->paymentMethod instanceof CreditCardData) {
                $data = $xml->createElement('PaymentMethodKeyData');

                if ($method->paymentMethod->expMonth !== null) {
                    $data->appendChild($xml->createElement('ExpMonth', $method->paymentMethod->expMonth));
                }

                if ($method->paymentMethod->expYear !== null) {
                    $data->appendChild($xml->createElement('ExpYear', $method->paymentMethod->expYear));
                }

                if ($method->paymentMethod->cvn !== null) {
                    $data->appendChild($xml->createElement('CVV2', $method->paymentMethod->cvn));
                }

                $block1->appendChild($data);
            }

            if ($method->paymentType === "ACH" && !empty($method->secCode)) {
                $block1->appendChild($xml->createElement('SECCode', $method->secCode));
            }

            $data = $xml->createElement('RecurringData');
            if ($builder->scheduleId !== null) {
                $data->appendChild($xml->createElement('ScheduleID', $builder->scheduleId));
            }
            $data->appendChild($xml->createElement('OneTime', $builder->oneTimePayment ? 'Y' : 'N'));
            $block1->appendChild($data);
        }

        if (
            $builder->paymentMethod instanceof IPinProtected
            && $builder->transactionType !== TransactionType::REVERSAL
        ) {
            $block1->appendChild($xml->createElement('PinBlock', $builder->paymentMethod->pinBlock));
        }

        if (
            $builder->paymentMethod instanceof IEncryptable
            && isset($builder->paymentMethod->encryptionData)
            && null !== $builder->paymentMethod->encryptionData
        ) {
            $enc = $this->hydrateEncryptionData($xml, $builder);

            if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                $block1->appendChild($enc);
            } else {
                $cardData->appendChild($enc);
            }
        }

        if ($builder->paymentMethod instanceof ITokenizable) {
            if ($builder->requestMultiUseToken) {
                $cardData->appendChild(
                    $xml->createElement('TokenRequest', 'Y')
                );

                if ($builder->requestUniqueToken) {
                    $tokenMappingBlock = $xml->createElement('Mapping', 'UNIQUE');
                    $tokenParametersBlock = $xml->createElement('TokenParameters');
                    $tokenParametersBlock->appendChild($tokenMappingBlock);
                    $cardData->appendChild($tokenParametersBlock);
                }
            }
        }

        if ($cardData->childNodes->length > 0 && $builder->aliasAction !== AliasAction::CREATE) {
            $block1->appendChild($cardData);
        }

        //secure 3d
        if ($builder->paymentMethod instanceof CreditCardData) {
            if (!empty($builder->paymentMethod->threeDSecure)) {
                $this->hydrateThreeDSecureData($xml, $builder, $block1);
            } else {
                $this->hydrateWalletData($xml, $builder, $block1, $cardData);
            }
        }

        if ($builder->paymentMethod instanceof IBalanceable && $builder->balanceInquiryType !== null) {
            $block1->appendChild($xml->createElement('BalanceInquiryType', $builder->balanceInquiryType));
        }

        if ($builder->level2Request === true || $builder->commercialData !== null) {
            $block1->appendChild($xml->createElement('CPCReq', 'Y'));
        }

        if (
            $builder->customerId !== null
            || $builder->description !== null
            || $builder->invoiceNumber !== null
        ) {
            $block1->appendChild($this->hydrateAdditionalTxnFields($xml, $builder));
        }

        if ($builder->ecommerceInfo !== null) {
            $block1->appendChild($xml->createElement('Ecommerce', $builder->ecommerceInfo->channel));

            if (!empty($builder->invoiceNumber) || !empty($builder->ecommerceInfo->shipMonth)) {
                $direct = $xml->createElement('DirectMktData');
                if (!empty($builder->invoiceNumber)) {
                    $direct->appendChild($xml->createElement('DirectMktInvoiceNbr', $builder->invoiceNumber));
                }

                if (!empty($builder->ecommerceInfo->shipMonth)) {
                    $direct->appendChild($xml->createElement('DirectMktShipMonth', $builder->ecommerceInfo->shipMonth));
                }

                if (!empty($builder->ecommerceInfo->shipDay)) {
                    $direct->appendChild($xml->createElement('DirectMktShipDay', $builder->ecommerceInfo->shipDay));
                }
                $block1->appendChild($direct);
            }
        }

        if ($builder->dynamicDescriptor !== null) {
            $block1->appendChild(
                $xml->createElement('TxnDescriptor', $builder->dynamicDescriptor)
            );
        }

        if ($builder->commercialData !== null) {
            $commercialDataNode = $xml->createElement('CPCData');

            $commercialDataNode->appendChild($xml->createElement('CardHolderPONbr', $builder->commercialData->poNumber));
            $commercialDataNode->appendChild($xml->createElement('TaxType', $builder->commercialData->taxType));
            $commercialDataNode->appendChild($xml->createElement('TaxAmt', $builder->commercialData->taxAmount));

            $block1->appendChild($commercialDataNode);
        }

        // auto substantiation
        if ($builder->autoSubstantiation !== null) {
            $autoSubstantiationNode = $xml->createElement('AutoSubstantiation');

            $fieldNames = ["First", "Second", "Third", "Fourth"];
            $i = 0;
            $hasAdditionalAmount = false;

            foreach ($builder->autoSubstantiation->amounts as $amtType => $amount) {
                if ($amount !== 0) {
                    $hasAdditionalAmount = true;
                    if ($i > 3) { // Portico Gateway limits to 3 subtotals
                        throw new BuilderException("You may only specify three different subtotals in a single transaction.");
                    }
                    $additionalAmountNode = $xml->createElement($fieldNames[$i] . "AdditionalAmtInfo");
                    $additionalAmountNode->appendChild($xml->createElement("AmtType", $amtType));
                    $additionalAmountNode->appendChild($xml->createElement("Amt", $amount));
                    $autoSubstantiationNode->appendChild($additionalAmountNode);
                    $i++;
                }
            }

            $autoSubstantiationNode->appendChild($xml->createElement("MerchantVerificationValue", $builder->autoSubstantiation->merchantVerificationValue ?? ''));
            $autoSubstantiationNode->appendChild($xml->createElement("RealTimeSubstantiation", $builder->autoSubstantiation->realTimeSubstantiation ? "Y" : "N"));

            if ($hasAdditionalAmount) { // Portico Gateway requires at least one healthcare amount subtotal
                $block1->appendChild($autoSubstantiationNode);
            } else {
                throw new BuilderException("You must provide at least one healthcare amount w/autoSubstantiation requests");
            }
        }

        $transaction->appendChild($block1);

        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction, $builder->clientTransactionId));
        return $this->mapResponse($response, $builder, $this->buildEnvelope($xml, $transaction));
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        throw new UnsupportedTransactionException('Portico does not support hosted payments.');
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
        $xml = new DOMDocument('1.0', 'utf-8');

        // build request
        $transaction = $xml->createElement($this->mapRequestType($builder));

        if ($builder->transactionType !== TransactionType::BATCH_CLOSE) {
            $root = null;
            if (
                $builder->transactionType === TransactionType::REVERSAL
                || $builder->transactionType === TransactionType::REFUND
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH
                || $builder->transactionType === TransactionTYpe::AUTH
            ) {
                $root = $xml->createElement('Block1');
            } else {
                $root = $transaction;
            }

            // Transaction ID
            if ($builder->paymentMethod !== null && !empty($builder->paymentMethod->transactionId)) {
                $root->appendChild(
                    $xml->createElement('GatewayTxnId', $builder->paymentMethod->transactionId)
                );
            }

            // reversal & Capture
            if (
                $builder->transactionType === TransactionType::REVERSAL ||
                (
                    $builder->paymentMethod !== null &&
                    $builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH
                )
            ) {
                // Client Transaction ID
                if (!empty($builder->paymentMethod->clientTransactionId)) {
                    $root->appendChild(
                        $xml->createElement(
                            'ClientTxnId',
                            $builder->paymentMethod->clientTransactionId
                        )
                    );
                }
            }

            if (
                $builder->transactionType === TransactionType::REVERSAL
                || $builder->transactionType === TransactionType::CAPTURE
                || ($builder->paymentMethod !== null && $builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH)
            ) {
                // tag data
                if (!empty($builder->tagData)) {
                    $tagDataElement = $xml->createElement('TagData');
                    $root->appendChild($tagDataElement);
                    $tagValuesElement = $xml->createElementNS('TagValues', 'source', 'chip');
                    $tagDataElement->appendChild($tagValuesElement);
                }
            }

            if ($builder->allowDuplicates !== null && !empty($builder->allowDuplicates)) {
                $root->appendChild(
                    $xml->createElement(
                        'AllowDup',
                        ($builder->allowDuplicates ? 'Y' : 'N')
                    )
                );
            }

            // Level II Data
            if (
                $builder->transactionType === TransactionType::EDIT
                && $builder->transactionModifier === TransactionModifier::LEVEL_II
            ) {
                $cpc = $xml->createElement('CPCData');

                if ($builder->poNumber !== null) {
                    $cpc->appendChild(
                        $xml->createElement('CardHolderPONbr', $builder->poNumber)
                    );
                }

                if ($builder->taxType !== null) {
                    $cpc->appendChild(
                        $xml->createElement(
                            'TaxType',
                            TaxType::validate($builder->taxType)
                        )
                    );
                }

                if ($builder->taxAmount !== null) {
                    $cpc->appendChild($xml->createElement('TaxAmt', $builder->taxAmount));
                }

                $root->appendChild($cpc);
            } elseif (
                $builder->transactionType === TransactionType::EDIT
                && $builder->transactionModifier === TransactionModifier::LEVEL_III
            ) {
                $cpc = $xml->createElement('CPCData');

                if ($builder->commercialData->poNumber !== null) {
                    $cpc->appendChild(
                        $xml->createElement('CardHolderPONbr', $builder->commercialData->poNumber)
                    );
                }

                if ($builder->commercialData->taxType !== null) {
                    $cpc->appendChild(
                        $xml->createElement(
                            'TaxType',
                            TaxType::validate($builder->commercialData->taxType)
                        )
                    );
                }

                if ($builder->commercialData->taxAmount !== null) {
                    $cpc->appendChild($xml->createElement('TaxAmt', $builder->commercialData->taxAmount));
                }

                $root->appendChild($cpc);

                $commercialDataNode = $xml->createElement('CorporateData');

                if ($builder->cardType == 'Visa') {
                    $visaCorporateDataNode = $xml->createElement('Visa');

                    if (!empty($builder->commercialData->summaryCommodityCode)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('SummaryCommodityCode', $builder->commercialData->summaryCommodityCode));
                    }

                    if (!empty($builder->commercialData->discountAmount)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('DiscountAmt', $builder->commercialData->discountAmount));
                    }

                    if (!empty($builder->commercialData->freightAmount)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('FreightAmt', $builder->commercialData->freightAmount));
                    }

                    if (!empty($builder->commercialData->dutyAmount)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('DutyAmt', $builder->commercialData->dutyAmount));
                    }

                    if (!empty($builder->commercialData->destinationPostalCode)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('DestinationPostalZipCode', $builder->commercialData->destinationPostalCode));
                    }

                    if (!empty($builder->commercialData->originPostalCode)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('ShipFromPostalZipCode', $builder->commercialData->originPostalCode));
                    }

                    if (!empty($builder->commercialData->destinationCountryCode)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('DestinationCountryCode', $builder->commercialData->destinationCountryCode));
                    }

                    if (!empty($builder->commercialData->vatInvoiceNumber)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('InvoiceRefNbr', $builder->commercialData->vatInvoiceNumber));
                    }

                    if (!empty($builder->commercialData->orderDate)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('OrderDate', $builder->commercialData->orderDate));
                    }

                    if (!empty($builder->commercialData->additionalTaxDetails->taxAmount)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('VATTaxAmtFreight', $builder->commercialData->additionalTaxDetails->taxAmount));
                    }

                    if (!empty($builder->commercialData->additionalTaxDetails->taxRate)) {
                        $visaCorporateDataNode->appendChild($xml->createElement('VATTaxRateFreight', $builder->commercialData->additionalTaxDetails->taxRate));
                    }

                    // if (!empty($builder->commercialData->somethingsome)) {
                    //     $visaCorporateDataNode->appendChild($xml->createElement('LineItemDiscountTreatmentCode', $builder->commercialData->somethingsome));
                    // }

                    // if (!empty($builder->commercialData->somethingsome)) {
                    //     $visaCorporateDataNode->appendChild($xml->createElement('TaxTreatment', $builder->commercialData->somethingsome));
                    // }

                    if (count($builder->commercialData->lineItems) > 0) {
                        $lineItemsNode = $xml->createElement('LineItems');

                        foreach ($builder->commercialData->lineItems as $lineItem) {
                            $linetItemNode = $xml->createElement('LineItemDetail');

                            // if (!empty($lineItem->commodityCode)) {
                            //     $linetItemNode->appendChild($xml->createElement('ItemCommodityCode', $lineItem->commodityCode));
                            // }

                            if (!empty($lineItem->description)) {
                                $linetItemNode->appendChild($xml->createElement('ItemDescription', htmlentities($lineItem->description)));
                            }

                            if (!empty($lineItem->productCode)) {
                                $linetItemNode->appendChild($xml->createElement('ProductCode', $lineItem->productCode));
                            }

                            if (!empty($lineItem->quantity)) {
                                $linetItemNode->appendChild($xml->createElement('Quantity', $lineItem->quantity));
                            }

                            if (!empty($lineItem->unitOfMeasure)) {
                                $linetItemNode->appendChild($xml->createElement('UnitOfMeasure', $lineItem->unitOfMeasure));
                            }

                            if (!empty($lineItem->unitCost)) {
                                $linetItemNode->appendChild($xml->createElement('UnitCost', $lineItem->unitCost));
                            }

                            if (!empty($lineItem->taxAmount)) {
                                $linetItemNode->appendChild($xml->createElement('VATTaxAmt', $lineItem->taxAmount));
                            }

                            if (!empty($lineItem->taxPercentage)) {
                                $linetItemNode->appendChild($xml->createElement('VATTaxRate', $lineItem->taxPercentage));
                            }

                            if (!empty($lineItem->discountDetails->discountPercentage)) {
                                $linetItemNode->appendChild($xml->createElement('DiscountAmt', $lineItem->discountDetails->discountPercentage));
                            }

                            if (!empty($lineItem->totalAmount)) {
                                $linetItemNode->appendChild($xml->createElement('LineItemTotalAmt', $lineItem->totalAmount));
                            }

                            // if (!empty($lineItem->somethingsome)) {
                            //     $visaCorporateDataNode->appendChild($xml->createElement('LineItemTreatmentCode', $builder->commercialData->somethingsome));
                            // }

                            $lineItemsNode->appendChild($linetItemNode);
                        };
                    }

                    if (!empty($lineItemsNode)) {
                        $visaCorporateDataNode->appendChild($lineItemsNode);
                    }

                    $commercialDataNode->appendChild($visaCorporateDataNode);
                    $root->appendChild($commercialDataNode);
                } elseif ($builder->cardType == 'MC') {
                    $mastercardCorporateDataNode = $xml->createElement('MC');

                    if (count($builder->commercialData->lineItems) > 0) {
                        $lineItemsNode = $xml->createElement('LineItems');

                        foreach ($builder->commercialData->lineItems as $lineItem) {
                            $linetItemNode = $xml->createElement('LineItemDetail');

                            if (!empty($lineItem->description)) {
                                $linetItemNode->appendChild($xml->createElement('ItemDescription', htmlentities($lineItem->description)));
                            }

                            if (!empty($lineItem->productCode)) {
                                $linetItemNode->appendChild($xml->createElement('ProductCode', $lineItem->productCode));
                            }

                            if (!empty($lineItem->quantity)) {
                                $linetItemNode->appendChild($xml->createElement('Quantity', $lineItem->quantity));
                            }

                            if (!empty($lineItem->unitCost)) {
                                $linetItemNode->appendChild($xml->createElement('ItemTotalAmt', $lineItem->unitCost));
                            }

                            if (!empty($lineItem->unitOfMeasure)) {
                                $linetItemNode->appendChild($xml->createElement('UnitOfMeasure', $lineItem->unitOfMeasure));
                            }

                            $lineItemsNode->appendChild($linetItemNode);
                        };
                    }

                    $mastercardCorporateDataNode->appendChild($lineItemsNode);
                    $commercialDataNode->appendChild($mastercardCorporateDataNode);
                    $root->appendChild($commercialDataNode);
                }
            } else {
                // amount
                if ($builder->amount !== null) {
                    $root->appendChild($xml->createElement('Amt', $builder->amount));
                }

                // auth amount
                if ($builder->authAmount !== null) {
                    $root->appendChild($xml->createElement('AuthAmt', $builder->authAmount));
                }

                // gratuity
                if ($builder->gratuity !== null) {
                    $root->appendChild(
                        $xml->createElement('GratuityAmtInfo', $builder->gratuity)
                    );
                }
            }

            // Token Management
            if (
                $builder->transactionType === TransactionType::TOKEN_UPDATE
                || $builder->transactionType === TransactionType::TOKEN_DELETE
            ) {
                $token = $builder->paymentMethod;

                // Set the token value
                $root->appendChild($xml->createElement('TokenValue', $token->token));

                $tokenActions = $root->appendChild($xml->createElement('TokenActions'));
                if ($builder->transactionType === TransactionType::TOKEN_UPDATE) {
                    $setElement = $tokenActions->appendChild($xml->createElement('Set'));

                    $expMonth = $setElement->appendChild($xml->createElement('Attribute'));
                    $expMonth->appendChild($xml->createElement('Name', 'ExpMonth'));
                    $expMonth->appendChild($xml->createElement('Value', $token->expMonth));

                    $expYear = $setElement->appendChild($xml->createElement('Attribute'));
                    $expYear->appendChild($xml->createElement('Name', 'ExpYear'));
                    $expYear->appendChild($xml->createElement('Value', $token->expYear));
                } else {
                    $tokenActions->appendChild($xml->createElement('Delete'));
                }
            }

            // Additional Transaction Fields
            if ($builder->transactionType !== TransactionType::VOID) {
                if (!empty($builder->customerId) || !empty($builder->description) || !empty($builder->invoiceNumber)) {
                    $addons = $xml->createElement('AdditionalTxnFields');

                    if (!empty($builder->customerId))
                    $addons->appendChild($xml->createElement('CustomerID', $builder->customerId));

                    if (!empty($builder->description))
                    $addons->appendChild($xml->createElement('Description', htmlentities($builder->description)));

                    if (!empty($builder->invoiceNumber))
                    $addons->appendChild($xml->createElement('InvoiceNbr', $builder->invoiceNumber));

                    $root->appendChild($addons);
                }
            }

            if (
                $builder->transactionType === TransactionType::REVERSAL
                || $builder->transactionType === TransactionType::REFUND
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH
                || $builder->transactionType === TransactionTYpe::AUTH
            ) {
                $transaction->appendChild($root);
            }
        }

        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapResponse($response, $builder, $this->buildEnvelope($xml, $transaction));
    }
    public function processReport(ReportBuilder $builder)
    {
        $xml = new DOMDocument('1.0', 'utf-8');

        $reportType = $builder->reportType;
        $transaction = $xml->createElement($this->mapReportType($builder));

        if ($builder instanceof TransactionReportBuilder) {
            /** @var TransactionReportBuilder */
            $trb = $builder;
            
            if (
                $reportType === ReportType::FIND_TRANSACTIONS
                || $reportType === ReportType::TRANSACTION_DETAIL
            ) {
                if ($trb->transactionId !== null) {
                    $transaction->appendChild(
                        $xml->createElement('TxnId', $builder->transactionId)
                    );
                }
            }
            if ($reportType === ReportType::FIND_TRANSACTIONS) {
                $criteria = $transaction->appendChild($xml->createElement('Criteria'));

                if ($builder->searchBuilder->startDate !== null) {
                    $criteria->appendChild($xml->createElement(
                        'StartUtcDT',
                        $builder->searchBuilder->startDate
                    ));
                }
                if ($builder->searchBuilder->endDate !== null) {
                    $criteria->appendChild($xml->createElement(
                        'EndUtcDT',
                        $builder->searchBuilder->endDate
                    ));
                }
                if ($builder->searchBuilder->authCode !== null) {
                    $criteria->appendChild($xml->createElement(
                        'AuthCode',
                        $builder->searchBuilder->authCode
                    ));
                }
                if ($builder->searchBuilder->cardHolderLastName !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CardHolderLastName',
                        htmlentities($builder->searchBuilder->cardHolderLastName)
                    ));
                }
                if ($builder->searchBuilder->cardHolderFirstName !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CardHolderFirstName',
                        htmlentities($builder->searchBuilder->cardHolderFirstName)
                    ));
                }
                if ($builder->searchBuilder->cardNumberFirstSix !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CardNbrFirstSix',
                        $builder->searchBuilder->cardNumberFirstSix
                    ));
                }
                if ($builder->searchBuilder->cardNumberLastFour !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CardNbrLastFour',
                        $builder->searchBuilder->cardNumberLastFour
                    ));
                }
                if ($builder->searchBuilder->invoiceNumber !== null) {
                    $criteria->appendChild($xml->createElement(
                        'InvoiceNbr',
                        $builder->searchBuilder->invoiceNumber
                    ));
                }
                if ($builder->searchBuilder->cardHolderPoNumber !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CardHolderPONbr',
                        $builder->searchBuilder->cardHolderPoNumber
                    ));
                }
                if ($builder->searchBuilder->customerId !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CustomerID',
                        $builder->searchBuilder->customerId
                    ));
                }
                if ($builder->searchBuilder->issuerResult !== null) {
                    $criteria->appendChild($xml->createElement(
                        'IssuerResult',
                        $builder->searchBuilder->issuerResult
                    ));
                }
                if ($builder->searchBuilder->settlementAmount !== null) {
                    $criteria->appendChild($xml->createElement(
                        'SettlementAmt',
                        $builder->searchBuilder->settlementAmount
                    ));
                }
                if ($builder->searchBuilder->issuerTransactionId !== null) {
                    $criteria->appendChild($xml->createElement(
                        'IssTxnId',
                        $builder->searchBuilder->issuerTransactionId
                    ));
                }
                if ($builder->searchBuilder->referenceNumber !== null) {
                    $criteria->appendChild($xml->createElement(
                        'RefNbr',
                        $builder->searchBuilder->referenceNumber
                    ));
                }
                if ($builder->searchBuilder->username !== null) {
                    $criteria->appendChild($xml->createElement(
                        'UserName',
                        htmlentities($builder->searchBuilder->username)
                    ));
                }
                if ($builder->searchBuilder->clerkId !== null) {
                    $criteria->appendChild($xml->createElement(
                        'ClerkID',
                        $builder->searchBuilder->clerkId
                    ));
                }
                if ($builder->searchBuilder->batchSequenceNumber !== null) {
                    $criteria->appendChild($xml->createElement(
                        'BatchSeqNbr',
                        $builder->searchBuilder->batchSequenceNumber
                    ));
                }
                if ($builder->searchBuilder->batchId !== null) {
                    $criteria->appendChild($xml->createElement(
                        'BatchId',
                        $builder->searchBuilder->batchId
                    ));
                }
                if ($builder->searchBuilder->siteTrace !== null) {
                    $criteria->appendChild($xml->createElement(
                        'SiteTrace',
                        $builder->searchBuilder->siteTrace
                    ));
                }
                if ($builder->searchBuilder->displayName !== null) {
                    $criteria->appendChild($xml->createElement(
                        'DisplayName',
                        htmlentities($builder->searchBuilder->displayName)
                    ));
                }
                if ($builder->searchBuilder->clientTransactionId !== null) {
                    $criteria->appendChild($xml->createElement(
                        'ClientTxnId',
                        $builder->searchBuilder->clientTransactionId
                    ));
                }
                if ($builder->searchBuilder->uniqueDeviceId !== null) {
                    $criteria->appendChild($xml->createElement(
                        'UniqueDeviceId',
                        $builder->searchBuilder->uniqueDeviceId
                    ));
                }
                if ($builder->searchBuilder->accountNumberLastFour !== null) {
                    $criteria->appendChild($xml->createElement(
                        'AcctNbrLastFour',
                        $builder->searchBuilder->accountNumberLastFour
                    ));
                }
                if ($builder->searchBuilder->bankRoutingNumber !== null) {
                    $criteria->appendChild($xml->createElement(
                        'BankRountingNbr',
                        $builder->searchBuilder->bankRoutingNumber
                    ));
                }
                if ($builder->searchBuilder->checkNumber !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CheckNbr',
                        $builder->searchBuilder->checkNumber
                    ));
                }
                if ($builder->searchBuilder->checkFirstName !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CheckFirstName',
                        htmlentities($builder->searchBuilder->checkFirstName)
                    ));
                }
                if ($builder->searchBuilder->checkLastName !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CheckLastName',
                        htmlentities($builder->searchBuilder->checkLastName)
                    ));
                }
                if ($builder->searchBuilder->checkName !== null) {
                    $criteria->appendChild($xml->createElement(
                        'CheckName',
                        htmlentities($builder->searchBuilder->checkName)
                    ));
                }
                if ($builder->searchBuilder->giftCurrency !== null) {
                    $criteria->appendChild($xml->createElement(
                        'GiftCurrency',
                        $builder->searchBuilder->giftCurrency
                    ));
                }
                if ($builder->searchBuilder->giftMaskedAlias !== null) {
                    $criteria->appendChild($xml->createElement(
                        'GiftMaskedAlias',
                        $builder->searchBuilder->giftMaskedAlias
                    ));
                }
                if ($builder->searchBuilder->oneTime !== null) {
                    $criteria->appendChild($xml->createElement(
                        'OneTime',
                        $builder->searchBuilder->oneTime
                    ));
                }
                if ($builder->searchBuilder->paymentMethodKey !== null) {
                    $criteria->appendChild($xml->createElement(
                        'PaymentMethodKey',
                        $builder->searchBuilder->paymentMethodKey
                    ));
                }
                if ($builder->searchBuilder->scheduleId !== null) {
                    $criteria->appendChild($xml->createElement(
                        'ScheduleID',
                        $builder->searchBuilder->scheduleId
                    ));
                }
                if ($builder->searchBuilder->buyerEmailAddress !== null) {
                    $criteria->appendChild($xml->createElement(
                        'BuyerEmailAddress',
                        $builder->searchBuilder->buyerEmailAddress
                    ));
                }
                if ($builder->searchBuilder->altPaymentStatus !== null) {
                    $criteria->appendChild($xml->createElement(
                        'AltPaymentStatus',
                        $builder->searchBuilder->altPaymentStatus
                    ));
                }
                if ($builder->searchBuilder->fullyCaptured !== null) {
                    $criteria->appendChild($xml->createElement(
                        'FullyCapturedInd',
                        $builder->searchBuilder->fullyCaptured
                    ));
                }
            }

            if ($reportType === ReportType::ACTIVITY) {
                if ($trb->startDate !== null) {
                    $transaction->appendChild(
                        $xml->createElement('RptStartUtcDT', $builder->startDate->format('Y-m-d\TH:i:s.v'))
                    );
                }

                if ($trb->endDate !== null) {
                    $transaction->appendChild(
                        $xml->createElement('RptEndUtcDT', $builder->endDate->format('Y-m-d\TH:i:s.v'))
                    );
                }
            }

            if (
                $reportType === ReportType::ACTIVITY ||
                $reportType === ReportType::OPEN_AUTH ||
                $reportType === ReportType::BATCH_DETAIL
            ) {
                if ($trb->deviceId !== null) {
                    $transaction->appendChild(
                        $xml->createElement('DeviceId', $trb->deviceId)
                    );
                }
                if ($trb->timeZoneConversion !== null) {
                    $transaction->appendChild(
                        $xml->createElement('TzConversion', $trb->timeZoneConversion ?? '')
                    );
                }
            }

            if ($reportType === ReportType::BATCH_DETAIL) {
                if ($trb->batchId !== 0 && $trb->batchId !== null) {
                    $transaction->appendChild(
                        $xml->createElement('BatchId', $trb->batchId)
                    );
                }
            }
        }
        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapReportResponse($response, $builder);
    }

    /**
     * Wraps a transaction with a SOAP envelope
     *
     * @param DOMDocument $xml The current DOMDocument object
     * @param DOMElement $transaction The current transaction to wrap
     *
     * @return DOMElement
     */
    protected function buildEnvelope(DOMDocument $xml, DOMElement $transaction, $clientTransactionId = null)
    {
        $soapEnvelope = $xml->createElement('soapenv:Envelope');
        $soapEnvelope->setAttribute(
            'xmlns:soapenv',
            'http://schemas.xmlsoap.org/soap/envelope/'
        );
        $soapEnvelope->setAttribute('xmlns', static::XML_NAMESPACE);

        $soapBody = $xml->createElement('soapenv:Body');
        $request = $xml->createElement('PosRequest');

        $version = $xml->createElement('Ver1.0');
        $header = $xml->createElement('Header');

        if (!empty($this->secretApiKey)) {
            $header->appendChild(
                $xml->createElement('SecretAPIKey', trim($this->secretApiKey))
            );
        }
        if (!empty($this->siteId)) {
            $header->appendChild(
                $xml->createElement('SiteId', $this->siteId)
            );
        }
        if (!empty($this->deviceId)) {
            $header->appendChild(
                $xml->createElement('DeviceId', $this->deviceId)
            );
        }
        if (!empty($this->licenseId)) {
            $header->appendChild(
                $xml->createElement('LicenseId', $this->licenseId)
            );
        }
        if (!empty($this->username)) {
            $header->appendChild(
                $xml->createElement('UserName', $this->username)
            );
        }
        if (!empty($this->password)) {
            $header->appendChild(
                $xml->createElement('Password', $this->password)
            );
        }
        if (!empty($this->developerId)) {
            $header->appendChild(
                $xml->createElement('DeveloperID', $this->developerId)
            );
        }
        if (!empty($this->versionNumber)) {
            $header->appendChild(
                $xml->createElement('VersionNbr', $this->versionNumber)
            );
        }
        if (!empty($this->sdkNameVersion)) {
            $header->appendChild(
                $xml->createElement('SDKNameVersion', $this->sdkNameVersion)
            );
        } else {
            $header->appendChild(
                $xml->createElement('SDKNameVersion', 'php;version=' . $this->getReleaseVersion())
            );         
        }

        $version->appendChild($header);
        $transactionElement = $xml->createElement('Transaction');
        $transactionElement->appendChild($xml->importNode($transaction, true));
        $version->appendChild($transactionElement);

        if (!empty($clientTransactionId)) {
            $header->appendChild($xml->createElement('ClientTxnId', $clientTransactionId));
        }

        $request->appendChild($version);
        $soapBody->appendChild($request);
        $soapEnvelope->appendChild($soapBody);
        $xml->appendChild($soapEnvelope);

        return $xml->saveXML();
    }

    /**
     * Deserializes the gateway's XML response
     *
     * @param string $rawResponse The XML response
     * @param BaseBuilder $builder The original transaction builder
     *
     * @throws Exception
     * @return Transaction
     */
    protected function mapResponse($rawResponse, BaseBuilder $builder, $request)
    {
        $result = new Transaction();

        // TODO: handle non-200 responses

        $root = $this->xml2object($rawResponse)->{'Ver1.0'};
        $acceptedCodes = ['00', '0', '85', '10'];

        $gatewayRspCode = $this->normalizeResponse((string)$root->Header->GatewayRspCode);
        $gatewayRspText = (string)$root->Header->GatewayRspMsg;

        if (!in_array($gatewayRspCode, $acceptedCodes)) {

            if (!empty($root->Header->GatewayTxnId))
                $gatewayRspText .= '. GatewayTxnId: ' . $root->Header->GatewayTxnId;

            throw new GatewayException(
                sprintf(
                    'Unexpected Gateway Response: %s - %s. ',
                    $gatewayRspCode,
                    $gatewayRspText
                ),
                $gatewayRspCode,
                $gatewayRspText
            );
        }

        $item = $root->Transaction->{$this->mapRequestType($builder)};

        $result->responseCode = isset($item) && isset($item->RspCode)
            ? $this->normalizeResponse((string)$item->RspCode)
            : $gatewayRspCode;
        $result->responseMessage = isset($item) && isset($item->RspText)
            ? (string)$item->RspText
            : $gatewayRspText;

        if (isset($item) && (isset($item->AuthAmt) || isset($item->SplitTenderCardAmt))) {
            $result->authorizedAmount =
                isset($item->SplitTenderCardAmt)
                ? (string)$item->SplitTenderCardAmt
                : (string)$item->AuthAmt;
        }

        if (isset($item) && isset($item->SplitTenderBalanceDueAmt)) {
            $result->splitTenderBalanceDueAmt = (string)$item->SplitTenderBalanceDueAmt;
        }

        if (isset($item) && isset($item->AvailableBalance)) {
            $result->availableBalance = (string)$item->AvailableBalance;
        }

        if (isset($item) && isset($item->AVSRsltCode)) {
            $result->avsResponseCode = (string)$item->AVSRsltCode;
        }

        if (isset($item) && isset($item->AVSRsltText)) {
            $result->avsResponseMessage = (string)$item->AVSRsltText;
        }

        if (isset($item) && isset($item->BalanceAmt)) {
            $result->balanceAmount = (string)$item->BalanceAmt;
        }

        if (isset($item) && isset($item->CardType)) {
            $result->cardType = (string)$item->CardType;
        }

        if (isset($item) && isset($item->CardLast4)) {
            $result->cardLast4 = (string)$item->TokenPANLast4;
        }

        if (isset($item) && isset($item->CAVVResultCode)) {
            $result->cavvResponseCode = (string)$item->CAVVResultCode;
        }

        if (isset($item) && isset($item->CPCInd)) {
            $result->commercialIndicator = (string)$item->CPCInd;
        }

        if (isset($item) && isset($item->CVVRsltCode)) {
            $result->cvnResponseCode = (string)$item->CVVRsltCode;
        }

        if (isset($item) && isset($item->CVVRsltText)) {
            $result->cvnResponseMessage = (string)$item->CVVRsltText;
        }

        if (isset($item) && isset($item->EMVIssuerResp)) {
            $result->emvIssuerResponse = (string)$item->EMVIssuerResp;
        }

        if (isset($item) && isset($item->PointsBalanceAmt)) {
            $result->pointsBalanceAmount = (string)$item->PointsBalanceAmt;
        }

        if (isset($item) && isset($item->RecurringDataCode)) {
            $result->recurringDataCode = (string)$item->RecurringDataCode;
        }

        if (isset($item) && isset($item->RefNbr)) {
            $result->referenceNumber = (string)$item->RefNbr;
        }

        if (isset($item) && isset($item->TxnDescriptor)) {
            $result->transactionDescriptor = (string)$item->TxnDescriptor;
        }

        if ($builder->paymentMethod !== null) {
            $result->transactionReference = new TransactionReference();
            $result->transactionReference->transactionId = (string)$root->Header->GatewayTxnId;
            $result->transactionReference->paymentMethodType = $builder->paymentMethod->paymentMethodType;

            if (isset($item) && isset($item->AuthCode)) {
                $result->transactionReference->authCode = (string)$item->AuthCode;
            }
        }

        if (isset($item) && isset($item->CardData)) {
            $result->giftCard = new GiftCard();
            $result->giftCard->number = (string)$item->CardData->CardNbr;
            $result->giftCard->alias = (string)$item->CardData->Alias;
            $result->giftCard->pin = (string)$item->CardData->PIN;
        }

        if (isset($root->Header->TokenData) && isset($root->Header->TokenData->TokenValue)) {
            $result->token = (string)$root->Header->TokenData->TokenValue;
        }

        if (isset($item) && isset($item->BatchId)) {
            $result->batchSummary = new BatchSummary();
            $result->batchSummary->id = (string)$item->BatchId;
            $result->batchSummary->transactionCount = (string)$item->TxnCnt;
            $result->batchSummary->totalAmount = (string)$item->TotalAmt;
            $result->batchSummary->sequenceNumber = (string)$item->BatchSeqNbr;

            if (isset($item->HostBatchNbr)) {
                $result->batchSummary->hostBatchNbr = (string)$item->HostBatchNbr;
            }
            if (isset($item->HostTotalCnt)) {
                $result->batchSummary->hostTotalCnt = (string)$item->HostTotalCnt;
            }
            if (isset($item->HostTotalAmt)) {
                $result->batchSummary->hostTotalAmt = (string)$item->HostTotalAmt;
            }
            if (isset($item->ProcessedDeviceId)) {
                $result->batchSummary->processedDeviceId = (string)$item->ProcessedDeviceId;
            }
        }

        if (isset($item) && isset($item->CardBrandTxnId)) {
            $result->cardBrandTransactionId = (string)$item->CardBrandTxnId;
        }

        if (!empty($root->PaymentFacilitatorTxnId) || !empty($root->PaymentFacilitatorTxnNbr)) {
            $result->payFacData = new PayFacResponseData();
            $result->payFacData->transactionId = !empty($root->PaymentFacilitatorTxnId) ? (string) $root->PaymentFacilitatorTxnId : '';
            $result->payFacData->transactionNumber = !empty($root->PaymentFacilitatorTxnNbr) ? (string) $root->PaymentFacilitatorTxnNbr : '';
        }

        return $result;
    }

    protected function mapReportResponse($rawResponse, ReportBuilder $builder)
    {
        $root = $this->xml2object($rawResponse)->{'Ver1.0'};
        $doc = $root->Transaction->{$this->mapReportType($builder)};

        if ((($builder->reportType === ReportType::ACTIVITY)
                || ($builder->reportType === ReportType::FIND_TRANSACTIONS))
            && isset($doc->Transactions)
        ) {
            $response = [];
            foreach ($doc->Transactions as $item) {
                $response[] = $this->hydrateTransactionSummary($item);
            }
            return $response;
        }

        if ($builder->reportType === ReportType::TRANSACTION_DETAIL) {
            if (isset($doc->Data))
                return $this->hydrateTransactionSummary($doc->Data);
        }

        if ((
                $builder->reportType === ReportType::BATCH_DETAIL
                || $builder->reportType === ReportType::OPEN_AUTH
            )
            && isset($doc->Details)
        ) {
            $response = [];
            foreach ($doc->Details as $item) {
                $response[] = $this->hydrateTransactionSummary($item);
            }

            return $response;
        }

        return null;
    }

    protected function hydrateTransactionSummary($item)
    {
        $summary = new TransactionSummary();

        if (isset($item) && isset($item->AcctDataSrc)) {
            $summary->accountDataSource = (string)$item->AcctDataSrc;
        }

        if (isset($item) && isset($item->Amt)) {
            $summary->amount = (string)$item->Amt;
        }

        if (isset($item) && isset($item->AuthAmt)) {
            $summary->authorizedAmount = (string)$item->AuthAmt;
        }

        if (isset($item) && isset($item->AuthCode)) {
            $summary->authCode = (string)$item->AuthCode;
        }

        if (isset($item) && isset($item->AVSRsltCode)) {
            $summary->avsResponseCode = (string)$item->AVSRsltCode;
        }

        if (isset($item) && isset($item->AVSRsltText)) {
            $summary->avsResponseMessage = (string)$item->AVSRsltText;
        }

        if (isset($item) && isset($item->BatchCloseDT)) {
            $summary->batchCloseDate = (string)$item->BatchCloseDT;
        }

        if (isset($item) && isset($item->BatchSeqNbr)) {
            $summary->batchSequenceNumber = (string)$item->BatchSeqNbr;
        }

        if (isset($item) && isset($item->CardHolderData)) {
            if (isset($item->CardHolderData->CardHolderFirstName)) {
                $summary->cardHolderFirstName = (string)$item->CardHolderData->CardHolderFirstName;
            }
            if (isset($item->CardHolderData->CardHolderLastName)) {
                $summary->cardHolderLastName = (string)$item->CardHolderData->CardHolderLastName;
            }
            if (isset($item->CardHolderData->CardHolderAddr)) {
                $summary->cardHolderAddr = (string)$item->CardHolderData->CardHolderAddr;
            }
            if (isset($item->CardHolderData->CardHolderCity)) {
                $summary->cardHolderCity = (string)$item->CardHolderData->CardHolderCity;
            }
            if (isset($item->CardHolderData->CardHolderState)) {
                $summary->cardHolderState = (string)$item->CardHolderData->CardHolderState;
            }
            if (isset($item->CardHolderData->CardHolderZip)) {
                $summary->cardHolderZip = (string)$item->CardHolderData->CardHolderZip;
            }
            if (isset($item->CardHolderData->EmailAddress)) {
                $summary->email = (string)$item->CardHolderData->EmailAddress;
            }
            if (isset($item->CardHolderData->CardHolderEmail)) {
                $summary->email = (string)$item->CardHolderData->CardHolderEmail;
            }
            if (isset($item->CardHolderData->CardHolderPhone)) {
                $summary->phone = (string)$item->CardHolderData->CardHolderPhone;
            }
        } else {
            if (isset($item->CardHolderFirstName)) {
                $summary->cardHolderFirstName = (string)$item->CardHolderFirstName;
            }
            if (isset($item->CardHolderLastName)) {
                $summary->cardHolderLastName = (string)$item->CardHolderLastName;
            }
            if (isset($item->CardHolderAddr)) {
                $summary->cardHolderAddr = (string)$item->CardHolderAddr;
            }
            if (isset($item->CardHolderCity)) {
                $summary->cardHolderCity = (string)$item->CardHolderCity;
            }
            if (isset($item->CardHolderState)) {
                $summary->cardHolderState = (string)$item->CardHolderState;
            }
            if (isset($item->CardHolderZip)) {
                $summary->cardHolderZip = (string)$item->CardHolderZip;
            }
            if (isset($item->EmailAddress)) {
                $summary->email = (string)$item->EmailAddress;
            }
            if (isset($item->CardHolderEmail)) {
                $summary->email = (string)$item->CardHolderEmail;
            }
            if (isset($item->CardHolderPhone)) {
                $summary->phone = (string)$item->CardHolderPhone;
            }
        }

        if (isset($item) && isset($item->CardSwiped)) {
            $summary->cardSwiped = (string)$item->CardSwiped;
        }

        if (isset($item) && isset($item->CardType)) {
            $summary->cardType = (string)$item->CardType;
        }

        if (isset($item) && isset($item->ClerkId)) {
            $summary->clerkId = (string)$item->ClerkId;
        }

        if (isset($item) && isset($item->ClientTxnId)) {
            $summary->clientTransactionId = (string)$item->ClientTxnId;
        }

        if (isset($item) && isset($item->ConvenienceAmtInfo)) {
            $summary->convenienceAmount = (string)$item->ConvenienceAmtInfo;
        }

        if (isset($item) && isset($item->CVVRsltCode)) {
            $summary->cvnResponseCode = (string)$item->CVVRsltCode;
        }

        if (isset($item) && isset($item->CVVRsltText)) {
            $summary->cvnResponseMessage = (string)$item->CVVRsltText;
        }

        if (isset($item) && isset($item->DeviceId)) {
            $summary->deviceId = (string)$item->DeviceId;
        }

        if (isset($item) && isset($item->GratuityAmtInfo)) {
            $summary->gratuityAmount = (string)$item->GratuityAmtInfo;
        }

        if (isset($item) && (isset($item->RspCode) || isset($item->IssuerRspCode))) {
            $summary->issuerResponseCode =
                isset($item->RspCode)
                ? (string)$item->RspCode
                : (string)$item->IssuerRspCode;
        }

        if (isset($item) && (isset($item->RspText) || isset($item->IssuerRspText))) {
            $summary->issuerResponseMessage =
                isset($item->RspText)
                ? (string)$item->RspText
                : (string)$item->IssuerRspText;
        }

        if (isset($item) && isset($item->IssTxnId)) {
            $summary->issuerTransactionId = (string)$item->IssTxnId;
        }

        if (isset($item) && isset($item->MaskedCardNbr)) {
            $summary->maskedCardNumber = (string)$item->MaskedCardNbr;
        }

        if (isset($item) && isset($item->OriginalGatewayTxnId)) {
            $summary->originalTransactionId = (string)$item->OriginalGatewayTxnId;
        }

        if (isset($item) && isset($item->GatewayRspCode)) {
            $summary->gatewayResponseCode = $this->normalizeResponse((string)$item->GatewayRspCode);
        }

        if (isset($item) && isset($item->GatewayResponseMsg)) {
            $summary->gatewayResponseMessage = (string)$item->GatewayResponseMsg;
        }

        if (isset($item) && isset($item->PaymentType)) {
            $summary->paymentType = (string)$item->PaymentType;
        }

        if (isset($item) && isset($item->CardHolderPONbr)) {
            $summary->poNumber = (string)$item->CardHolderPONbr;
        }

        if (isset($item) && isset($item->RefNbr)) {
            $summary->referenceNumber = (string)$item->RefNbr;
        }

        if (isset($item) && isset($item->AmountIndicator)) {
            $summary->amountIndicator = (string)$item->AmountIndicator;
        }

        if (isset($item) && isset($item->RspDT)) {
            $summary->responseDate = (string)$item->RspDT;
        }

        if (isset($item) && isset($item->ServiceName)) {
            $summary->serviceName = (string)$item->ServiceName;
        }

        if (isset($item) && isset($item->SettlementAmt)) {
            $summary->settlementAmount = (string)$item->SettlementAmt;
        }

        if (isset($item) && isset($item->ShippingAmtInfo)) {
            $summary->shippingAmount = (string)$item->ShippingAmtInfo;
        }

        if (isset($item) && isset($item->SiteTrace)) {
            $summary->siteTrace = (string)$item->SiteTrace;
        }

        if (isset($item) && (isset($item->TxnStatus) || isset($item->Status))) {
            $summary->status = isset($item->TxnStatus) ? (string)$item->TxnStatus : (string)$item->Status;
        }

        if (isset($item) && (isset($item->TaxAmtInfo) || isset($item->TaxAmt))) {
            $summary->taxAmount = isset($item->TaxAmtInfo) ? (string)$item->TaxAmtInfo : (string)$item->TaxAmt;
        }

        if (isset($item) && isset($item->TaxType)) {
            $summary->taxType = (string)$item->TaxType;
        }

        if (isset($item) && (isset($item->TxnUtcDT) || isset($item->ReqUtcDT))) {
            $summary->transactionDate = isset($item->TxnUtcDT) ? (string)$item->TxnUtcDT : (string)$item->ReqUtcDT;
        }

        if (isset($item) && isset($item->GatewayTxnId)) {
            $summary->transactionId = (string)$item->GatewayTxnId;
        }

        if (isset($item) && isset($item->TxnStatus)) {
            $summary->transactionStatus = (string)$item->TxnStatus;
        }

        if (isset($item) && isset($item->UserName)) {
            $summary->username = (string)$item->UserName;
        }

        if (isset($item) && isset($item->AdditionalTxnFields)) {
            if (isset($item->AdditionalTxnFields->Description)) {
                $summary->description = (string)$item->AdditionalTxnFields->Description;
            }

            if (isset($item->AdditionalTxnFields->InvoiceNbr)) {
                $summary->invoiceNumber = (string)$item->AdditionalTxnFields->InvoiceNbr;
            }

            if (isset($item->AdditionalTxnFields->CustomerID)) {
                $summary->customerId = (string)$item->AdditionalTxnFields->CustomerID;
            }
        }

        if (isset($item) && isset($item->UniqueDeviceId)) {
            $summary->uniqueDeviceId = (string)$item->UniqueDeviceId;
        }

        if (isset($item) && isset($item->AdditionalTxnFields->TxnDescriptor)) {
            $summary->transactionDescriptor = (string)$item->AdditionalTxnFields->TxnDescriptor;
        }

        if (isset($item) && isset($item->GiftCurrency)) {
            $summary->giftCurrency = (string)$item->GiftCurrency;
        }

        if (isset($item) && isset($item->GiftMaskedAlias)) {
            $summary->maskedAlias = (string)$item->GiftMaskedAlias;
        }

        if (isset($item) && isset($item->PaymentMethodKey)) {
            $summary->paymentMethodKey = (string)$item->PaymentMethodKey;
        }

        if (isset($item) && isset($item->ScheduleID)) {
            $summary->scheduleId = (string)$item->ScheduleID;
        }

        if (isset($item) && isset($item->OneTime)) {
            $summary->oneTimePayment = (string)$item->OneTime;
        }

        if (isset($item) && isset($item->RecurringDataCode)) {
            $summary->recurringDataCode = (string)$item->RecurringDataCode;
        }

        if (isset($item) && isset($item->SurchargeAmtInfo)) {
            $summary->surchargeAmount = (string)$item->SurchargeAmtInfo;
        }

        if (isset($item) && isset($item->FraudInfoRule)) {
            $summary->fraudRuleInfo = (string)$item->UserNFraudInfoRuleame;
        }

        if (isset($item) && isset($item->RepeatCount)) {
            $summary->repeatCount = (string)$item->RepeatCount;
        }

        if (isset($item) && isset($item->EMVChipCondition)) {
            $summary->emvChipCondition = (string)$item->EMVChipCondition;
        }

        if (isset($item) && isset($item->HasEMVTag)) {
            $summary->hasEmvTags = (string)$item->HasEMVTag;
        }

        if (isset($item) && isset($item->HasEcomPaymentData)) {
            $summary->hasEcomPaymentData = (string)$item->HasEcomPaymentData;
        }

        if (isset($item) && isset($item->CAVVResultCode)) {
            $summary->cavvResponseCode = (string)$item->CAVVResultCode;
        }

        if (isset($item) && isset($item->TokenPANLast4)) {
            $summary->tokenPanLastFour = (string)$item->TokenPANLast4;
        }

        if (isset($item) && isset($item->Company)) {
            $summary->companyName = (string)$item->Company;
        }

        if (isset($item) && isset($item->CustomerFirstname)) {
            $summary->customerFirstName = (string)$item->CustomerFirstname;
        }

        if (isset($item) && isset($item->CustomerLastName)) {
            $summary->customerLastName = (string)$item->CustomerLastName;
        }

        if (isset($item) && isset($item->DebtRepaymentIndicator)) {
            $summary->debtRepaymentIndicator = (string)$item->DebtRepaymentIndicator;
        }

        if (isset($item) && isset($item->CaptureAmtInfo)) {
            $summary->captureAmount = (string)$item->CaptureAmtInfo;
        }

        if (isset($item) && isset($item->FullyCapturedInd)) {
            $summary->fullyCaptured = (string)$item->FullyCapturedInd;
        }

        // lodging data
        if (isset($item) && isset($item->LodgingData)) {
            $summary->lodgingData = new LodgingData();
            $summary->lodgingData->prestigiousPropertyLimit = (string)$item->LodgingData->PrestigiousPropertyLimit;
            $summary->lodgingData->noShow = (string)$item->LodgingData->NoShow;
            $summary->lodgingData->advancedDepositType = (string)$item->LodgingData->AdvancedDepositType;
            $summary->lodgingData->lodgingDataEdit = (string)$item->LodgingData->LodgingDataEdit;
            $summary->lodgingData->preferredCustomer = (string)$item->LodgingData->PreferredCustomer;
        }

        // check data
        if (isset($item) && isset($item->CheckData)) {
            $summary->checkData = new CheckData();
            $summary->checkData->accountInfo = $item->CheckData->AccountInfo;
            $summary->checkData->consumerInfo = $item->CheckData->ConsumerInfo;
            $summary->checkData->dataEntryMode = (string)$item->CheckData->DataEntryMode;
            $summary->checkData->checkType = (string)$item->CheckData->CheckType;
            $summary->checkData->secCode = (string)$item->CheckData->SECCode;
            $summary->checkData->checkAction = (string)$item->CheckData->CheckAction;
        }

        // alt payment data
        if (isset($item) && isset($item->AltPaymentData)) {
            $summary->altPaymentData = new AltPaymentData();
            $summary->altPaymentData->buyerEmailAddress = (string)$item->AltPaymentData->BuyerEmailAddress;
            $summary->altPaymentData->stateDate = (string)$item->AltPaymentData->StatusDT;
            $summary->altPaymentData->status = (string)$item->AltPaymentData->Status;
            $summary->altPaymentData->statusMessage = (string)$item->AltPaymentData->StatusMsg;

            $summary->altPaymentData->processorResponseInfo = new AltPaymentProcessorInfo();
            foreach ($summary->altPaymentData->processorResponseInfo as $info) {
                $pri = new AltPaymentProcessorInfo();
                $pri->code = (string)$info->Code;
                $pri->message = (string)$info->Message;
                $pri->type = (string)$info->Type;
                $summary->altPaymentData->processorResponseInfo->add($pri);
            }
        }

        // 3DSecure
        if (isset($item) && isset($item->Secure3D)) {
            $secure3D = new ThreeDSecure();
            $secure3D->authenticationValue = (string)$item->Secure3D->AuthenticationValue;
            $secure3D->directoryServerTransactionId = (string)$item->Secure3D->DirectoryServerTxnId;
            $secure3D->eci = (int)$item->Secure3D->ECI;

            try {
                // account for default value of Version One.No value will be returned from Portico in this case.
                $versionString = (string)$item->Secure3D->Version;
                if (is_null($versionString) || empty($versionString)) {
                    // Default to version One.
                    $secure3D->setVersion(Secure3dVersion::ONE);
                } else {
                    $versionNumber = (int)$item->Secure3D->Version;
                    $secure3D->setVersion($this->getSecure3DVersionType($versionNumber));
                }
            } catch (Exception) {
                trigger_error("No Matching Version Found");
            }

            $summary->threeDSecure = $secure3D;
        }

        return $summary;
    }

    /**
     * Maps a transaction builder to a Portico request type
     *
     * @param BaseBuilder $builder Transaction builder
     *
     * @return string
     */
    protected function mapRequestType(BaseBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::BATCH_CLOSE:
                return 'BatchClose';
            case TransactionType::DECLINE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardDeactivate';
                } elseif ($builder->transactionModifier === TransactionModifier::CHIP_DECLINE) {
                    return 'ChipCardDecline';
                } elseif ($builder->transactionModifier === TransactionModifier::FRAUD_DECLINE) {
                    return 'OverrideFraudDecline';
                }
                throw new NotImplementedException();
            case TransactionType::VERIFY:
                if ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                    throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
                }
                return 'CreditAccountVerify';
            case TransactionType::CAPTURE:
                return 'CreditAddToBatch';
            case TransactionType::AUTH:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    if ($builder->transactionModifier === TransactionModifier::ADDITIONAL) {
                        return 'CreditAdditionalAuth';
                    } elseif (is_a($builder, 'GlobalPayments\Api\Builders\ManagementBuilder')) {
                        return 'CreditIncrementalAuth';
                    } elseif ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                        return 'CreditOfflineAuth';
                    } elseif ($builder->transactionModifier == TransactionModifier::RECURRING) {
                        return 'RecurringBillingAuth';
                    }
                    // elseif ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                    //     throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
                    // }

                    return 'CreditAuth';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::RECURRING) {
                    return 'RecurringBillingAuth';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::SALE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                        return 'CreditOfflineSale';
                    } elseif ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                        throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
                    } elseif ($builder->transactionModifier == TransactionModifier::RECURRING) {
                        return 'RecurringBilling';
                    } else {
                        return 'CreditSale';
                    }
                } elseif ($builder->paymentMethod->paymentMethodType == PaymentMethodType::RECURRING) {
                    if ($builder->paymentMethod->paymentType == 'ACH') {
                        return 'CheckSale';
                    }
                    return 'RecurringBilling';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CASH) {
                    return 'CashSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH) {
                    return 'CheckSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::EBT) {
                    if ($builder->transactionModifier === TransactionModifier::CASH_BACK) {
                        return 'EBTCashBackPurchase';
                    } elseif ($builder->transactionModifier === TransactionModifier::VOUCHER) {
                        return 'EBTVoucherPurchase';
                    } else {
                        return 'EBTFSPurchase';
                    }
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardSale';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::REFUND:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'CreditReturn';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitReturn';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CASH) {
                    return 'CashReturn';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::EBT) {
                    return 'EBTFSReturn';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::REVERSAL:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'CreditReversal';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitReversal';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardReversal';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::EDIT:
                if (
                    $builder->transactionModifier === TransactionModifier::LEVEL_II
                    || $builder->transactionModifier === TransactionModifier::LEVEL_III
                ) {
                    return 'CreditCPCEdit';
                } else {
                    return 'CreditTxnEdit';
                }
                break;
            case TransactionType::VOID:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'CreditVoid';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH) {
                    return 'CheckVoid';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardVoid';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::ADD_VALUE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'PrePaidAddValue';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitAddValue';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardAddValue';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::BALANCE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'PrePaidBalanceInquiry';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::EBT) {
                    return 'EBTBalanceInquiry';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardBalance';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::ACTIVATE:
                return 'GiftCardActivate';
            case TransactionType::ALIAS:
                return 'GiftCardAlias';
            case TransactionType::REPLACE:
                return 'GiftCardReplace';
            case TransactionType::REWARD:
                return 'GiftCardReward';
            case TransactionType::TOKEN_DELETE:
            case TransactionType::TOKEN_UPDATE:
                return 'ManageTokens';
            default:
                break;
        }

        throw new UnsupportedTransactionException('Unknown transaction');
    }

    protected function mapReportType(ReportBuilder $builder)
    {
        switch ($builder->reportType) {
            case ReportType::ACTIVITY:
            case ReportType::FIND_TRANSACTIONS:
                return 'FindTransactions';
            case ReportType::TRANSACTION_DETAIL:
                return 'ReportTxnDetail';
            case ReportType::BATCH_DETAIL:
                return 'ReportBatchDetail';
            case ReportType::OPEN_AUTH:
                return 'ReportOpenAuths';
            default:
                throw new UnsupportedTransactionException();
        }
    }

    /**
     * Converts a XML string to a simple object for use,
     * removing extra nodes that are not necessary for
     * handling the response
     *
     * @param string $xml Response XML from the gateway
     *
     * @return \SimpleXMLElement
     */
    protected function xml2object($xml)
    {
        $envelope = simplexml_load_string(
            $xml,
            'SimpleXMLElement',
            0,
            'http://schemas.xmlsoap.org/soap/envelope/'
        );

        foreach ($envelope->Body as $response) {
            $children = $response->children(static::XML_NAMESPACE);
            foreach ($children as $item) {
                return $item;
            }
        }

        throw new Exception('XML from gateway could not be parsed');
    }

    /**
     * Tests the payment method for a token value
     *
     * @param IPaymentMethod $paymentMethod The payment method
     *
     * @return [bool, string|null]
     */
    protected function hasToken(IPaymentMethod $paymentMethod)
    {
        $tokenValue = null;

        if ($paymentMethod instanceof ITokenizable && !empty($paymentMethod->token)) {
            $tokenValue = $paymentMethod->token;
            return [true, $tokenValue];
        }

        return [false, $tokenValue];
    }

    /**
     * Normalizes response code for success responses
     *
     * @param string $input Original response code
     *
     * @return string
     */
    protected function normalizeResponse($input)
    {
        if (in_array($input, ['0', '85'])) {
            $input = '00';
        }

        return $input;
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param AuthorizationBuilder $builder Request builder
     * @param bool $isCheck If payment method is ACH
     *
     * @return DOMElement
     */
    protected function hydrateHolder(DOMDocument $xml, AuthorizationBuilder $builder, $isCheck = false)
    {
        $address = new Address();
        $holder = $xml->createElement($isCheck ? 'ConsumerInfo' : 'CardHolderData');

        if ($isCheck && $builder->paymentMethod instanceof RecurringPaymentMethod) {
            return null;
        }

        if ($builder->billingAddress !== null) {
            $holder->appendChild(
                $xml->createElement($isCheck ? 'Address1' : 'CardHolderAddr', htmlentities($builder->billingAddress->streetAddress1 ?? ''))
            );
            $holder->appendChild(
                $xml->createElement($isCheck ? 'City' : 'CardHolderCity', htmlentities($builder->billingAddress->city ?? ''))
            );
            $holder->appendChild(
                $xml->createElement($isCheck ? 'State' : 'CardHolderState', $builder->billingAddress->getProvince() ?? '')
            );
            $holder->appendChild(
                $xml->createElement($isCheck ? 'Zip' : 'CardHolderZip', $address->checkZipCode($builder->billingAddress->postalCode))
            );
        }

        if ($builder->customerData !== null) {
            if (!empty($builder->customerData->email)) {
                $holder->appendChild(
                    $xml->createElement(
                        $isCheck ? 'EmailAddress' : 'CardHolderEmail',
                        $builder->customerData->email
                    )
                );
            }

            // on the off chance more than one phone value is supplied, this 
            // is organized in order of importance
            if (!empty($builder->customerData->homePhone)) {
                $cardHolderPhone = preg_replace("/[^0-9]/", "", $builder->customerData->homePhone);
            } elseif (!empty($builder->customerData->workPhone)) {
                $cardHolderPhone = preg_replace("/[^0-9]/", "", $builder->customerData->workPhone);
            } elseif (!empty($builder->customerData->mobilePhone)) {
                $cardHolderPhone = preg_replace("/[^0-9]/", "", $builder->customerData->mobilePhone);
            };

            $propertyName = $isCheck ? 'checkHolderName' : 'cardHolderName';
            if (!empty($builder->paymentMethod->{$propertyName})) {
                $names = explode(' ', htmlentities($builder->paymentMethod->{$propertyName}), 2);
                $holder->appendChild(
                    $xml->createElement($isCheck ? 'FirstName' : 'CardHolderFirstName', $names[0])
                );

                if (isset($names[1])) {
                    $holder->appendChild(
                        $xml->createElement($isCheck ? 'LastName' : 'CardHolderLastName', $names[1])
                    );
                }
            }
        }

        // on the off chance more than one phone value is supplied, this 
        // is organized in order of importance
        if (!empty($builder->homePhone->number)) {
            $cardHolderPhone = preg_replace(
                "/[^0-9]/",
                "",
                $builder->homePhone->countryCode . $builder->homePhone->number
            );
        } elseif (!empty($builder->workPhone->number)) {
            $cardHolderPhone = preg_replace(
                "/[^0-9]/",
                "",
                $builder->workPhone->countryCode . $builder->workPhone->number
            );
        } elseif (!empty($builder->shippingPhone->phone)) {
            $cardHolderPhone = preg_replace(
                "/[^0-9]/",
                "",
                $builder->shippingPhone->countryCode . $builder->shippingPhone->number
            );
        };

        if (!empty($cardHolderPhone)) {
            $holder->appendChild(
                $xml->createElement('CardHolderPhone', $cardHolderPhone)
            );
        }

        if ($isCheck) {
            if ($builder->paymentMethod->checkHolderName !== null) {
                $holder->appendChild($xml->createElement('CheckName', htmlentities($builder->paymentMethod->checkHolderName)));
            }

            if ($builder->paymentMethod->phoneNumber !== null) {
                $holder->appendChild($xml->createElement('PhoneNumber', $address->checkPhoneNumber($builder->paymentMethod->phoneNumber)));
            }

            if ($builder->paymentMethod->driversLicenseNumber !== null) {
                $holder->appendChild($xml->createElement('DLNumber', $builder->paymentMethod->driversLicenseNumber));
            }

            if ($builder->paymentMethod->driversLicenseState !== null) {
                $holder->appendChild($xml->createElement('DLState', $builder->paymentMethod->driversLicenseState));
            }

            if (
                $builder->paymentMethod->ssnLast4 !== null
                || $builder->paymentMethod->birthYear !== null
            ) {
                $identity = $xml->createElement('IdentityInfo');
                $identity->appendChild($xml->createElement('SSNL4', $builder->paymentMethod->ssnLast4));
                $identity->appendChild($xml->createElement('DOBYear', $builder->paymentMethod->birthYear));
                $holder->appendChild($identity);
            }
        }

        return $holder;
    }

    protected function hydrateAccountType($type)
    {
        switch ($type) {
            case AccountType::CHECKING:
                return 'CHECKING';
            case AccountType::SAVINGS:
                return 'SAVINGS';
        }
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     *
     * @return DOMElement
     */
    protected function hydrateAdditionalTxnFields(DOMDocument $xml, BaseBuilder $builder)
    {
        $additionalTxnFields = $xml->createElement('AdditionalTxnFields');

        if ($builder->description !== null && $builder->description !== '') {
            $additionalTxnFields->appendChild(
                $xml->createElement('Description', htmlentities($builder->description))
            );
        }

        if ($builder->invoiceNumber !== null && $builder->invoiceNumber !== '') {
            $additionalTxnFields->appendChild(
                $xml->createElement('InvoiceNbr', $builder->invoiceNumber)
            );
        }

        if ($builder->customerId !== null && $builder->customerId !== '') {
            $additionalTxnFields->appendChild(
                $xml->createElement('CustomerID', $builder->customerId)
            );
        }

        return $additionalTxnFields;
    }

    protected function hydrateCheckType($type)
    {
        switch ($type) {
            case CheckType::PERSONAL:
                return 'PERSONAL';
            case CheckType::BUSINESS:
                return 'BUSINESS';
            case CheckType::PAYROLL:
                return 'PAYROLL';
        }
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     *
     * @return DOMElement
     */
    protected function hydrateEncryptionData(DOMDocument $xml, BaseBuilder $builder)
    {
        $enc = $xml->createElement('EncryptionData');

        if ($builder->paymentMethod->encryptionData->version !== null) {
            $enc->appendChild($xml->createElement('Version', $builder->paymentMethod->encryptionData->version));
        }

        if ($builder->paymentMethod->encryptionData->trackNumber !== null) {
            $enc->appendChild($xml->createElement('TrackNumber', $builder->paymentMethod->encryptionData->trackNumber));
        }

        if ($builder->paymentMethod->encryptionData->ktb !== null) {
            $enc->appendChild($xml->createElement('KTB', $builder->paymentMethod->encryptionData->ktb));
        }

        if ($builder->paymentMethod->encryptionData->ksn !== null) {
            $enc->appendChild($xml->createElement('KSN', $builder->paymentMethod->encryptionData->ksn));
        }

        return $enc;
    }

    protected function hydrateEntryMethod($method)
    {
        switch ($method) {
            case EntryMethod::MANUAL:
                return 'Manual';
            case EntryMethod::SWIPE:
                return 'Swipe';
            case EntryMethod::PROXIMITY:
                return 'Proximity';
        }
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     * @param bool $hasToken If request builder is using token data
     * @param string $tokenValue Token if `$hasToken` is `true`
     *
     * @return DOMElement
     */
    protected function hydrateManualEntry(DOMDocument $xml, BaseBuilder $builder, $hasToken = false, $tokenValue = null)
    {
        if ($hasToken) {
            $me = $xml->createElement('TokenData');
        } else {
            $me = $xml->createElement('ManualEntry');
        }

        if ($hasToken || isset($builder->paymentMethod->number)) {
            $me->appendChild(
                $xml->createElement(
                    $hasToken ? 'TokenValue' : 'CardNbr',
                    $hasToken ? $tokenValue : $builder->paymentMethod->number
                )
            );
        }

        if (isset($builder->paymentMethod->expMonth)) {
            $me->appendChild($xml->createElement('ExpMonth', $builder->paymentMethod->expMonth));
        }

        if (isset($builder->paymentMethod->expYear)) {
            $me->appendChild($xml->createElement('ExpYear', $builder->paymentMethod->expYear));
        }

        if (isset($builder->paymentMethod->cvn)) {
            $me->appendChild($xml->createElement('CVV2', $builder->paymentMethod->cvn));
        }

        $me->appendChild(
            $xml->createElement('CardPresent', ($builder->paymentMethod->cardPresent ? 'Y' : 'N'))
        );

        $me->appendChild(
            $xml->createElement('ReaderPresent', ($builder->paymentMethod->readerPresent ? 'Y' : 'N'))
        );

        return $me;
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     * @param bool $hasToken If request builder is using token data
     * @param string $tokenValue Token if `$hasToken` is `true`
     *
     * @return DOMElement
     */
    protected function hydrateTrackData(DOMDocument $xml, BaseBuilder $builder, $hasToken = false, $tokenValue = null)
    {
        $trackData = $xml->createElement($hasToken ? 'TokenValue' : 'TrackData');

        if ($hasToken) {
            $trackData->appendChild($xml->createElement('TokenValue', $tokenValue));
            return $trackData;
        }

        $trackData->appendChild($xml->createTextNode($builder->paymentMethod->value));
        if ($builder->paymentMethod->paymentMethodType !== PaymentMethodType::DEBIT) {
            $trackData->setAttribute(
                'method',
                $builder->paymentMethod->entryMethod === EntryMethod::SWIPE
                    ? 'swipe'
                    : 'proximity'
            );
        }

        return $trackData;
    }

    public function supportsHostedPayments()
    {
        return $this->supportsHostedPayments;
    }

    /**
     * To hydrate ThreeDSecure Data
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     * @param DOMDocument $block1
     *
     * @return DOMElement
     */
    protected function hydrateThreeDSecureData(DOMDocument $xml, BaseBuilder $builder, $block1)
    {
        //3dseccure
        if (
            !empty($builder->paymentMethod->threeDSecure->eci)
            && !$this->isAppleOrGooglePay($builder->paymentMethod->threeDSecure->paymentDataSource)
        ) {
            $secure = $xml->createElement('Secure3D');

            $secure->appendChild(
                $xml->createElement(
                    'Version',
                    $this->getSecure3DVersion($builder->paymentMethod->threeDSecure->getVersion())
                )
            );

            if (!empty($builder->paymentMethod->threeDSecure->cavv)) {
                $secure->appendChild($xml->createElement('AuthenticationValue', $builder->paymentMethod->threeDSecure->cavv));
            }
            if (!empty($builder->paymentMethod->threeDSecure->eci)) {
                $secure->appendChild($xml->createElement('ECI', $builder->paymentMethod->threeDSecure->eci));
            }
            if (!empty($builder->paymentMethod->threeDSecure->xid)) {
                $secure->appendChild($xml->createElement('DirectoryServerTxnId', $builder->paymentMethod->threeDSecure->xid));
            }

            $block1->appendChild($secure);
        }
        if ($this->isAppleOrGooglePay($builder->paymentMethod->threeDSecure->paymentDataSource)) {
            $secure = $xml->createElement('WalletData');

            if (!empty($builder->paymentMethod->threeDSecure->cavv)) {
                $secure->appendChild($xml->createElement('PaymentSource', $builder->paymentMethod->threeDSecure->paymentDataSource));
            }
            if (!empty($builder->paymentMethod->threeDSecure->xid)) {
                $secure->appendChild($xml->createElement('Cryptogram', $builder->paymentMethod->threeDSecure->cavv));
            }
            if (!empty($builder->paymentMethod->threeDSecure->eci)) {
                $secure->appendChild($xml->createElement('ECI', $builder->paymentMethod->threeDSecure->eci));
            }

            $block1->appendChild($secure);
        }
    }

    /**
     * To hydrate Wallet Data
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     * @param DOMDocument $block1
     * @param DOMDocument $cardData
     * @return DOMElement
     */
    private function hydrateWalletData(DOMDocument $xml, BaseBuilder $builder, $block1, $cardData)
    {
        //wallet data
        if ($this->isAppleOrGooglePay($builder->paymentMethod->paymentSource)) {
            $walletData  = $xml->createElement('WalletData');

            if (!empty($builder->paymentMethod->threeDSecure->cavv)) {
                $walletData->appendChild($xml->createElement('Cryptogram', $builder->paymentMethod->threeDSecure->cavv));
            }
            if (!empty($builder->paymentMethod->threeDSecure->eci)) {
                $walletData->appendChild($xml->createElement('ECI', $builder->paymentMethod->threeDSecure->eci));
            }
            if (!empty($builder->paymentMethod->paymentSource)) {
                $walletData->appendChild($xml->createElement('PaymentSource', $builder->paymentMethod->paymentSource));
            }
            if (!empty($builder->paymentMethod->token)) {
                $token = $builder->paymentMethod->token;
                $dpt = $walletData->appendChild($xml->createElement('DigitalPaymentToken'));
                $dpt->appendChild($xml->createCDATASection($token));
                $block1->removeChild($cardData);
            }

            $block1->appendChild($walletData);
        }
    }

    /**
     * To format token
     *
     * @param string $token
     *
     * @return string
     */
    private function toFormatToken(string $token)
    {
        $decodedToken = json_decode(preg_replace('/(\\\)(\w)/', '${1}${1}${2}', $token));
        foreach ($decodedToken as $key => $value) {
            if ($key == 'signedMessage')
                $decodedToken->$key = str_replace('u003d', '\u003d', $value);
        }

        $result = json_encode($decodedToken, JSON_UNESCAPED_SLASHES);
        return str_replace('\\\\', '\\', $result);
    }

    /**
     * To check ApplePay or GooglePay
     *
     * @param string $paymentDataSource
     * @return boolean
     */
    private function isAppleOrGooglePay($paymentDataSource)
    {
        if (
            $paymentDataSource == PaymentDataSourceType::APPLEPAY
            || $paymentDataSource == PaymentDataSourceType::APPLEPAYAPP
            || $paymentDataSource == PaymentDataSourceType::APPLEPAYWEB
            || $paymentDataSource == PaymentDataSourceType::GOOGLEPAYAPP
            || $paymentDataSource == PaymentDataSourceType::GOOGLEPAYWEB
        ) {
            return true;
        }
        return false;
    }

    /**
     * Parses and returns the release number (version number) from 'metadata.xml'
     * 
     * @return string 
     */
    private function getReleaseVersion() : string
    {
        try {
            $fileContents = file_get_contents(
                dirname(__DIR__, 2) . '/metadata.xml'
            );
            
            $posOne = strpos($fileContents, "<releaseNumber>");
            $posTwo = strpos($fileContents, "</releaseNumber>");
            
            return substr($fileContents, $posOne + 15, $posTwo - $posOne - 15);
        } catch(Exception $e) {
            trigger_error(
                "Unable to append SDK version to request header. Inner Exception:"
                . PHP_EOL . $e->getMessage()
            );
        }        
    }

    /**
     * To get Secure3dVersion
     *
     * @param string $version
     * @return int
     */
    private function getSecure3DVersion($version)
    {
        if ($version == null) {
            return 1;
        }
        switch ($version) {
            case 'TWO':
                return 2;
            case 'ONE':
            case 'ANY':
            default:
                return 1;
        }
    }

    /**
     * To get Secure3dVersion
     *
     * @param int $version
     * @return Secure3dVersion
     */
    private function getSecure3DVersionType($version)
    {
        switch ($version){
            case 0:
                return Secure3dVersion::NONE;
            case 1:
                return Secure3dVersion::ONE;
            case 2:
                return Secure3dVersion::TWO;
            default:
                return Secure3dVersion::ANY;
        }
    }
}
